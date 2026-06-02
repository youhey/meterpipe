<?php

namespace App\Filament\Widgets;

use App\Services\CostSummaryService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CostSummaryWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $summary = app(CostSummaryService::class)->monthlySummary();
        $currency = strtoupper((string) $summary['currency']);

        return [
            Stat::make('今月合計コスト', $this->money($summary['month_to_date'], $currency)),
            Stat::make('OpenAI API 今月コスト', $this->money($summary['openai_month_to_date'], $currency)),
            Stat::make('Laravel Cloud 今月コスト', $this->money($summary['laravel_cloud_month_to_date'], $currency)),
            Stat::make('昨日の総コスト', $this->money($summary['yesterday_cost'], $currency)),
            Stat::make('月末予測', $this->money($summary['month_end_forecast'], $currency)),
        ];
    }

    private function money(mixed $amount, string $currency): string
    {
        return $currency . ' ' . number_format((float) $amount, 2);
    }
}
