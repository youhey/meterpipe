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
        $token = $this->token ?? config('meterpipe.laravel_cloud_api_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('Laravel Cloud API token is not configured.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->retry(3, 500)
            ->get($this->baseUrl . '/usage', [
                'from' => $from->utc()->toDateString(),
                'to' => $to->utc()->toDateString(),
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
}
