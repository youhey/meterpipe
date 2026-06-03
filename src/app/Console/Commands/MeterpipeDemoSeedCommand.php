<?php

namespace App\Console\Commands;

use App\Enums\CollectorRunStatus;
use App\Enums\CostProviderKey;
use App\Meterpipe\Collectors\Concerns\BuildsCollectorRows;
use App\Models\AnalyticsEvent;
use App\Models\CollectorRun;
use App\Models\CostDailySummary;
use App\Models\MetricSnapshot;
use App\Models\PipeApp;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class MeterpipeDemoSeedCommand extends Command
{
    use BuildsCollectorRows;

    protected $signature = 'meterpipe:demo:seed {--force : Allow production execution}';

    protected $description = 'Seed fake meterpipe dashboard data.';

    public function handle(): int
    {
        if (app()->isProduction() && ! $this->option('force')) {
            $this->warn('production で demo seed を実行するには --force が必要です。');

            return self::FAILURE;
        }

        $this->call('db:seed', ['--class' => 'Database\\Seeders\\PipeAppSeeder']);

        $apps = PipeApp::query()->get()->keyBy('key');
        $now = CarbonImmutable::now();

        for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
            $date = $now->subDays($daysAgo)->startOfDay();
            $this->seedProviderCost(CostProviderKey::OpenAi->value, 'line_item', 'completions', 4.5 + ($daysAgo % 5), $date);
            $this->seedProviderCost(CostProviderKey::LaravelCloud->value, 'resource_type', 'compute', 7.0 + ($daysAgo % 3), $date);

            foreach (['digestpipe', 'radiopipe', 'voicepipe', 'playpipe'] as $index => $key) {
                $app = $apps->get($key);

                if ($app === null) {
                    continue;
                }

                $dimensions = ['demo' => true, 'app' => $key];
                $summaryKey = $this->dimensionsHash($dimensions + [
                    'provider_key' => CostProviderKey::All->value,
                    'date' => $date->toDateString(),
                ]);

                CostDailySummary::query()->updateOrCreate(
                    [
                        'summary_key' => $summaryKey,
                    ],
                    [
                        'summary_date' => $date,
                        'provider_key' => CostProviderKey::All->value,
                        'pipe_app_key' => $app->key,
                        'dimension_type' => 'pipe_app',
                        'dimension_key' => $app->key,
                        'dimension_label' => $app->name,
                        'amount' => 1.2 + ($index * 0.7) + ($daysAgo % 4),
                        'currency' => 'usd',
                        'record_count' => 1,
                        'calculated_at' => $now,
                    ],
                );

                MetricSnapshot::query()->create([
                    'source' => $key,
                    'pipe_app_id' => $app->id,
                    'metric_name' => 'app.requests',
                    'value' => 80 + ($index * 30) + $daysAgo,
                    'unit' => 'request',
                    'dimensions' => ['demo' => true],
                    'measured_at' => $now->subDays($daysAgo),
                ]);
            }
        }

        foreach ($apps->take(4) as $app) {
            AnalyticsEvent::query()->create([
                'pipe_app_id' => $app->id,
                'event_name' => 'demo.pipeline.completed',
                'subject_type' => 'demo',
                'subject_id' => 'sample-' . $app->key,
                'actor_type' => 'system',
                'actor_id_hash' => hash('sha256', 'demo-system'),
                'properties' => ['demo' => true],
                'occurred_at' => $now,
            ]);
        }

        CollectorRun::query()->create([
            'collector_name' => 'fake-openai-cost',
            'status' => CollectorRunStatus::Succeeded,
            'started_at' => $now->subMinutes(20),
            'finished_at' => $now->subMinutes(19),
            'fetched_count' => 3,
            'stored_count' => 3,
            'metadata' => ['demo' => true],
        ]);

        CollectorRun::query()->create([
            'collector_name' => 'fake-laravel-cloud-cost',
            'status' => CollectorRunStatus::Failed,
            'started_at' => $now->subHours(2),
            'finished_at' => $now->subHours(2)->addMinute(),
            'error_message' => 'Demo failure placeholder',
            'metadata' => ['demo' => true],
        ]);

        $this->info('meterpipe demo data を投入しました。');

        return self::SUCCESS;
    }

    private function seedProviderCost(string $providerKey, string $dimensionType, string $dimensionKey, float $amount, CarbonImmutable $date): void
    {
        $dimensions = ['demo' => true];
        $summaryKey = $this->dimensionsHash($dimensions + [
            'provider_key' => $providerKey,
            'dimension_type' => $dimensionType,
            'dimension_key' => $dimensionKey,
            'date' => $date->toDateString(),
        ]);

        CostDailySummary::query()->updateOrCreate(
            [
                'summary_key' => $summaryKey,
            ],
            [
                'summary_date' => $date,
                'provider_key' => $providerKey,
                'pipe_app_key' => null,
                'dimension_type' => $dimensionType,
                'dimension_key' => $dimensionKey,
                'dimension_label' => $dimensionKey,
                'amount' => $amount,
                'currency' => 'usd',
                'record_count' => 1,
                'calculated_at' => CarbonImmutable::now(),
            ],
        );
    }
}
