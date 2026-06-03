<?php

namespace App\Filament\Widgets;

use App\Enums\CostProviderKey;
use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class LaravelCloudCostByResourceTypeChart extends ChartWidget
{
    protected ?string $heading = 'Laravel Cloud: Resources';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = app(CostSummaryService::class)->dimensionBreakdown(CostProviderKey::LaravelCloud->value, 'resource_type');
        $colors = CostChartPalette::colors(count($data['values']));

        return [
            'datasets' => [[
                'label' => 'Laravel Cloud resource type',
                'data' => $data['values'],
                'backgroundColor' => $colors,
                'borderColor' => '#ffffff',
                'borderWidth' => 2,
                'hoverBackgroundColor' => $colors,
            ]],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
