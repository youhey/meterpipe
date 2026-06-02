<?php

namespace App\Meterpipe\Collectors;

use App\Enums\MetricSource;
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
                'source' => MetricSource::OpenAi->value,
                'pipe_app_id' => null,
                'service' => $service,
                'amount' => $amount,
                'currency' => 'usd',
                'dimensions' => $dimensions,
                'dimensions_hash' => $this->dimensionsHash($dimensions),
                'date' => $date,
            ];
        }

        return new CollectorResult(fetchedCount: count($rows), costDailySummaries: $rows);
    }
}
