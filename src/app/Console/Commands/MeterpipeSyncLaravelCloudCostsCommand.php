<?php

namespace App\Console\Commands;

use App\Services\Costs\CostSyncPeriod;
use App\Services\Costs\CostSyncService;
use Illuminate\Console\Command;
use Throwable;

class MeterpipeSyncLaravelCloudCostsCommand extends Command
{
    protected $signature = 'meterpipe:sync-laravel-cloud-costs
        {--from= : YYYY-MM-DD}
        {--to= : YYYY-MM-DD}
        {--days= : Default sync period when from/to are omitted}
        {--sync : Run synchronously without queue}
        {--force : Run disabled provider}';

    protected $description = 'Sync Laravel Cloud usage costs.';

    public function handle(CostSyncPeriod $period, CostSyncService $service): int
    {
        [$from, $to] = $period->resolve(
            is_string($this->option('from')) ? $this->option('from') : null,
            is_string($this->option('to')) ? $this->option('to') : null,
            (int) ($this->option('days') ?: config('meterpipe.laravel_cloud_cost_sync_days', 30)),
        );

        try {
            $run = $this->option('sync')
                ? $service->syncLaravelCloud($from, $to, 'manual', (bool) $this->option('force'))
                : $service->queueLaravelCloud($from, $to, 'manual', (bool) $this->option('force'));

            $this->line(sprintf('laravel_cloud: %s fetched=%d saved=%d', $run->status, $run->records_fetched, $run->records_saved));

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error('laravel_cloud: ' . $throwable::class . ': ' . mb_substr($throwable->getMessage(), 0, 300));

            return self::FAILURE;
        }
    }
}
