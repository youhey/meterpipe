<?php

namespace App\Services\CostProviders\OpenAi;

use App\Enums\CostProviderKey;
use Carbon\CarbonImmutable;

class OpenAiCostNormalizer
{
    /** @param list<array<string, mixed>> $pages */
    public function normalize(array $pages, ?string $groupBy = null): array
    {
        $records = [];

        foreach ($pages as $page) {
            $buckets = data_get($page, 'data', []);

            if (! is_array($buckets)) {
                continue;
            }

            foreach ($buckets as $bucket) {
                if (! is_array($bucket)) {
                    continue;
                }

                $bucketStart = $this->epoch(data_get($bucket, 'start_time'));
                $bucketEnd = $this->epoch(data_get($bucket, 'end_time'));
                $results = data_get($bucket, 'results', []);

                if ($bucketStart === null || $bucketEnd === null || ! is_array($results)) {
                    continue;
                }

                foreach ($results as $result) {
                    if (! is_array($result)) {
                        continue;
                    }

                    $amount = data_get($result, 'amount.value', 0);
                    $currency = data_get($result, 'amount.currency', config('meterpipe.default_currency', 'usd'));
                    $projectId = $this->nullableString(data_get($result, 'project_id'));
                    $apiKeyId = $this->nullableString(data_get($result, 'api_key_id'));
                    $lineItem = $this->nullableString(data_get($result, 'line_item'));
                    $quantity = data_get($result, 'quantity');
                    $dimensionType = $this->dimensionType($groupBy, $projectId, $apiKeyId, $lineItem);

                    $records[] = [
                        'provider_key' => CostProviderKey::OpenAi->value,
                        'source_record_key' => $this->sourceRecordKey(
                            $bucketStart,
                            $bucketEnd,
                            $dimensionType,
                            $projectId,
                            $apiKeyId,
                            $lineItem,
                            (string) $currency,
                        ),
                        'bucket_start' => $bucketStart,
                        'bucket_end' => $bucketEnd,
                        'bucket_date' => $bucketStart->toDateString(),
                        'amount' => $this->stringDecimal($amount),
                        'currency' => strtolower((string) $currency),
                        'pipe_app_key' => null,
                        'source_dimension_type' => $dimensionType,
                        'external_project_id' => $projectId,
                        'external_api_key_id' => $apiKeyId,
                        'external_application_id' => null,
                        'external_environment_id' => null,
                        'line_item' => $lineItem,
                        'resource_type' => null,
                        'service_name' => $lineItem,
                        'quantity' => is_numeric($quantity) ? $this->stringDecimal($quantity) : null,
                        'unit' => $lineItem !== null ? 'unit' : null,
                        'raw_payload' => [
                            'bucket_start' => $bucketStart->timestamp,
                            'bucket_end' => $bucketEnd->timestamp,
                            'result' => $result,
                        ],
                    ];
                }
            }
        }

        return $records;
    }

    public function sourceRecordKey(
        CarbonImmutable $bucketStart,
        CarbonImmutable $bucketEnd,
        string $dimensionType,
        ?string $projectId,
        ?string $apiKeyId,
        ?string $lineItem,
        string $currency,
    ): string {
        return implode(':', [
            CostProviderKey::OpenAi->value,
            $bucketStart->timestamp,
            $bucketEnd->timestamp,
            $dimensionType,
            $projectId ?? '_',
            $apiKeyId ?? '_',
            $lineItem ?? '_',
            strtolower($currency),
        ]);
    }

    private function epoch(mixed $value): ?CarbonImmutable
    {
        return is_numeric($value) ? CarbonImmutable::createFromTimestampUTC((int) $value) : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function dimensionType(?string $groupBy, ?string $projectId, ?string $apiKeyId, ?string $lineItem): string
    {
        if ($groupBy === null) {
            return 'total';
        }

        return match ($groupBy) {
            'project_id' => 'project',
            'api_key_id' => 'api_key',
            'line_item' => 'line_item',
            default => match (true) {
                $projectId !== null => 'project',
                $apiKeyId !== null => 'api_key',
                $lineItem !== null => 'line_item',
                default => 'total',
            },
        };
    }

    private function stringDecimal(mixed $value): string
    {
        return number_format((float) $value, 8, '.', '');
    }
}
