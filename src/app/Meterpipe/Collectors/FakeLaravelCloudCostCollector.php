<?php

namespace App\Meterpipe\Collectors;

use App\Enums\CostProviderKey;
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
                'summary_date' => $date,
                'provider_key' => CostProviderKey::LaravelCloud->value,
                'pipe_app_key' => null,
                'dimension_type' => 'resource_type',
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
