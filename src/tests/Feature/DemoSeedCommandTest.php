<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\CollectorRun;
use App\Models\CostDailySummary;
use App\Models\MetricSnapshot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeedCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_command_creates_fake_dashboard_data(): void
    {
        $this->artisan('meterpipe:demo:seed')
            ->assertSuccessful();

        $this->assertGreaterThan(0, CostDailySummary::query()->count());
        $this->assertGreaterThan(0, MetricSnapshot::query()->count());
        $this->assertGreaterThan(0, AnalyticsEvent::query()->count());
        $this->assertGreaterThan(0, CollectorRun::query()->count());
    }
}
