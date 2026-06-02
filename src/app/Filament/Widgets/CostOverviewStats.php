<?php

namespace App\Filament\Widgets;

use App\Services\CostSummaryService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CostOverviewStats extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $summary = app(CostSummaryService::class)->monthlySummary();
        $currency = strtoupper((string) $summary['currency']);

        return [
            Stat::make('今月の総コスト', $this->money($summary['month_to_date'], $currency)),
            Stat::make('今月の OpenAI コスト', $this->money($summary['openai_month_to_date'], $currency)),
            Stat::make('今月の Laravel Cloud コスト', $this->money($summary['laravel_cloud_month_to_date'], $currency)),
            Stat::make('昨日の総コスト', $this->money($summary['yesterday_cost'], $currency)),
            Stat::make('月末予測', $this->money($summary['month_end_forecast'], $currency)),
            Stat::make('最終同期日時', $summary['last_synced_at'] ?? '未同期'),
        ];
    }

    private function money(mixed $amount, string $currency): string
    {
        return $currency . ' ' . number_format((float) $amount, 2);
    }
}
