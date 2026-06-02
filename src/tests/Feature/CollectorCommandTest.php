<?php

namespace Tests\Feature;

use App\Enums\CollectorRunStatus;
use App\Models\CollectorRun;
use App\Models\CostDailySummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectorCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_fake_openai_cost_collector_creates_run_and_cost_data(): void
    {
        $this->artisan('meterpipe:collect --collector=fake-openai-cost')
            ->assertSuccessful();

        $this->assertDatabaseHas('collector_runs', [
            'collector_name' => 'fake-openai-cost',
            'status' => 'succeeded',
        ]);

        $this->assertGreaterThan(0, CostDailySummary::query()->where('provider_key', 'openai')->count());
    }

    public function test_failing_collector_marks_run_as_failed(): void
    {
        $this->artisan('meterpipe:collect --collector=failing-test')
            ->assertFailed();

        $run = CollectorRun::query()->latest('id')->firstOrFail();

        $this->assertSame('failing-test', $run->collector_name);
        $this->assertSame(CollectorRunStatus::Failed, $run->status);
        $this->assertStringContainsString('Fake collector failure', (string) $run->error_message);
    }

    public function test_dry_run_does_not_persist_data(): void
    {
        $this->artisan('meterpipe:collect --collector=fake-openai-cost --dry-run')
            ->assertSuccessful();

        $this->assertSame(0, CollectorRun::query()->count());
        $this->assertSame(0, CostDailySummary::query()->count());
    }
}
