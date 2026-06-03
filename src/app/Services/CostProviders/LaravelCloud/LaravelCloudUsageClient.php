<?php

namespace App\Services\CostProviders\LaravelCloud;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class LaravelCloudUsageClient
{
    public function __construct(
        private readonly ?string $token = null,
        private readonly string $baseUrl = 'https://cloud.laravel.com/api',
    ) {
    }

    /** @return array<string, mixed> */
    public function fetchUsage(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $periods = $this->fetchUsagePeriods($from, $to);

        return $periods[0] ?? [];
    }

    /** @return list<array<string, mixed>> */
    public function fetchUsagePeriods(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $token = $this->token ?? config('meterpipe.laravel_cloud_api_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Laravel Cloud API token is not configured.');
        }

        $currentPeriodPayload = $this->fetchUsagePeriod($token, 0);
        $periods = $this->matchingPeriods($currentPeriodPayload, $from, $to);

        if ($periods === []) {
            return [];
        }

        $payloads = [];

        foreach ($periods as $period) {
            $payloads[] = $period === 0
                ? $currentPeriodPayload
                : $this->fetchUsagePeriod($token, $period);
        }

        return $payloads;
    }

    /** @return array<string, mixed> */
    private function fetchUsagePeriod(string $token, int $period): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->retry(3, 500)
            ->get($this->baseUrl . '/usage', [
                'period' => $period,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Laravel Cloud Usage API request failed with HTTP ' . $response->status() . '.');
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw new RuntimeException('Laravel Cloud Usage API response was not JSON object.');
        }

        return $payload;
    }

    /** @return list<int> */
    private function matchingPeriods(array $payload, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $availablePeriods = data_get($payload, 'meta.available_periods');

        if (! is_array($availablePeriods) || $availablePeriods === []) {
            return [0];
        }

        $periods = [];
        $from = $from->utc()->startOfDay();
        $to = $to->utc()->endOfDay();

        foreach ($availablePeriods as $period => $bounds) {
            if (! is_int($period) || $period < 0 || $period > 2 || ! is_array($bounds)) {
                continue;
            }

            $periodFrom = $this->date(data_get($bounds, 'from'))?->startOfDay();
            $periodTo = $this->date(data_get($bounds, 'to'))?->endOfDay();

            if ($periodFrom === null || $periodTo === null) {
                continue;
            }

            if ($periodFrom->lessThanOrEqualTo($to) && $periodTo->greaterThanOrEqualTo($from)) {
                $periods[] = $period;
            }
        }

        return array_values(array_unique($periods));
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_scalar($value) || (string) $value === '') {
            return null;
        }

        return CarbonImmutable::parse((string) $value, 'UTC');
    }
}
