<?php

namespace Tests\Unit;

use App\Enums\CostProviderKey;
use App\Models\CostDailySummary;
use App\Models\CostRecord;
use App\Services\CostSummaryService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostSummaryServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_monthly_summary_calculates_totals_and_forecast(): void
    {
        $now = CarbonImmutable::parse('2026-06-10 12:00:00');

        CostDailySummary::query()->create([
            'summary_date' => '2026-06-01',
            'provider_key' => CostProviderKey::OpenAi->value,
            'dimension_type' => null,
            'amount' => 10,
            'currency' => 'usd',
            'record_count' => 1,
            'calculated_at' => $now,
            'summary_key' => hash('sha256', 'openai'),
        ]);

        CostRecord::query()->create([
            'provider_key' => CostProviderKey::LaravelCloud->value,
            'source_record_key' => 'laravel_cloud:billing-period',
            'bucket_start' => '2026-05-25 00:00:00',
            'bucket_end' => '2026-06-24 23:59:59',
            'bucket_date' => '2026-05-25',
            'amount' => '5.00000000',
            'currency' => 'usd',
            'source_dimension_type' => 'total',
            'raw_payload' => ['fixture' => true],
            'synced_at' => $now,
        ]);

        CostRecord::query()->create([
            'provider_key' => CostProviderKey::LaravelCloud->value,
            'source_record_key' => 'laravel_cloud:previous-billing-period',
            'bucket_start' => '2026-04-25 00:00:00',
            'bucket_end' => '2026-05-24 23:59:59',
            'bucket_date' => '2026-04-25',
            'amount' => '99.00000000',
            'currency' => 'usd',
            'source_dimension_type' => 'total',
            'raw_payload' => ['fixture' => true],
            'synced_at' => $now,
        ]);

        CostDailySummary::query()->create([
            'summary_date' => '2026-06-01',
            'provider_key' => CostProviderKey::All->value,
            'dimension_type' => null,
            'amount' => 10,
            'currency' => 'usd',
            'record_count' => 1,
            'calculated_at' => $now,
            'summary_key' => hash('sha256', 'all:1'),
        ]);

        CostDailySummary::query()->create([
            'summary_date' => '2026-06-10',
            'provider_key' => CostProviderKey::All->value,
            'dimension_type' => null,
            'amount' => 5,
            'currency' => 'usd',
            'record_count' => 1,
            'calculated_at' => $now,
            'summary_key' => hash('sha256', 'all:2'),
        ]);

        $summary = app(CostSummaryService::class)->monthlySummary($now);

        $this->assertSame(15.0, $summary['month_to_date']);
        $this->assertSame(10.0, $summary['openai_month_to_date']);
        $this->assertSame(5.0, $summary['laravel_cloud_month_to_date']);
        $this->assertSame([
            'amount' => 5.0,
            'bucket_start' => '2026-05-25',
            'bucket_end' => '2026-06-24',
        ], $summary['laravel_cloud_billing_period']);
        $this->assertSame(0.0, $summary['yesterday_cost']);
        $this->assertSame(35.0, $summary['month_end_forecast']);
    }
}
