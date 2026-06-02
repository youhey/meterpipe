<?php

namespace Tests\Feature;

use App\Models\CostDailySummary;
use App\Models\CostProvider;
use App\Models\CostRecord;
use App\Models\CostSyncRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CostSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_openai_costs_command_persists_cost_records(): void
    {
        Config::set('meterpipe.openai_admin_key', 'test-openai-token');
        CostProvider::query()->create(['key' => CostProvider::OPENAI, 'name' => 'OpenAI', 'is_enabled' => true]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'has_more' => false,
                'data' => [[
                    'start_time' => 1_780_272_000,
                    'end_time' => 1_780_358_400,
                    'results' => [[
                        'amount' => ['value' => 4.25, 'currency' => 'usd'],
                        'project_id' => 'proj_digest',
                        'api_key_id' => 'key_abc',
                        'line_item' => 'responses',
                    ]],
                ]],
            ]),
        ]);

        $this->artisan('meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertSuccessful();

        $this->assertGreaterThan(0, CostRecord::query()->where('provider_key', CostProvider::OPENAI)->count());
        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProvider::OPENAI,
            'status' => CostSyncRun::SUCCEEDED,
        ]);
        $this->assertDatabaseHas('cost_daily_summaries', [
            'provider_key' => CostProvider::ALL,
        ]);
    }

    public function test_sync_laravel_cloud_costs_command_persists_cost_records(): void
    {
        Config::set('meterpipe.laravel_cloud_api_token', 'test-cloud-token');
        CostProvider::query()->create(['key' => CostProvider::LARAVEL_CLOUD, 'name' => 'Laravel Cloud', 'is_enabled' => true]);

        Http::fake([
            'cloud.laravel.com/*' => Http::response([
                'currency' => 'usd',
                'data' => [
                    'summary' => ['current_spend' => 14.5],
                    'applications' => [[
                        'id' => 'app_digest',
                        'name' => 'digestpipe',
                        'cost' => 8.25,
                    ]],
                ],
            ]),
        ]);

        $this->artisan('meterpipe:sync-laravel-cloud-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertSuccessful();

        $this->assertGreaterThan(0, CostRecord::query()->where('provider_key', CostProvider::LARAVEL_CLOUD)->count());
        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProvider::LARAVEL_CLOUD,
            'status' => CostSyncRun::SUCCEEDED,
        ]);
    }

    public function test_sync_failure_is_recorded(): void
    {
        Config::set('meterpipe.openai_admin_key', 'test-openai-token');
        CostProvider::query()->create(['key' => CostProvider::OPENAI, 'name' => 'OpenAI', 'is_enabled' => true]);

        Http::fake([
            'api.openai.com/*' => Http::response(['error' => 'denied'], 403),
        ]);

        $this->artisan('meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertFailed();

        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProvider::OPENAI,
            'status' => CostSyncRun::FAILED,
            'error_class' => 'Illuminate\\Http\\Client\\RequestException',
        ]);
    }

    public function test_recalculate_command_recreates_daily_summaries(): void
    {
        CostRecord::query()->create([
            'provider_key' => CostProvider::OPENAI,
            'source_record_key' => 'openai:test',
            'bucket_start' => '2026-06-01 00:00:00',
            'bucket_end' => '2026-06-02 00:00:00',
            'bucket_date' => '2026-06-01',
            'amount' => '7.50000000',
            'currency' => 'usd',
            'line_item' => 'responses',
            'raw_payload' => ['fixture' => true],
            'synced_at' => '2026-06-02 00:00:00',
        ]);

        $this->artisan('meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02')
            ->assertSuccessful();

        $this->assertGreaterThan(0, CostDailySummary::query()->count());
        $this->assertDatabaseHas('cost_daily_summaries', [
            'provider_key' => CostProvider::OPENAI,
            'amount' => '7.50000000',
        ]);
    }
}
