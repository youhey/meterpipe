<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_panel_redirects_unauthenticated_users(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_only_allowed_email_can_access_filament_panel(): void
    {
        config()->set('meterpipe.admin_allowed_emails', ['admin@example.test']);

        $allowed = User::factory()->create(['email' => 'admin@example.test']);
        $denied = User::factory()->create(['email' => 'other@example.test']);

        $this->actingAs($allowed)->get('/admin')->assertOk();
        $this->actingAs($denied)->get('/admin')->assertForbidden();
    }

    public function test_dev_login_is_disabled_in_production_environment(): void
    {
        config()->set('meterpipe.admin_dev_login_enabled', true);
        config()->set('meterpipe.admin_dev_login_email', 'admin@example.test');
        $this->app->detectEnvironment(fn(): string => 'production');

        $this->get('/admin/dev-login')->assertNotFound();
    }

    public function test_cost_dashboard_can_render_without_cost_data(): void
    {
        config()->set('meterpipe.admin_allowed_emails', ['admin@example.test']);

        $user = User::factory()->create(['email' => 'admin@example.test']);

        $this->actingAs($user)
            ->get('/admin/cost-dashboard')
            ->assertOk()
            ->assertSee('まだコストデータが同期されていません');
    }
}
