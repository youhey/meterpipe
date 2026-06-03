<?php

namespace App\Filament\Widgets;

use App\Enums\CostProviderKey;
use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class LaravelCloudCostByApplicationChart extends ChartWidget
{
    protected ?string $heading = 'Laravel Cloud application 別コスト';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = app(CostSummaryService::class)->dimensionBreakdown(CostProviderKey::LaravelCloud->value, 'application');
        $colors = CostChartPalette::colors(count($data['values']));

        return [
            'datasets' => [[
                'label' => 'Laravel Cloud application',
                'data' => $data['values'],
                'backgroundColor' => CostChartPalette::translucentColors(count($data['values'])),
                'borderColor' => $colors,
                'borderWidth' => 2,
            ]],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
