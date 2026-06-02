<?php

namespace App\Console\Commands;

use App\Services\Costs\CostSyncPeriod;
use App\Services\Costs\CostSyncService;
use Illuminate\Console\Command;
use Throwable;

class MeterpipeSyncOpenAiCostsCommand extends Command
{
    protected $signature = 'meterpipe:sync-openai-costs
        {--from= : YYYY-MM-DD}
        {--to= : YYYY-MM-DD}
        {--days= : Default sync period when from/to are omitted}
        {--sync : Run synchronously without queue}
        {--force : Run disabled provider}';

    protected $description = 'Sync OpenAI organization costs.';

    public function handle(CostSyncPeriod $period, CostSyncService $service): int
    {
        [$from, $to] = $period->resolve(
            is_string($this->option('from')) ? $this->option('from') : null,
            is_string($this->option('to')) ? $this->option('to') : null,
            (int) ($this->option('days') ?: config('meterpipe.openai_cost_sync_days', 30)),
        );

        try {
            $run = $this->option('sync')
                ? $service->syncOpenAi($from, $to, 'manual', (bool) $this->option('force'))
                : $service->queueOpenAi($from, $to, 'manual', (bool) $this->option('force'));

            $this->line(sprintf('openai: %s fetched=%d saved=%d', $run->status, $run->records_fetched, $run->records_saved));

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error('openai: ' . $throwable::class . ': ' . mb_substr($throwable->getMessage(), 0, 300));

            return self::FAILURE;
        }
    }
}
