<?php

namespace App\Filament\Widgets;

use App\Enums\CostProviderKey;
use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class OpenAiCostByProjectChart extends ChartWidget
{
    protected ?string $heading = 'OpenAI project 別コスト';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = app(CostSummaryService::class)->dimensionBreakdown(CostProviderKey::OpenAi->value, 'project');

        return [
            'datasets' => [['label' => 'OpenAI project', 'data' => $data['values']]],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
