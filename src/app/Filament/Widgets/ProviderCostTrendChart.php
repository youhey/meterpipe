<?php

namespace App\Filament\Widgets;

use App\Services\CostSummaryService;
use Filament\Widgets\ChartWidget;

class ProviderCostTrendChart extends ChartWidget
{
    protected ?string $heading = 'Provider 別日別コスト';

    protected ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $trend = app(CostSummaryService::class)->providerTrend();

        return [
            'datasets' => [
                ['label' => 'OpenAI', 'data' => $trend['openai']],
                ['label' => 'Laravel Cloud', 'data' => $trend['laravel_cloud']],
            ],
            'labels' => $trend['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
