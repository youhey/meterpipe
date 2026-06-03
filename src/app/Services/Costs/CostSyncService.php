<?php

namespace App\Services\Costs;

use App\Enums\CostProviderKey;
use App\Enums\IntegrationProvider;
use App\Jobs\SyncLaravelCloudCostsJob;
use App\Jobs\SyncOpenAiCostsJob;
use App\Models\AppIntegration;
use App\Models\CostDimensionMapping;
use App\Models\CostRecord;
use App\Models\CostSyncRun;
use App\Services\CostProviders\LaravelCloud\LaravelCloudUsageClient;
use App\Services\CostProviders\LaravelCloud\LaravelCloudUsageNormalizer;
use App\Services\CostProviders\OpenAi\OpenAiCostClient;
use App\Services\CostProviders\OpenAi\OpenAiCostNormalizer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Throwable;

class CostSyncService
{
    public function __construct(
        private readonly CostSummaryRecalculator $recalculator,
        private readonly OpenAiCostClient $openAiClient,
        private readonly OpenAiCostNormalizer $openAiNormalizer,
        private readonly LaravelCloudUsageClient $laravelCloudClient,
        private readonly LaravelCloudUsageNormalizer $laravelCloudNormalizer,
    ) {
    }

    public function queueOpenAi(CarbonImmutable $from, CarbonImmutable $to, string $scope = 'manual', bool $force = false): CostSyncRun
    {
        $run = $this->createRun(CostProviderKey::OpenAi->value, $from, $to, $scope, $force);

        if ($run->status === CostSyncRun::QUEUED) {
            SyncOpenAiCostsJob::dispatch($run->id, $from, $to, $force)
                ->onQueue((string) config('meterpipe.cost_sync_queue', 'default'));
        }

        return $run;
    }

    public function queueLaravelCloud(CarbonImmutable $from, CarbonImmutable $to, string $scope = 'manual', bool $force = false): CostSyncRun
    {
        $run = $this->createRun(CostProviderKey::LaravelCloud->value, $from, $to, $scope, $force);

        if ($run->status === CostSyncRun::QUEUED) {
            SyncLaravelCloudCostsJob::dispatch($run->id, $from, $to, $force)
                ->onQueue((string) config('meterpipe.cost_sync_queue', 'default'));
        }

        return $run;
    }

    public function syncOpenAi(CarbonImmutable $from, CarbonImmutable $to, string $scope = 'manual', bool $force = false): CostSyncRun
    {
        $run = $this->createRun(CostProviderKey::OpenAi->value, $from, $to, $scope, $force);

        if ($run->status !== CostSyncRun::QUEUED) {
            return $run;
        }

        return $this->executeOpenAi($run->id, $from, $to, $force);
    }

    public function syncLaravelCloud(CarbonImmutable $from, CarbonImmutable $to, string $scope = 'manual', bool $force = false): CostSyncRun
    {
        $run = $this->createRun(CostProviderKey::LaravelCloud->value, $from, $to, $scope, $force);

        if ($run->status !== CostSyncRun::QUEUED) {
            return $run;
        }

        return $this->executeLaravelCloud($run->id, $from, $to, $force);
    }

    public function executeOpenAi(int $runId, CarbonImmutable $from, CarbonImmutable $to, bool $force = false): CostSyncRun
    {
        return $this->withProviderLock(CostProviderKey::OpenAi->value, $from, $to, $runId, function (CostSyncRun $run) use ($from, $to, $force): CostSyncRun {
            if (! $this->providerEnabled(CostProviderKey::OpenAi->value, $force)) {
                return $this->skipRun($run, 'OpenAI provider is disabled.');
            }

            $projectIds = $this->openAiProjectIds();

            if ($projectIds === []) {
                return $this->skipRun($run, 'No enabled OpenAI project integrations are configured.');
            }

            $projectAppKeys = $this->openAiProjectAppKeys();

            return $this->executeRun($run, $from, $to, function () use ($from, $to, $projectIds, $projectAppKeys): array {
                $records = [];

                foreach ([null, 'project_id', 'line_item'] as $groupBy) {
                    $pages = $this->openAiClient->fetchCosts($from, $to, $groupBy, $projectIds);
                    $records = array_merge($records, $this->openAiNormalizer->normalize($pages, $groupBy));
                }

                return $this->applyOpenAiProjectAppKeys($records, $projectAppKeys);
            });
        });
    }

