<?php

namespace App\Services\Costs;

use App\Enums\CostProviderKey;
use App\Models\CostDailySummary;
use App\Models\CostDimensionMapping;
use App\Models\CostRecord;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class CostSummaryRecalculator
{
    public function recalculate(CarbonImmutable $from, CarbonImmutable $to, ?string $providerKey = null): int
    {
        $query = CostDailySummary::query()
            ->whereDate('summary_date', '>=', $from->toDateString())
            ->whereDate('summary_date', '<=', $to->toDateString());

        if ($providerKey !== null && $providerKey !== CostProviderKey::All->value) {
            $query->whereIn('provider_key', [$providerKey, CostProviderKey::All->value]);
        }

        $query->delete();

        $records = CostRecord::query()
            ->whereDate('bucket_date', '>=', $from->toDateString())
            ->whereDate('bucket_date', '<=', $to->toDateString())
            ->when($providerKey !== null && $providerKey !== CostProviderKey::All->value, fn($query) => $query->where('provider_key', $providerKey))
            ->get();

        if ($records->isEmpty()) {
            return 0;
        }

        $now = CarbonImmutable::now('UTC');
        $rows = [];

        foreach ($this->groupRecords($records, ['bucket_date', 'currency']) as $group) {
            $rows[] = $this->summaryRow($group, CostProviderKey::All->value, null, null, null, null, $now);
        }

        foreach ($this->groupRecords($records, ['bucket_date', 'provider_key', 'currency']) as $group) {
            $rows[] = $this->summaryRow($group, $group->first()->provider_key, null, null, null, null, $now);
        }

        foreach ($this->groupRecords($records->whereNotNull('pipe_app_key'), ['bucket_date', 'provider_key', 'pipe_app_key', 'currency']) as $group) {
            $pipeAppKey = $group->first()->pipe_app_key;
            $rows[] = $this->summaryRow($group, $group->first()->provider_key, $pipeAppKey, 'pipe_app', $pipeAppKey, $pipeAppKey, $now);
        }

        foreach ($this->dimensionGroups($records) as $dimensionGroup) {
            $rows[] = $this->summaryRow(
                $dimensionGroup['records'],
                $dimensionGroup['provider_key'],
                $dimensionGroup['pipe_app_key'],
                $dimensionGroup['dimension_type'],
                $dimensionGroup['dimension_key'],
                $dimensionGroup['dimension_label'],
                $now,
            );
        }

        foreach (array_values(array_filter($rows)) as $row) {
            CostDailySummary::query()->updateOrCreate(
                ['summary_key' => $row['summary_key']],
                $row,
            );
        }

        return count($rows);
    }

    /** @param Collection<int, CostRecord> $records */
    private function groupRecords(Collection $records, array $fields): Collection
    {
        return $records->groupBy(function (CostRecord $record) use ($fields): string {
            return implode('|', array_map(fn(string $field): string => (string) $record->{$field}, $fields));
        });
    }

    /** @param Collection<int, CostRecord> $records */
    private function summaryRow(
        Collection $records,
        string $providerKey,
        ?string $pipeAppKey,
        ?string $dimensionType,
        ?string $dimensionKey,
        ?string $dimensionLabel,
        CarbonImmutable $now,
    ): ?array {
        if ($records->isEmpty()) {
            return null;
        }

        $first = $records->first();

        $amount = $records->reduce(fn(float $carry, CostRecord $record): float => $carry + (float) $record->amount, 0.0);
        $bucketDate = CarbonImmutable::parse((string) $first->bucket_date)->toDateString();
        $summaryKey = hash('sha256', implode('|', [
            $bucketDate,
            $providerKey,
            $pipeAppKey ?? '_',
            $dimensionType ?? '_',
            $dimensionKey ?? '_',
            $first->currency,
        ]));

        return [
            'summary_date' => $bucketDate,
            'provider_key' => $providerKey,
            'pipe_app_key' => $pipeAppKey,
            'dimension_type' => $dimensionType,
            'dimension_key' => $dimensionKey,
            'dimension_label' => $dimensionLabel,
            'amount' => number_format($amount, 8, '.', ''),
            'currency' => $first->currency,
            'record_count' => $records->count(),
            'calculated_at' => $now,
            'summary_key' => $summaryKey,
        ];
    }

    /** @param Collection<int, CostRecord> $records */
    private function dimensionGroups(Collection $records): array
    {
        $mappings = CostDimensionMapping::query()
            ->where('is_enabled', true)
            ->get()
            ->keyBy(fn(CostDimensionMapping $mapping): string => $mapping->provider_key . ':' . $mapping->dimension_type . ':' . $mapping->external_id);

        $dimensionFields = [
            CostProviderKey::OpenAi->value => [
                'project' => 'external_project_id',
                'api_key' => 'external_api_key_id',
                'line_item' => 'line_item',
            ],
            CostProviderKey::LaravelCloud->value => [
                'application' => 'external_application_id',
                'environment' => 'external_environment_id',
                'resource_type' => 'resource_type',
                'line_item' => 'line_item',
            ],
        ];

        $groups = [];

        foreach ($dimensionFields as $providerKey => $fields) {
            foreach ($fields as $dimensionType => $field) {
                $filtered = $records
                    ->where('provider_key', $providerKey)
                    ->filter(fn(CostRecord $record): bool => $record->{$field} !== null);

                foreach ($this->groupRecords($filtered, ['bucket_date', 'provider_key', $field, 'currency']) as $group) {
                    $first = $group->first();

                    $dimensionKey = (string) $first->{$field};
                    $mapping = $mappings->get($providerKey . ':' . $dimensionType . ':' . $dimensionKey);
                    $pipeAppKey = $first->pipe_app_key;
                    $dimensionLabel = $dimensionKey;

                    if ($mapping instanceof CostDimensionMapping) {
                        $pipeAppKey = $mapping->pipe_app_key ?? $pipeAppKey;
                        $dimensionLabel = $mapping->display_name ?? $dimensionLabel;
                    }

                    $groups[] = [
                        'records' => $group,
                        'provider_key' => $providerKey,
                        'pipe_app_key' => $pipeAppKey,
                        'dimension_type' => $dimensionType,
                        'dimension_key' => $dimensionKey,
                        'dimension_label' => $dimensionLabel,
                    ];
                }
            }
        }

        return $groups;
    }
}
