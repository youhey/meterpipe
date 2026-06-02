<?php

namespace App\Console\Commands;

use App\Enums\CollectorRunStatus;
use App\Meterpipe\Collectors\CollectorContext;
use App\Meterpipe\Collectors\FailingTestCollector;
use App\Meterpipe\Collectors\FakeLaravelCloudCostCollector;
use App\Meterpipe\Collectors\FakeOpenAiCostCollector;
use App\Meterpipe\Collectors\FakeOpenAiUsageCollector;
use App\Meterpipe\Collectors\FakePipeAnalyticsCollector;
use App\Meterpipe\Collectors\MetricCollector;
use App\Models\AnalyticsEvent;
use App\Models\CollectorRun;
use App\Models\CostDailySummary;
use App\Models\MetricSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class MeterpipeCollectCommand extends Command
{
    protected $signature = 'meterpipe:collect
        {--collector= : Collector name}
        {--all : Run all fake collectors}
        {--dry-run : Do not persist collector runs or metric rows}';

    protected $description = 'Run meterpipe metric collectors.';

    public function handle(): int
    {
        $collectors = $this->resolveCollectors();

        if ($collectors === []) {
            $this->error('collector を指定するか --all を使用してください。');

            return self::FAILURE;
        }

        $exitCode = self::SUCCESS;

        foreach ($collectors as $collector) {
            if (! $this->runCollector($collector)) {
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }

    /** @return list<MetricCollector> */
    private function resolveCollectors(): array
    {
        $available = collect($this->availableCollectors())->keyBy(fn(MetricCollector $collector) => $collector->name());

        if ($this->option('all')) {
            return $available
                ->reject(fn(MetricCollector $collector) => $collector instanceof FailingTestCollector)
                ->values()
                ->all();
        }

        $name = $this->option('collector');

        if (! is_string($name) || $name === '') {
            return [];
        }

        $collector = $available->get($name);

        return $collector instanceof MetricCollector ? [$collector] : [];
    }

    /** @return list<MetricCollector> */
    private function availableCollectors(): array
    {
        return [
            new FakeOpenAiCostCollector(),
            new FakeOpenAiUsageCollector(),
            new FakeLaravelCloudCostCollector(),
            new FakePipeAnalyticsCollector(),
            new FailingTestCollector(),
        ];
    }

    private function runCollector(MetricCollector $collector): bool
    {
        $dryRun = (bool) $this->option('dry-run');
        $now = CarbonImmutable::now();
        $run = null;

        if (! $dryRun) {
            $run = CollectorRun::query()->create([
                'collector_name' => $collector->name(),
                'status' => CollectorRunStatus::Running,
                'started_at' => $now,
                'metadata' => ['dry_run' => false],
            ]);
        }

        try {
            $result = $collector->collect(new CollectorContext($dryRun, $now));

            if (! $dryRun) {
                foreach ($result->costDailySummaries as $row) {
                    CostDailySummary::query()->updateOrCreate(
                        [
                            'source' => $row['source'],
                            'pipe_app_id' => $row['pipe_app_id'],
                            'service' => $row['service'],
                            'date' => $row['date'],
                            'dimensions_hash' => $row['dimensions_hash'],
                        ],
                        $row,
                    );
                }

                foreach ($result->metricSnapshots as $row) {
                    MetricSnapshot::query()->create($row);
                }

                foreach ($result->analyticsEvents as $row) {
                    AnalyticsEvent::query()->create($row);
                }

                $run->update([
                    'status' => CollectorRunStatus::Succeeded,
                    'finished_at' => CarbonImmutable::now(),
                    'fetched_count' => $result->fetchedCount,
                    'stored_count' => $result->storedCount(),
                ]);
            }

            $this->info(sprintf(
                '%s: fetched=%d stored=%d%s',
                $collector->name(),
                $result->fetchedCount,
                $dryRun ? 0 : $result->storedCount(),
                $dryRun ? ' dry-run' : '',
            ));

            return true;
        } catch (Throwable $throwable) {
            $message = $this->safeErrorMessage($throwable);

            $run?->update([
                'status' => CollectorRunStatus::Failed,
                'finished_at' => CarbonImmutable::now(),
                'error_message' => $message,
            ]);

            $this->error($collector->name() . ': ' . $message);

            return false;
        }
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
