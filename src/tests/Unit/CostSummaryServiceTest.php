<?php

namespace Tests\Unit;

use App\Models\CostDailySummary;
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
        $hash = hash('sha256', '{}');

        CostDailySummary::query()->create([
            'source' => 'openai',
            'pipe_app_id' => null,
            'service' => 'completions',
            'amount' => 10,
            'currency' => 'usd',
            'dimensions' => [],
            'dimensions_hash' => $hash,
            'date' => '2026-06-01',
        ]);

        CostDailySummary::query()->create([
            'source' => 'laravel_cloud',
            'pipe_app_id' => null,
            'service' => 'compute',
            'amount' => 5,
            'currency' => 'usd',
            'dimensions' => [],
            'dimensions_hash' => $hash,
            'date' => '2026-06-10',
        ]);

        $summary = app(CostSummaryService::class)->monthlySummary($now);

        $this->assertSame(15.0, $summary['month_to_date']);
        $this->assertSame(10.0, $summary['openai_month_to_date']);
        $this->assertSame(5.0, $summary['laravel_cloud_month_to_date']);
        $this->assertSame(5.0, $summary['today_increment']);
        $this->assertSame(45.0, $summary['month_end_forecast']);
    }
}
