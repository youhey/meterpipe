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

        $this->get('/admin/login')
            ->assertRedirect(route('auth.google.redirect'));
    }

    public function test_only_allowed_email_can_access_filament_panel(): void
    {
        config()->set('meterpipe.admin.allowed_emails', ['admin@example.test']);

        $allowed = User::factory()->create(['email' => 'admin@example.test']);
        $denied = User::factory()->create(['email' => 'other@example.test']);

        $this->actingAs($allowed)->get('/admin')->assertOk();
        $this->actingAs($denied)->get('/admin')->assertForbidden();
    }

    public function test_dev_login_is_disabled_in_production_environment(): void
    {
        config([
            'app.env' => 'production',
            'meterpipe.admin.allowed_emails' => ['admin@example.test'],
            'meterpipe.admin.dev_login.enabled' => true,
            'meterpipe.admin.dev_login.email' => 'admin@example.test',
        ]);

        $this->get(route('local.admin.login'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_local_admin_login_returns_not_found_when_disabled(): void
    {
        config([
            'app.env' => 'local',
            'meterpipe.admin.allowed_emails' => ['admin@example.test'],
            'meterpipe.admin.dev_login.enabled' => false,
            'meterpipe.admin.dev_login.email' => 'admin@example.test',
        ]);

        $this->get(route('local.admin.login'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_local_admin_login_returns_not_found_when_email_is_missing(): void
    {
        config([
            'app.env' => 'local',
            'meterpipe.admin.allowed_emails' => ['admin@example.test'],
            'meterpipe.admin.dev_login.enabled' => true,
            'meterpipe.admin.dev_login.email' => '',
        ]);

        $this->get(route('local.admin.login'))->assertNotFound();
        $this->assertGuest();
    }

    public function test_local_admin_login_denies_dev_email_outside_allow_list(): void
    {
        config([
            'app.env' => 'local',
            'meterpipe.admin.allowed_emails' => ['admin@example.test'],
            'meterpipe.admin.dev_login.enabled' => true,
            'meterpipe.admin.dev_login.email' => 'other@example.test',
        ]);

        $this->get(route('local.admin.login'))->assertForbidden();

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'other@example.test']);
    }

    public function test_local_admin_login_logs_in_allowed_dev_user(): void
    {
        config([
            'app.env' => 'local',
            'meterpipe.admin.allowed_emails' => [' admin@example.test '],
            'meterpipe.admin.dev_login.enabled' => true,
            'meterpipe.admin.dev_login.email' => 'Admin@Example.Test',
        ]);

        $this->get(route('local.admin.login'))->assertRedirect(url('/admin'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'Admin@Example.Test',
            'name' => 'Local Admin',
        ]);
    }

    public function test_cost_dashboard_can_render_without_cost_data(): void
    {
        config()->set('meterpipe.admin.allowed_emails', ['admin@example.test']);

        $user = User::factory()->create(['email' => 'admin@example.test']);

        $this->actingAs($user)
            ->get('/admin/cost-dashboard')
            ->assertOk()
            ->assertSee('まだコストデータが同期されていません');
    }
}
