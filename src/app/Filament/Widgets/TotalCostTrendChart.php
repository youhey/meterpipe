<?php

namespace App\Filament\Widgets;

use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class TotalCostTrendChart extends ChartWidget
{
    protected ?string $heading = '日別総コスト';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $trend = app(CostSummaryService::class)->totalTrend();

        return [
            'datasets' => [[
                'label' => 'Total',
                'data' => $trend['values'],
                'backgroundColor' => CostChartPalette::translucentColor(7),
                'borderColor' => CostChartPalette::color(7),
                'borderWidth' => 2,
                'pointBackgroundColor' => CostChartPalette::color(7),
                'pointBorderColor' => '#ffffff',
                'pointRadius' => 3,
                'tension' => 0.25,
            ]],
            'labels' => $trend['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
