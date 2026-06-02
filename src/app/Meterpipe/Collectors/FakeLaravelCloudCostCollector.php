<?php

namespace App\Meterpipe\Collectors;

use App\Enums\MetricSource;
use App\Meterpipe\Collectors\Concerns\BuildsCollectorRows;

final class FakeLaravelCloudCostCollector implements MetricCollector
{
    use BuildsCollectorRows;

    public function name(): string
    {
        return 'fake-laravel-cloud-cost';
    }

    public function collect(CollectorContext $context): CollectorResult
    {
        $date = $context->now->startOfDay();
        $rows = [];

        foreach (['compute' => 7.8, 'database' => 3.2] as $service => $amount) {
            $dimensions = ['environment' => 'production'];
            $rows[] = [
                'source' => MetricSource::LaravelCloud->value,
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
