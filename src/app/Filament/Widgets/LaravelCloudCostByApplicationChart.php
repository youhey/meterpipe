<?php

namespace App\Filament\Widgets;

use App\Models\CostProvider;
use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class LaravelCloudCostByApplicationChart extends ChartWidget
{
    protected ?string $heading = 'Laravel Cloud application 別コスト';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $data = app(CostSummaryService::class)->dimensionBreakdown(CostProvider::LARAVEL_CLOUD, 'application');

        return [
            'datasets' => [['label' => 'Laravel Cloud application', 'data' => $data['values']]],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
