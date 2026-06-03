<?php

namespace Tests\Unit;

use App\Enums\CostProviderKey;
use App\Services\CostProviders\LaravelCloud\LaravelCloudUsageNormalizer;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class LaravelCloudUsageNormalizerTest extends TestCase
{
    public function test_normalizes_usage_fixture_records(): void
    {
        $records = app(LaravelCloudUsageNormalizer::class)->normalize([
            'currency' => 'usd',
            'data' => [
                'summary' => ['current_spend' => 20.5],
                'applications' => [[
                    'id' => 'app_digest',
                    'name' => 'digestpipe',
                    'cost' => 12.25,
                    'environments' => [[
                        'id' => 'env_prod',
                        'name' => 'production',
                        'cost' => 9.5,
                    ]],
                ]],
                'resources' => [
                    'databases' => [[
                        'id' => 'db_1',
                        'name' => 'mysql',
                        'cost' => 3.75,
                    ]],
                ],
            ],
        ], CarbonImmutable::parse('2026-06-01'), CarbonImmutable::parse('2026-06-02'));

        $this->assertGreaterThanOrEqual(4, count($records));
        $this->assertSame(CostProviderKey::LaravelCloud->value, $records[0]['provider_key']);
        $this->assertContains('app_digest', array_column($records, 'external_application_id'));
        $this->assertContains('database', array_column($records, 'resource_type'));
    }
}
