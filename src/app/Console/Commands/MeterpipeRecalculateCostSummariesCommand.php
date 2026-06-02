<?php

namespace App\Console\Commands;

use App\Models\CostProvider;
use App\Services\Costs\CostSummaryRecalculator;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class MeterpipeRecalculateCostSummariesCommand extends Command
{
    protected $signature = 'meterpipe:recalculate-cost-summaries
        {--from= : YYYY-MM-DD}
        {--to= : YYYY-MM-DD}
        {--provider=all : openai, laravel_cloud, all}';

    protected $description = 'Recalculate dashboard daily cost summaries from cost records.';

    public function handle(CostSummaryRecalculator $recalculator): int
    {
        $provider = (string) $this->option('provider');

        if (! in_array($provider, [CostProvider::OPENAI, CostProvider::LARAVEL_CLOUD, CostProvider::ALL], true)) {
            $this->error('--provider は openai, laravel_cloud, all のいずれかを指定してください。');

            return self::FAILURE;
        }

        $from = is_string($this->option('from')) && $this->option('from') !== ''
            ? CarbonImmutable::parse($this->option('from'), 'UTC')->startOfDay()
            : CarbonImmutable::now('UTC')->subDays(29)->startOfDay();
        $to = is_string($this->option('to')) && $this->option('to') !== ''
            ? CarbonImmutable::parse($this->option('to'), 'UTC')->endOfDay()
            : CarbonImmutable::now('UTC')->endOfDay();

        $count = $recalculator->recalculate($from, $to, $provider);

        $this->info(sprintf('cost_daily_summaries recalculated: %d', $count));

        return self::SUCCESS;
    }
}
