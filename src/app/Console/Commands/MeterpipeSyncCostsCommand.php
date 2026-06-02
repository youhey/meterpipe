<?php

namespace App\Console\Commands;

use App\Models\CostProvider;
use App\Models\CostSyncRun;
use App\Services\Costs\CostSyncPeriod;
use App\Services\Costs\CostSyncService;
use Illuminate\Console\Command;
use Throwable;

class MeterpipeSyncCostsCommand extends Command
{
    protected $signature = 'meterpipe:sync-costs
        {--provider=all : openai, laravel_cloud, all}
        {--from= : YYYY-MM-DD}
        {--to= : YYYY-MM-DD}
        {--days=30 : Default sync period when from/to are omitted}
        {--sync : Run synchronously without queue}
        {--force : Run disabled providers}';

    protected $description = 'Sync all meterpipe cost providers.';

    public function handle(CostSyncPeriod $period, CostSyncService $service): int
    {
        [$from, $to] = $period->resolve(
            is_string($this->option('from')) ? $this->option('from') : null,
            is_string($this->option('to')) ? $this->option('to') : null,
            (int) $this->option('days'),
        );

        $provider = (string) $this->option('provider');
        $providers = match ($provider) {
            CostProvider::OPENAI => [CostProvider::OPENAI],
            CostProvider::LARAVEL_CLOUD => [CostProvider::LARAVEL_CLOUD],
            CostProvider::ALL => [CostProvider::OPENAI, CostProvider::LARAVEL_CLOUD],
            default => [],
        };

        if ($providers === []) {
            $this->error('--provider は openai, laravel_cloud, all のいずれかを指定してください。');

            return self::FAILURE;
        }

        $exitCode = self::SUCCESS;

        foreach ($providers as $providerKey) {
            try {
                $run = match ($providerKey) {
                    CostProvider::OPENAI => $this->option('sync')
                        ? $service->syncOpenAi($from, $to, 'manual', (bool) $this->option('force'))
                        : $service->queueOpenAi($from, $to, 'manual', (bool) $this->option('force')),
                    CostProvider::LARAVEL_CLOUD => $this->option('sync')
                        ? $service->syncLaravelCloud($from, $to, 'manual', (bool) $this->option('force'))
                        : $service->queueLaravelCloud($from, $to, 'manual', (bool) $this->option('force')),
                };

                $this->line(sprintf('%s: %s fetched=%d saved=%d', $providerKey, $run->status, $run->records_fetched, $run->records_saved));

                if ($run->status === CostSyncRun::FAILED) {
                    $exitCode = self::FAILURE;
                }
            } catch (Throwable $throwable) {
                $this->error($providerKey . ': ' . $throwable::class . ': ' . mb_substr($throwable->getMessage(), 0, 300));
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }
}
