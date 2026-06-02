<?php

namespace App\Filament\Widgets;

use App\Models\CostProvider;
use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class OpenAiCostByLineItemChart extends ChartWidget
{
    protected ?string $heading = 'OpenAI line item 別コスト';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = app(CostSummaryService::class)->dimensionBreakdown(CostProvider::OPENAI, 'line_item');

        return [
            'datasets' => [['label' => 'OpenAI line item', 'data' => $data['values']]],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