    public function executeLaravelCloud(int $runId, CarbonImmutable $from, CarbonImmutable $to, bool $force = false): CostSyncRun
    {
        return $this->withProviderLock(CostProviderKey::LaravelCloud->value, $from, $to, $runId, function (CostSyncRun $run) use ($from, $to, $force): CostSyncRun {
            if (! $this->providerEnabled(CostProviderKey::LaravelCloud->value, $force)) {
                return $this->skipRun($run, 'Laravel Cloud provider is disabled.');
            }

            return $this->executeRun($run, $from, $to, function () use ($from, $to): array {
                $payload = $this->laravelCloudClient->fetchUsage($from, $to);

                return $this->laravelCloudNormalizer->normalize($payload, $from, $to);
            });
        });
    }

    private function createRun(string $providerKey, CarbonImmutable $from, CarbonImmutable $to, string $scope, bool $force): CostSyncRun
    {
        if (! $this->providerEnabled($providerKey, $force)) {
            return CostSyncRun::query()->create([
                'provider_key' => $providerKey,
                'status' => CostSyncRun::SKIPPED,
                'scope' => $scope,
                'period_start' => $from,
                'period_end' => $to,
                'finished_at' => CarbonImmutable::now('UTC'),
                'meta' => ['reason' => 'provider_disabled'],
            ]);
        }

        return CostSyncRun::query()->create([
            'provider_key' => $providerKey,
            'status' => CostSyncRun::QUEUED,
            'scope' => $scope,
            'period_start' => $from,
            'period_end' => $to,
            'meta' => ['force' => $force],
        ]);
    }

    /** @param callable(): list<array<string, mixed>> $fetch */
    private function executeRun(CostSyncRun $run, CarbonImmutable $from, CarbonImmutable $to, callable $fetch): CostSyncRun
    {
        $run->update([
            'status' => CostSyncRun::RUNNING,
            'started_at' => CarbonImmutable::now('UTC'),
            'error_class' => null,
            'error_message' => null,
        ]);

        try {
            $records = $this->applyDimensionMappings($fetch());
            $saved = $this->upsertRecords($records);
            $this->recalculator->recalculate($from, $to);

            $run->update([
                'status' => CostSyncRun::SUCCEEDED,
                'finished_at' => CarbonImmutable::now('UTC'),
                'records_fetched' => count($records),
                'records_saved' => $saved,
            ]);

            return $run->refresh();
        } catch (Throwable $throwable) {
            $run->update([
                'status' => CostSyncRun::FAILED,
                'finished_at' => CarbonImmutable::now('UTC'),
                'error_class' => $throwable::class,
                'error_message' => $this->safeErrorMessage($throwable),
            ]);

            throw $throwable;
        }
    }

    private function providerEnabled(string $providerKey, bool $force): bool
    {
        if ($force) {
            return true;
        }

        $provider = CostProviderKey::tryFrom($providerKey);

        if (! $provider instanceof CostProviderKey || $provider === CostProviderKey::All) {
            return false;
        }

        return (bool) config($provider->enabledConfigPath(), false);
    }

    /** @return list<string> */
    private function openAiProjectIds(): array
    {
        return AppIntegration::query()
            ->where('provider', IntegrationProvider::OpenAi->value)
            ->where('enabled', true)
            ->whereNotNull('provider_project_id')
            ->pluck('provider_project_id')
            ->map(fn(mixed $projectId): string => trim((string) $projectId))
            ->filter(fn(string $projectId): bool => $projectId !== '')
            ->unique()
            ->values()
            ->all();
    }

    /** @return array<string, string> */
    private function openAiProjectAppKeys(): array
    {
        $integrations = AppIntegration::query()
            ->with('pipeApp')
            ->where('provider', IntegrationProvider::OpenAi->value)
            ->where('enabled', true)
            ->whereNotNull('provider_project_id')
            ->get();

        $projectAppKeys = [];

        foreach ($integrations->groupBy(fn(AppIntegration $integration): string => trim((string) $integration->provider_project_id)) as $projectId => $group) {
            if (! is_string($projectId) || $projectId === '') {
                continue;
            }

            $appKeys = $group
                ->map(fn(AppIntegration $integration): ?string => $integration->pipeApp?->key)
                ->filter(fn(?string $appKey): bool => is_string($appKey) && $appKey !== '')
                ->unique()
                ->values();

            if ($appKeys->count() === 1) {
                $projectAppKeys[$projectId] = (string) $appKeys->first();
            }
        }

        return $projectAppKeys;
    }

