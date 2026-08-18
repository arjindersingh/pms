<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSessionHistory;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSessionTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_successful_login_activity_and_logout_are_recorded(): void
    {
        $agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/126.0.0.0 Safari/537.36';

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.24'])
            ->withHeader('User-Agent', $agent)
            ->post(route('administrator.login.store'), [
                'email' => 'admin@example.com', 'password' => 'password', 'remember' => '1',
            ])->assertRedirect(route('admin.dashboard'));

        $session = UserSessionHistory::query()->sole();
        $this->assertSame('203.0.113.24', $session->ip_address);
        $this->assertSame('Chrome', $session->browser);
        $this->assertSame('126.0.0.0', $session->browser_version);
        $this->assertSame('Windows 11/10', $session->operating_system);
        $this->assertSame('desktop', $session->device_type);
        $this->assertTrue($session->remembered);
        $this->assertNull($session->logged_out_at);

        $this->travel(5)->minutes();
        $this->get(route('admin.dashboard'))->assertOk();
        $this->post(route('logout'))
            ->assertRedirect(route('administrator.login'))
            ->assertHeader('Clear-Site-Data', '"cache", "cookies", "storage"')
            ->assertHeader('Cache-Control', 'no-store, private');

        $session->refresh();
        $this->assertNotNull($session->logged_out_at);
        $this->assertSame('logout', $session->ended_reason);
        $this->assertGreaterThanOrEqual(300, $session->duration_seconds);
        $this->assertGreaterThanOrEqual(2, $session->request_count);
        $this->assertDatabaseHas('user_session_activities', ['user_session_history_id' => $session->id, 'route_name' => 'admin.dashboard', 'response_status' => 200]);
    }

    public function test_authenticated_pages_are_not_cached_by_the_browser(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');
    }

    public function test_admin_can_filter_reports_and_open_session_details(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $session = UserSessionHistory::query()->create([
            'user_id' => $admin->id, 'session_hash' => hash('sha256', 'example'),
            'ip_address' => '198.51.100.7', 'browser' => 'Firefox', 'browser_version' => '128',
            'operating_system' => 'Linux', 'device_type' => 'desktop',
            'logged_in_at' => now()->subMinutes(10), 'last_seen_at' => now(), 'request_count' => 4,
        ]);
        $session->activities()->create(['method' => 'GET', 'path' => '/talent/dashboard', 'route_name' => 'talent.dashboard', 'response_status' => 200, 'occurred_at' => now()]);

        $this->actingAs($admin)->get(route('admin.sessions.index', ['search' => '198.51.100.7', 'status' => 'active']))
            ->assertOk()->assertSee('User session reports')->assertSee('198.51.100.7')->assertSee('Firefox 128');

        $this->actingAs($admin)->get(route('admin.sessions.show', $session))
            ->assertOk()->assertSee('Session details')->assertSee('talent.dashboard')->assertSee('/talent/dashboard');
    }

    public function test_active_now_counts_logged_in_users_instead_of_sessions(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $talent = User::query()->where('email', 'talent@example.com')->firstOrFail();

        foreach (['first-session', 'second-session'] as $sessionHash) {
            UserSessionHistory::query()->create([
                'user_id' => $talent->id,
                'session_hash' => hash('sha256', $sessionHash),
                'logged_in_at' => now()->subMinutes(5),
                'last_seen_at' => now(),
            ]);
        }

        $this->actingAs($admin)->get(route('admin.sessions.index'))
            ->assertOk()
            ->assertSeeInOrder(['>1</strong><small>Active now'], false)
            ->assertSee('Demo Talent')
            ->assertDontSee('Agency Recruiter');
    }

    public function test_non_administrator_cannot_view_session_reports(): void
    {
        $talent = User::query()->where('email', 'talent@example.com')->firstOrFail();

        $this->actingAs($talent)->get(route('admin.sessions.index'))->assertForbidden();
    }

    public function test_failed_login_does_not_create_session_history(): void
    {
        $this->post(route('login.store'), ['email' => 'talent@example.com', 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('user_session_histories', 0);
    }
}
