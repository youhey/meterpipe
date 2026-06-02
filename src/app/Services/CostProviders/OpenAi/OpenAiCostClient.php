<?php

namespace App\Services\CostProviders\OpenAi;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiCostClient
{
    public function __construct(
        private readonly ?string $token = null,
        private readonly string $baseUrl = 'https://api.openai.com/v1',
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function fetchCosts(CarbonImmutable $from, CarbonImmutable $to, ?string $groupBy = null): array
    {
        $token = $this->token ?? config('meterpipe.openai_admin_key');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('OpenAI Admin API key is not configured.');
        }

        $params = [
            'start_time' => $from->utc()->timestamp,
            'end_time' => $to->utc()->timestamp,
            'bucket_width' => '1d',
            'limit' => 180,
        ];

        if ($groupBy !== null) {
            $params['group_by'] = $groupBy;
        }

        $pages = [];
        $nextPage = null;

        do {
            $query = $params;

            if (is_string($nextPage)) {
                $query['page'] = $nextPage;
            }

            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(20)
                ->retry(3, 500)
                ->get($this->baseUrl . '/organization/costs', $query);

            if (! $response->successful()) {
                throw new RuntimeException('OpenAI Costs API request failed with HTTP ' . $response->status() . '.');
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                throw new RuntimeException('OpenAI Costs API response was not JSON object.');
            }

            $pages[] = $payload;
            $nextPage = data_get($payload, 'next_page');
        } while ((bool) data_get($payload, 'has_more', false) && is_string($nextPage) && $nextPage !== '');

        return $pages;
    }
}
