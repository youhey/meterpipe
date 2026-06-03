<?php

namespace App\Meterpipe\Collectors;

use App\Enums\CostProviderKey;
use App\Meterpipe\Collectors\Concerns\BuildsCollectorRows;

final class FakeOpenAiCostCollector implements MetricCollector
{
    use BuildsCollectorRows;

    public function name(): string
    {
        return 'fake-openai-cost';
    }

    public function collect(CollectorContext $context): CollectorResult
    {
        $date = $context->now->startOfDay();
        $rows = [];

        foreach (['completions' => 4.25, 'audio' => 1.45, 'images' => 0.65] as $service => $amount) {
            $dimensions = ['model_family' => $service === 'audio' ? 'tts' : 'mixed'];
            $rows[] = [
                'summary_date' => $date,
                'provider_key' => CostProviderKey::OpenAi->value,
                'pipe_app_key' => null,
                'dimension_type' => 'line_item',
                'dimension_key' => $service,
                'dimension_label' => $service,
                'amount' => $amount,
                'currency' => 'usd',
                'record_count' => 1,
                'calculated_at' => $context->now,
                'summary_key' => $this->dimensionsHash($dimensions + ['date' => $date->toDateString()]),
            ];
        }

        return new CollectorResult(fetchedCount: count($rows), costDailySummaries: $rows);
    }
}
