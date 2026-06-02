<?php

namespace App\Meterpipe\Collectors;

use App\Enums\MetricSource;

final class FakeOpenAiUsageCollector implements MetricCollector
{
    public function name(): string
    {
        return 'fake-openai-usage';
    }

    public function collect(CollectorContext $context): CollectorResult
    {
        $measuredAt = $context->now;

        $rows = [
            [
                'source' => MetricSource::OpenAi->value,
                'pipe_app_id' => null,
                'metric_name' => 'openai.requests',
                'value' => 1240,
                'unit' => 'request',
                'dimensions' => ['endpoint' => 'responses'],
                'measured_at' => $measuredAt,
            ],
            [
                'source' => MetricSource::OpenAi->value,
                'pipe_app_id' => null,
                'metric_name' => 'openai.tokens.input',
                'value' => 842000,
                'unit' => 'token',
                'dimensions' => ['endpoint' => 'responses'],
                'measured_at' => $measuredAt,
            ],
        ];

        return new CollectorResult(fetchedCount: count($rows), metricSnapshots: $rows);
    }
}
