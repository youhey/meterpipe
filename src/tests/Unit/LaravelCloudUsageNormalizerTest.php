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
            'data' => [
                'summary' => ['current_spend_cents' => 2050],
                'resources' => [
                    'total_cost_cents' => 375,
                    'databases' => [[
                        'id' => 'db_1',
                        'name' => 'mysql',
                        'total_cost_cents' => 375,
                    ]],
                ],
                'addons' => [
                    'total_cost_cents' => 125,
                    'items' => [[
                        'name' => 'private networking',
                        'total_cents' => 125,
                    ]],
                ],
                'application_totals' => [
                    'total_cost_cents' => 1225,
                    'applications' => [[
                        'id' => 'app_digest',
                        'name' => 'digestpipe',
                        'total_cost_cents' => 1225,
                    ]],
                ],
                'environment_usage' => [
                    'total_cost_cents' => 950,
                    'items' => [[
                        'id' => 'env_prod',
                        'application_id' => 'app_digest',
                        'name' => 'production',
                        'total_cost_cents' => 950,
                    ]],
                ],
            ],
            'meta' => [
                'currency' => 'USD',
                'period' => 0,
            ],
        ], CarbonImmutable::parse('2026-06-01'), CarbonImmutable::parse('2026-06-02'));

        $this->assertGreaterThanOrEqual(4, count($records));
        $this->assertSame(CostProviderKey::LaravelCloud->value, $records[0]['provider_key']);
        $this->assertSame('total', $records[0]['source_dimension_type']);
        $this->assertSame('20.50000000', $records[0]['amount']);
        $this->assertContains('app_digest', array_column($records, 'external_application_id'));
        $this->assertContains('database', array_column($records, 'resource_type'));
        $this->assertContains('add_on', array_column($records, 'resource_type'));
    }
}
