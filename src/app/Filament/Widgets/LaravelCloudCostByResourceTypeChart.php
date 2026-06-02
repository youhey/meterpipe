<?php

namespace App\Filament\Widgets;

use App\Models\CostProvider;
use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class LaravelCloudCostByResourceTypeChart extends ChartWidget
{
    protected ?string $heading = 'Laravel Cloud resource type 別コスト';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = app(CostSummaryService::class)->dimensionBreakdown(CostProvider::LARAVEL_CLOUD, 'resource_type');

        return [
            'datasets' => [['label' => 'Laravel Cloud resource type', 'data' => $data['values']]],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
