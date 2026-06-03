<?php

namespace App\Filament\Widgets;

use App\Enums\CostProviderKey;
use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class OpenAiCostByLineItemChart extends ChartWidget
{
    protected ?string $heading = 'OpenAI: Items';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = app(CostSummaryService::class)->dimensionBreakdown(CostProviderKey::OpenAi->value, 'line_item');
        $colors = CostChartPalette::colors(count($data['values']));

        return [
            'datasets' => [[
                'label' => 'OpenAI line item',
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