    /**
     * @param list<array<string, mixed>> $records
     * @param array<string, string> $projectAppKeys
     * @return list<array<string, mixed>>
     */
    private function applyOpenAiProjectAppKeys(array $records, array $projectAppKeys): array
    {
        return array_map(function (array $record) use ($projectAppKeys): array {
            $projectId = $record['external_project_id'] ?? null;

            if (is_string($projectId) && isset($projectAppKeys[$projectId])) {
                $record['pipe_app_key'] = $record['pipe_app_key'] ?? $projectAppKeys[$projectId];
            }

            return $record;
        }, $records);
    }

    private function skipRun(CostSyncRun $run, string $reason): CostSyncRun
    {
        $run->update([
            'status' => CostSyncRun::SKIPPED,
            'finished_at' => CarbonImmutable::now('UTC'),
            'meta' => array_merge($run->meta ?? [], ['reason' => $reason]),
        ]);

        return $run->refresh();
    }

    /** @param callable(CostSyncRun): CostSyncRun $callback */
    private function withProviderLock(string $providerKey, CarbonImmutable $from, CarbonImmutable $to, int $runId, callable $callback): CostSyncRun
    {
        $run = CostSyncRun::query()->findOrFail($runId);
        $lock = Cache::lock(sprintf('meterpipe:cost-sync:%s:%s:%s', $providerKey, $from->timestamp, $to->timestamp), 1800);

        if (! $lock->get()) {
            return $this->skipRun($run, 'Same provider and period are already syncing.');
        }

        try {
            return $callback($run);
        } finally {
            $lock->release();
        }
    }

    /** @param list<array<string, mixed>> $records */
    private function applyDimensionMappings(array $records): array
    {
        $mappings = CostDimensionMapping::query()
            ->where('is_enabled', true)
            ->get()
            ->keyBy(fn(CostDimensionMapping $mapping): string => $mapping->provider_key . ':' . $mapping->dimension_type . ':' . $mapping->external_id);

        return array_map(function (array $record) use ($mappings): array {
            foreach ($this->mappingCandidates($record) as $candidate) {
                $mapping = $mappings->get($candidate);

                if ($mapping instanceof CostDimensionMapping) {
                    $record['pipe_app_key'] = $record['pipe_app_key'] ?? $mapping->pipe_app_key;
                    break;
                }
            }

            return $record;
        }, $records);
    }

    /** @return list<string> */
    private function mappingCandidates(array $record): array
    {
        $providerKey = (string) $record['provider_key'];
        $candidates = [];

        foreach ([
            'project' => 'external_project_id',
            'api_key' => 'external_api_key_id',
            'application' => 'external_application_id',
            'environment' => 'external_environment_id',
            'line_item' => 'line_item',
            'resource_type' => 'resource_type',
        ] as $dimensionType => $field) {
            $value = $record[$field] ?? null;

            if (is_string($value) && $value !== '') {
                $candidates[] = $providerKey . ':' . $dimensionType . ':' . $value;
            }
        }

        return $candidates;
    }

    /** @param list<array<string, mixed>> $records */
    private function upsertRecords(array $records): int
    {
        $now = CarbonImmutable::now('UTC');
        $rows = array_map(function (array $record) use ($now): array {
            if (isset($record['raw_payload']) && is_array($record['raw_payload'])) {
                $record['raw_payload'] = json_encode($record['raw_payload'], JSON_THROW_ON_ERROR);
            }

            $record['synced_at'] = $now;
            $record['created_at'] = $now;
            $record['updated_at'] = $now;

            return $record;
        }, $records);

        if ($rows === []) {
            return 0;
        }

        CostRecord::query()->upsert(
            $rows,
            ['provider_key', 'source_record_key'],
            [
                'bucket_start',
                'bucket_end',
                'bucket_date',
                'amount',
                'currency',
                'pipe_app_key',
                'source_dimension_type',
                'external_project_id',
                'external_api_key_id',
                'external_application_id',
                'external_environment_id',
                'line_item',
                'resource_type',
                'service_name',
                'quantity',
                'unit',
                'raw_payload',
                'synced_at',
                'updated_at',
            ],
        );

        return count($rows);
    }

    private function safeErrorMessage(Throwable $throwable): string
    {
        $message = $throwable::class . ': ' . $throwable->getMessage();

        foreach (['openai_admin_key', 'laravel_cloud_api_token'] as $key) {
            $secret = config('meterpipe.' . $key);

            if (is_string($secret) && $secret !== '') {
                $message = str_replace($secret, '[redacted]', $message);
            }
        }

        return mb_substr($message, 0, 500);
    }
}
