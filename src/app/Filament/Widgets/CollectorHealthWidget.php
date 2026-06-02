<?php

namespace App\Filament\Widgets;

use App\Services\CollectorHealthService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CollectorHealthWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $summary = app(CollectorHealthService::class)->summary();

        return [
            Stat::make('Active Alerts', (string) $summary['active_alerts'])
                ->description('Phase 1 placeholder'),
            Stat::make('最終 collector 実行時刻', $summary['latest_run_at']?->toDateTimeString() ?? 'none')
                ->description($summary['latest_run_status'] ?? 'no runs'),
        ];
    }
}
