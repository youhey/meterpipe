<?php

namespace Tests\Feature;

use App\Enums\CostProviderKey;
use App\Enums\IntegrationProvider;
use App\Enums\PipeAppStatus;
use App\Models\AppIntegration;
use App\Models\CostDailySummary;
use App\Models\CostRecord;
use App\Models\CostSyncRun;
use App\Models\PipeApp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CostSyncCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_openai_costs_command_persists_cost_records(): void
    {
        Config::set('meterpipe.openai_admin_key', 'test-openai-token');
        Config::set(CostProviderKey::OpenAi->enabledConfigPath(), true);
        $this->createOpenAiProjectIntegration();

        Http::fake(function (Request $request) {
            $query = $this->queryParams($request);
            $groupBy = $query['group_by'] ?? null;
            $result = [
                'amount' => ['value' => 4.25, 'currency' => 'usd'],
            ];

            if ($groupBy === 'project_id') {
                $result['project_id'] = 'proj_pipekit';
            }

            if ($groupBy === 'line_item') {
                $result['line_item'] = 'responses';
            }

            return Http::response([
                'has_more' => false,
                'data' => [[
                    'start_time' => 1_780_272_000,
                    'end_time' => 1_780_358_400,
                    'results' => [$result],
                ]],
            ]);
        });

        $this->artisan('meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertSuccessful();

        Http::assertSentCount(3);
        Http::assertSent(function (Request $request): bool {
            $query = $this->queryParams($request);

            return in_array('proj_pipekit', (array) ($query['project_ids'] ?? []), true);
        });

        $this->assertSame(3, CostRecord::query()->where('provider_key', CostProviderKey::OpenAi->value)->count());
        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'status' => CostSyncRun::SUCCEEDED,
        ]);
        $this->assertDatabaseHas('cost_daily_summaries', [
            'provider_key' => CostProviderKey::All->value,
            'dimension_type' => null,
            'amount' => '4.25000000',
        ]);
        $this->assertDatabaseHas('cost_daily_summaries', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'dimension_type' => null,
            'amount' => '4.25000000',
        ]);
        $this->assertDatabaseHas('cost_daily_summaries', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'dimension_type' => 'project',
            'dimension_key' => 'proj_pipekit',
            'amount' => '4.25000000',
        ]);
        $this->assertDatabaseHas('cost_daily_summaries', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'dimension_type' => 'line_item',
            'dimension_key' => 'responses',
            'amount' => '4.25000000',
        ]);
    }

    public function test_sync_laravel_cloud_costs_command_persists_cost_records(): void
    {
        Config::set('meterpipe.laravel_cloud_api_token', 'test-cloud-token');
        Config::set(CostProviderKey::LaravelCloud->enabledConfigPath(), true);

        Http::fake([
            'cloud.laravel.com/*' => Http::response([
                'data' => [
                    'summary' => ['current_spend_cents' => 1450],
                    'application_totals' => [
                        'total_cost_cents' => 825,
                        'applications' => [[
                            'id' => 'app_digest',
                            'name' => 'digestpipe',
                            'total_cost_cents' => 825,
                        ]],
                    ],
                ],
                'meta' => [
                    'currency' => 'USD',
                    'period' => 0,
                    'available_periods' => [
                        ['from' => '2026-06-01', 'to' => '2026-06-30'],
                        ['from' => '2026-05-01', 'to' => '2026-05-31'],
                        ['from' => '2026-04-01', 'to' => '2026-04-30'],
                    ],
                ],
            ]),
        ]);

        $this->artisan('meterpipe:sync-laravel-cloud-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertSuccessful();

        Http::assertSent(function (Request $request): bool {
            $query = $this->queryParams($request);

            return ($query['period'] ?? null) === '0' && ! isset($query['from'], $query['to']);
        });

        $this->assertGreaterThan(0, CostRecord::query()->where('provider_key', CostProviderKey::LaravelCloud->value)->count());
        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProviderKey::LaravelCloud->value,
            'status' => CostSyncRun::SUCCEEDED,
        ]);
        $this->assertDailySummary(CostProviderKey::LaravelCloud->value, '2026-06-01', null, 14.5);
    }

    public function test_sync_laravel_cloud_costs_fetches_each_overlapping_billing_period(): void
    {
        Config::set('meterpipe.laravel_cloud_api_token', 'test-cloud-token');
        Config::set(CostProviderKey::LaravelCloud->enabledConfigPath(), true);

        Http::fake(function (Request $request) {
            $query = $this->queryParams($request);
            $period = (int) ($query['period'] ?? 0);

            return Http::response([
                'data' => [
                    'summary' => ['current_spend_cents' => $period === 0 ? 540 : 320],
                ],
                'meta' => [
                    'currency' => 'USD',
                    'period' => $period,
                    'available_periods' => [
                        ['from' => '2026-06-01', 'to' => '2026-06-30'],
                        ['from' => '2026-05-01', 'to' => '2026-05-31'],
                        ['from' => '2026-04-01', 'to' => '2026-04-30'],
                    ],
                ],
            ]);
        });

        $this->artisan('meterpipe:sync-laravel-cloud-costs --from=2026-05-20 --to=2026-06-03 --sync')
            ->assertSuccessful();

        Http::assertSentCount(2);
        Http::assertSent(fn(Request $request): bool => ($this->queryParams($request)['period'] ?? null) === '0');
        Http::assertSent(fn(Request $request): bool => ($this->queryParams($request)['period'] ?? null) === '1');

        $this->assertDailySummary(CostProviderKey::LaravelCloud->value, '2026-06-01', null, 5.4);
        $this->assertDailySummary(CostProviderKey::LaravelCloud->value, '2026-05-01', null, 3.2);
    }

    public function test_sync_failure_is_recorded(): void
    {
        Config::set('meterpipe.openai_admin_key', 'test-openai-token');
        Config::set(CostProviderKey::OpenAi->enabledConfigPath(), true);
        $this->createOpenAiProjectIntegration();

        Http::fake([
            'api.openai.com/*' => Http::response(['error' => 'denied'], 403),
        ]);

        $this->artisan('meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertFailed();

        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'status' => CostSyncRun::FAILED,
            'error_class' => 'Illuminate\\Http\\Client\\RequestException',
        ]);
    }

    public function test_sync_openai_costs_skips_without_enabled_project_integrations(): void
    {
        Config::set('meterpipe.openai_admin_key', 'test-openai-token');
        Config::set(CostProviderKey::OpenAi->enabledConfigPath(), true);

        Http::fake();

        $this->artisan('meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertSuccessful();

        Http::assertNothingSent();
        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'status' => CostSyncRun::SKIPPED,
        ]);
    }

    public function test_shared_openai_project_is_not_assigned_to_one_pipe_app(): void
    {
        Config::set('meterpipe.openai_admin_key', 'test-openai-token');
        Config::set(CostProviderKey::OpenAi->enabledConfigPath(), true);
        $this->createOpenAiProjectIntegration('proj_pipekit', 'digestpipe');
        $this->createOpenAiProjectIntegration('proj_pipekit', 'radiopipe');

        Http::fake([
            'api.openai.com/*' => Http::response([
                'has_more' => false,
                'data' => [[
                    'start_time' => 1_780_272_000,
                    'end_time' => 1_780_358_400,
                    'results' => [[
                        'amount' => ['value' => 4.25, 'currency' => 'usd'],
                        'project_id' => 'proj_pipekit',
                    ]],
                ]],
            ]),
        ]);

        $this->artisan('meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertSuccessful();

        $this->assertDatabaseHas('cost_records', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'source_dimension_type' => 'project',
            'external_project_id' => 'proj_pipekit',
            'pipe_app_key' => null,
        ]);
        $this->assertDatabaseMissing('cost_daily_summaries', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'dimension_type' => 'pipe_app',
        ]);
    }

    public function test_sync_disabled_provider_is_skipped_from_config(): void
    {
        Config::set('meterpipe.openai_admin_key', 'test-openai-token');
        Config::set(CostProviderKey::OpenAi->enabledConfigPath(), false);

        $this->artisan('meterpipe:sync-openai-costs --from=2026-06-01 --to=2026-06-02 --sync')
            ->assertSuccessful();

        $this->assertDatabaseHas('cost_sync_runs', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'status' => CostSyncRun::SKIPPED,
        ]);
        $this->assertSame(0, CostRecord::query()->where('provider_key', CostProviderKey::OpenAi->value)->count());
    }

    public function test_recalculate_command_recreates_daily_summaries(): void
    {
        CostRecord::query()->create([
            'provider_key' => CostProviderKey::OpenAi->value,
            'source_record_key' => 'openai:test',
            'bucket_start' => '2026-06-01 00:00:00',
            'bucket_end' => '2026-06-02 00:00:00',
            'bucket_date' => '2026-06-01',
            'amount' => '7.50000000',
            'currency' => 'usd',
            'source_dimension_type' => 'total',
            'line_item' => 'responses',
            'raw_payload' => ['fixture' => true],
            'synced_at' => '2026-06-02 00:00:00',
        ]);

        $this->artisan('meterpipe:recalculate-cost-summaries --from=2026-06-01 --to=2026-06-02')
            ->assertSuccessful();

        $this->assertGreaterThan(0, CostDailySummary::query()->count());
        $this->assertDatabaseHas('cost_daily_summaries', [
            'provider_key' => CostProviderKey::OpenAi->value,
            'amount' => '7.50000000',
        ]);
    }

    private function createOpenAiProjectIntegration(string $projectId = 'proj_pipekit', string $appKey = 'digestpipe'): void
    {
        $pipeApp = PipeApp::query()->updateOrCreate(
            ['key' => $appKey],
            [
                'name' => $appKey,
                'status' => PipeAppStatus::Active->value,
            ],
        );

        AppIntegration::query()->create([
            'pipe_app_id' => $pipeApp->id,
            'provider' => IntegrationProvider::OpenAi->value,
            'provider_project_id' => $projectId,
            'label' => 'Project',
            'enabled' => true,
        ]);
    }

    private function assertDailySummary(string $providerKey, string $summaryDate, ?string $dimensionType, float $amount): void
    {
        $summary = CostDailySummary::query()
            ->where('provider_key', $providerKey)
            ->whereDate('summary_date', $summaryDate)
            ->where('dimension_type', $dimensionType)
            ->first();

        $this->assertNotNull($summary);
        $this->assertSame($amount, (float) $summary->amount);
    }

    /** @return array<string, mixed> */
    private function queryParams(Request $request): array
    {
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query;
    }
}
