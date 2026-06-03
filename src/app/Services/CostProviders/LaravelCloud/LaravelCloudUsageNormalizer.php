<?php

namespace App\Services\CostProviders\LaravelCloud;

use App\Enums\CostProviderKey;
use Carbon\CarbonImmutable;

class LaravelCloudUsageNormalizer
{
    /** @return list<array<string, mixed>> */
    public function normalize(array $payload, CarbonImmutable $from, CarbonImmutable $to): array
    {
        [$bucketStart, $bucketEnd] = $this->bucketPeriod($payload, $from, $to);
        $currency = strtolower((string) (data_get($payload, 'meta.currency') ?? data_get($payload, 'currency') ?? data_get($payload, 'data.currency') ?? config('meterpipe.default_currency', 'usd')));
        $records = [];

        $summaryAmount = $this->amount(data_get($payload, 'data.summary') ?? data_get($payload, 'summary') ?? $payload);

        if ($summaryAmount !== null) {
            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'organization',
                null,
                null,
                'organization_spend',
                null,
                'organization',
                $summaryAmount,
                $currency,
                $payload,
            );
        }

        $resourceRecords = 0;

        foreach ($this->resourceRows($payload) as $resource) {
            $resourceAmount = $this->amount($resource);

            if ($resourceAmount === null) {
                continue;
            }

            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'resource',
                null,
                null,
                $this->label($resource),
                $this->resourceType($resource),
                $this->label($resource),
                $resourceAmount,
                $currency,
                $resource,
            );
            $resourceRecords++;
        }

        $resourceTotalAmount = $this->amount(data_get($payload, 'data.resources') ?? data_get($payload, 'resources') ?? []);

        if ($resourceRecords === 0 && $resourceTotalAmount !== null) {
            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'resource',
                null,
                null,
                'resources',
                'resources',
                'resources',
                $resourceTotalAmount,
                $currency,
                data_get($payload, 'data.resources') ?? data_get($payload, 'resources') ?? [],
            );
        }

        $applicationRecords = 0;

        foreach ($this->applicationRows($payload) as $application) {
            $applicationId = $this->id($application);
            $applicationAmount = $this->amount($application);

            if ($applicationAmount !== null) {
                $records[] = $this->record(
                    $bucketStart,
                    $bucketEnd,
                    'application',
                    $applicationId,
                    null,
                    $this->label($application),
                    'compute',
                    $this->label($application),
                    $applicationAmount,
                    $currency,
                    $application,
                );
                $applicationRecords++;
            }
        }

        $applicationTotalAmount = $this->amount(data_get($payload, 'data.application_totals') ?? []);

        if ($applicationRecords === 0 && $applicationTotalAmount !== null) {
            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'application',
                null,
                null,
                'applications',
                'compute',
                'applications',
                $applicationTotalAmount,
                $currency,
                data_get($payload, 'data.application_totals') ?? [],
            );
        }

        $environmentRecords = 0;

        foreach ($this->environmentRows($payload) as $environment) {
            $environmentAmount = $this->amount($environment);

            if ($environmentAmount === null) {
                continue;
            }

            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'environment',
                $this->applicationId($environment),
                $this->id($environment),
                $this->label($environment),
                'compute',
                $this->label($environment),
                $environmentAmount,
                $currency,
                $environment,
            );
            $environmentRecords++;
        }

        $environmentTotalAmount = $this->amount(data_get($payload, 'data.environment_usage') ?? []);

        if ($environmentRecords === 0 && $environmentTotalAmount !== null) {
            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'environment',
                null,
                null,
                'environments',
                'compute',
                'environments',
                $environmentTotalAmount,
                $currency,
                data_get($payload, 'data.environment_usage') ?? [],
            );
        }

        $addonRecords = 0;

        foreach ($this->addonRows($payload) as $addon) {
            $addonAmount = $this->amount($addon);

            if ($addonAmount === null) {
                continue;
            }

            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'add_on',
                null,
                null,
                $this->label($addon),
                'add_on',
                $this->label($addon),
                $addonAmount,
                $currency,
                $addon,
            );
            $addonRecords++;
        }

        $addonTotalAmount = $this->amount(data_get($payload, 'data.addons') ?? data_get($payload, 'addons') ?? []);

        if ($addonRecords === 0 && $addonTotalAmount !== null) {
            $records[] = $this->record(
                $bucketStart,
                $bucketEnd,
                'add_on',
                null,
                null,
                'add_ons',
                'add_on',
                'add_ons',
                $addonTotalAmount,
                $currency,
                data_get($payload, 'data.addons') ?? data_get($payload, 'addons') ?? [],
            );
        }

        return array_values(array_filter($records, fn(array $record): bool => (float) $record['amount'] !== 0.0));
    }

    /** @return list<array<string, mixed>> */
    private function resourceRows(array $payload): array
    {
        $resources = data_get($payload, 'data.resources') ?? data_get($payload, 'resources') ?? [];
        $rows = [];

        if (! is_array($resources)) {
            return [];
        }

        foreach ($resources as $type => $items) {
            if (! is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $item['resource_type'] ??= is_string($type) ? str($type)->singular()->snake()->toString() : 'resource';
                $rows[] = $item;
            }
        }

        return $rows;
    }

    /** @return list<array<string, mixed>> */
    private function applicationRows(array $payload): array
    {
        $applications = data_get($payload, 'data.application_totals.applications') ?? data_get($payload, 'data.applications') ?? data_get($payload, 'applications') ?? [];

        return is_array($applications) ? array_values(array_filter($applications, 'is_array')) : [];
    }

    /** @return list<array<string, mixed>> */
    private function environmentRows(array $payload): array
    {
        $rows = data_get($payload, 'data.environment_usage.items') ?? data_get($payload, 'environment_usage.items') ?? [];

        if (is_array($rows) && $rows !== []) {
            return array_values(array_filter($rows, 'is_array'));
        }

        $environments = [];

        foreach ($this->applicationRows($payload) as $application) {
            $applicationId = $this->id($application);
            $items = data_get($application, 'environments', []);

            if (! is_array($items)) {
                continue;
            }

            foreach ($items as $environment) {
                if (! is_array($environment)) {
                    continue;
                }

                $environment['application_id'] ??= $applicationId;
                $environments[] = $environment;
            }
        }

        return $environments;
    }

    /** @return list<array<string, mixed>> */
    private function addonRows(array $payload): array
    {
        $addons = data_get($payload, 'data.addons.items') ?? data_get($payload, 'addons.items') ?? data_get($payload, 'data.add_ons') ?? data_get($payload, 'data.addons') ?? data_get($payload, 'add_ons') ?? data_get($payload, 'addons') ?? [];

        return is_array($addons) ? array_values(array_filter($addons, 'is_array')) : [];
    }

    private function record(
        CarbonImmutable $bucketStart,
        CarbonImmutable $bucketEnd,
        string $dimensionType,
        ?string $applicationId,
        ?string $environmentId,
        ?string $lineItem,
        ?string $resourceType,
        ?string $serviceName,
        string $amount,
        string $currency,
        array $rawPayload,
    ): array {
        $sourceRecordKey = implode(':', [
            CostProviderKey::LaravelCloud->value,
            $bucketStart->timestamp,
            $bucketEnd->timestamp,
            $dimensionType,
            $applicationId ?? '_',
            $environmentId ?? '_',
            $lineItem ?? '_',
            $resourceType ?? '_',
            strtolower($currency),
        ]);

        return [
            'provider_key' => CostProviderKey::LaravelCloud->value,
            'source_record_key' => $sourceRecordKey,
            'bucket_start' => $bucketStart,
            'bucket_end' => $bucketEnd,
            'bucket_date' => $bucketStart->toDateString(),
            'amount' => $amount,
            'currency' => strtolower($currency),
            'pipe_app_key' => null,
            'source_dimension_type' => $this->sourceDimensionType($dimensionType),
            'external_project_id' => null,
            'external_api_key_id' => null,
            'external_application_id' => $applicationId,
            'external_environment_id' => $environmentId,
            'line_item' => $lineItem,
            'resource_type' => $resourceType,
            'service_name' => $serviceName,
            'quantity' => $this->quantity($rawPayload),
            'unit' => $this->unit($rawPayload),
            'raw_payload' => $rawPayload,
        ];
    }

    private function sourceDimensionType(string $dimensionType): string
    {
        return match ($dimensionType) {
            'organization' => 'total',
            default => $dimensionType,
        };
    }

    /** @return array{0: CarbonImmutable, 1: CarbonImmutable} */
    private function bucketPeriod(array $payload, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $period = data_get($payload, 'meta.period');
        $availablePeriods = data_get($payload, 'meta.available_periods');

        if (is_numeric($period) && is_array($availablePeriods)) {
            $bounds = $availablePeriods[(int) $period] ?? null;

            if (is_array($bounds)) {
                $periodFrom = $this->date(data_get($bounds, 'from'))?->startOfDay();
                $periodTo = $this->date(data_get($bounds, 'to'))?->endOfDay();

                if ($periodFrom instanceof CarbonImmutable && $periodTo instanceof CarbonImmutable) {
                    return [$periodFrom, $periodTo];
                }
            }
        }

        return [$from->utc()->startOfDay(), $to->utc()->endOfDay()];
    }

    private function amount(mixed $row): ?string
    {
        if (! is_array($row)) {
            return null;
        }

        $value = data_get($row, 'amount.value')
            ?? data_get($row, 'cost.value')
            ?? data_get($row, 'spend.value')
            ?? data_get($row, 'total_cost.value')
            ?? data_get($row, 'estimated_cost.value')
            ?? data_get($row, 'current_spend')
            ?? data_get($row, 'total_spend')
            ?? data_get($row, 'spend')
            ?? data_get($row, 'cost')
            ?? data_get($row, 'total_cost')
            ?? data_get($row, 'estimated_cost');

        if (is_numeric($value)) {
            return number_format((float) $value, 8, '.', '');
        }

        $cents = data_get($row, 'current_spend_cents')
            ?? data_get($row, 'total_cost_cents')
            ?? data_get($row, 'total_cents')
            ?? data_get($row, 'cost_cents')
            ?? data_get($row, 'used_cents');

        return is_numeric($cents) ? number_format((float) $cents / 100, 8, '.', '') : null;
    }

    private function id(array $row): ?string
    {
        $id = data_get($row, 'id') ?? data_get($row, 'uuid') ?? data_get($row, 'identifier');

        return is_scalar($id) && (string) $id !== '' ? (string) $id : null;
    }

    private function applicationId(array $row): ?string
    {
        $id = data_get($row, 'application_id') ?? data_get($row, 'application.id') ?? data_get($row, 'app_id');

        return is_scalar($id) && (string) $id !== '' ? (string) $id : null;
    }

    private function label(array $row): ?string
    {
        $label = data_get($row, 'name') ?? data_get($row, 'label') ?? data_get($row, 'identifier') ?? $this->id($row);

        return is_scalar($label) && (string) $label !== '' ? (string) $label : null;
    }

    private function resourceType(array $row): ?string
    {
        $type = data_get($row, 'resource_type') ?? data_get($row, 'type') ?? data_get($row, 'category');

        return is_scalar($type) && (string) $type !== '' ? str((string) $type)->snake()->toString() : null;
    }

    private function quantity(array $row): ?string
    {
        $quantity = data_get($row, 'quantity') ?? data_get($row, 'usage') ?? data_get($row, 'hours');

        return is_numeric($quantity) ? number_format((float) $quantity, 8, '.', '') : null;
    }

    private function unit(array $row): ?string
    {
        $unit = data_get($row, 'unit') ?? data_get($row, 'usage_unit');

        return is_scalar($unit) && (string) $unit !== '' ? (string) $unit : null;
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_scalar($value) || (string) $value === '') {
            return null;
        }

        return CarbonImmutable::parse((string) $value, 'UTC');
    }
}
