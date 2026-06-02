<?php

namespace Tests\Unit;

use App\Models\CostDailySummary;
use App\Models\CostProvider;
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
            'provider_key' => CostProvider::OPENAI,
            'dimension_type' => null,
            'amount' => 10,
            'currency' => 'usd',
            'record_count' => 1,
            'calculated_at' => $now,
            'summary_key' => hash('sha256', 'openai'),
        ]);

        CostDailySummary::query()->create([
            'summary_date' => '2026-06-10',
            'provider_key' => CostProvider::LARAVEL_CLOUD,
            'dimension_type' => null,
            'amount' => 5,
            'currency' => 'usd',
            'record_count' => 1,
            'calculated_at' => $now,
            'summary_key' => hash('sha256', 'laravel_cloud'),
        ]);

        CostDailySummary::query()->create([
            'summary_date' => '2026-06-01',
            'provider_key' => CostProvider::ALL,
            'dimension_type' => null,
            'amount' => 10,
            'currency' => 'usd',
            'record_count' => 1,
            'calculated_at' => $now,
            'summary_key' => hash('sha256', 'all:1'),
        ]);

        CostDailySummary::query()->create([
            'summary_date' => '2026-06-10',
            'provider_key' => CostProvider::ALL,
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
        $this->assertSame(0.0, $summary['yesterday_cost']);
        $this->assertSame(45.0, $summary['month_end_forecast']);
    }
}
