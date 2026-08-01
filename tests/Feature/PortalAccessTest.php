<?php

namespace Tests\Feature;

use App\Livewire\Admin\AccessMatrix;
use App\Models\PortalModule;
use App\Models\User;
use App\Models\UserType;
use App\Services\PortalAccess;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_home_page_links_the_compiled_bootstrap_assets(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('href="/build/assets/app-', false);
        $response->assertSee('src="/build/assets/app-', false);

        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $compiledCss = public_path('build/'.$manifest['resources/css/app.css']['file']);

        $this->assertFileExists($compiledCss);
        $this->assertStringContainsString('.btn-primary', file_get_contents($compiledCss));
    }

    public function test_login_redirects_each_category_to_its_own_dashboard(): void
    {
        foreach ([
            'admin@example.com' => [route('administrator.login.store'), route('admin.dashboard')],
            'recruiter@example.com' => [route('login.store'), route('recruiter.dashboard')],
            'talent@example.com' => [route('login.store'), route('talent.dashboard')],
        ] as $email => [$login, $dashboard]) {
            $this->post($login, ['email' => $email, 'password' => 'password'])
                ->assertRedirect($dashboard);
            $this->post(route('logout'));
        }
    }

    public function test_users_can_only_open_their_category_dashboard(): void
    {
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($recruiter)->get(route('recruiter.dashboard'))->assertOk();
        $this->actingAs($recruiter)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($recruiter)->get(route('talent.dashboard'))->assertForbidden();
    }

    public function test_each_dashboard_renders_its_own_theme_and_navigation_hierarchy(): void
    {
        foreach ([
            'admin@example.com' => [route('admin.dashboard'), 'data-portal-area="administrator"', 'Access Control', 'Permission Setup'],
            'recruiter@example.com' => [route('recruiter.dashboard'), 'data-portal-area="recruiter"', 'Hiring Workspace', 'Candidates'],
            'talent@example.com' => [route('talent.dashboard'), 'data-portal-area="talent"', 'My Career', 'Opportunities'],
        ] as $email => [$dashboard, $theme, $levelOne, $levelTwo]) {
            $user = User::query()->where('email', $email)->firstOrFail();

            $this->actingAs($user)->get($dashboard)
                ->assertOk()
                ->assertSee($theme, false)
                ->assertSee($levelOne)
                ->assertSee($levelTwo)
                ->assertSee('QUICK ACCESS');
        }
    }

    public function test_subtypes_inherit_module_and_menu_permissions(): void
    {
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();
        $access = app(PortalAccess::class);

        $this->assertTrue($access->module($recruiter, 'recruitment'));
        $this->assertTrue($access->menu($recruiter, 'recruiter-dashboard', 'view'));
        $this->assertTrue($access->menu($recruiter, 'job-postings', 'create'));
    }

    public function test_subtype_assignment_overrides_its_parent(): void
    {
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();
        $module = PortalModule::query()->where('slug', 'recruitment')->firstOrFail();

        $recruiter->userType->modules()->syncWithoutDetaching([$module->id => ['enabled' => false]]);

        $this->assertFalse(app(PortalAccess::class)->module($recruiter->fresh('userType'), $module));
        $this->actingAs($recruiter)->get(route('recruiter.dashboard'))->assertForbidden();
    }

    public function test_crud_permissions_are_independent(): void
    {
        $talent = User::query()->where('email', 'talent@example.com')->firstOrFail();
        $access = app(PortalAccess::class);

        $this->assertTrue($access->menu($talent, 'find-jobs', 'view'));
        $this->assertFalse($access->menu($talent, 'find-jobs', 'create'));
        $this->assertFalse($access->menu($talent, 'find-jobs', 'update'));
        $this->assertFalse($access->menu($talent, 'find-jobs', 'delete'));
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        User::query()->where('email', 'talent@example.com')->update(['is_active' => false]);

        $this->post(route('login.store'), ['email' => 'talent@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_administrator_login_is_separate_from_public_login(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('/administrator');
        $this->get(route('admin.dashboard'))->assertRedirect(route('administrator.login'));

        $this->post(route('login.store'), ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('administrator.login.store'), ['email' => 'recruiter@example.com', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_recruiters_and_talents_can_register_publicly(): void
    {
        $this->post(route('register.recruiter.store'), [
            'name' => 'New Recruiter',
            'email' => 'new-recruiter@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => true,
        ])->assertRedirect(route('recruiter.dashboard'));
        $this->assertAuthenticated();
        $this->post(route('logout'));

        $this->post(route('register.talent.store'), [
            'name' => 'New Candidate',
            'email' => 'new-candidate@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'terms' => true,
        ])->assertRedirect(route('talent.dashboard'));
        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', ['email' => 'new-recruiter@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'new-candidate@example.com']);
    }

    public function test_only_administrators_can_manage_the_access_matrix(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.access'))->assertOk()->assertSee('Access Management');
        $this->actingAs($recruiter)->get(route('admin.access'))->assertForbidden();
    }

    public function test_access_matrix_persists_explicit_subtype_permissions(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $corporate = UserType::query()->where('slug', 'corporate-recruiter')->firstOrFail();
        $recruitment = PortalModule::query()->where('slug', 'recruitment')->firstOrFail();

        Livewire::actingAs($administrator)
            ->test(AccessMatrix::class)
            ->set('selectedUserTypeId', $corporate->id)
            ->set("moduleAccess.{$recruitment->id}", false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('portal_module_user_type', [
            'user_type_id' => $corporate->id,
            'portal_module_id' => $recruitment->id,
            'enabled' => false,
        ]);
    }
}
