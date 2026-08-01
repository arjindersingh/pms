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

    public function test_individual_permission_overrides_the_assigned_role(): void
    {
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();
        $module = PortalModule::query()->where('slug', 'recruitment')->firstOrFail();

        $recruiter->permittedModules()->syncWithoutDetaching([$module->id => ['enabled' => false]]);

        $this->assertFalse(app(PortalAccess::class)->module($recruiter->fresh('userType'), $module));
        $this->actingAs($recruiter)->get(route('recruiter.dashboard'))->assertForbidden();
    }

    public function test_roles_are_category_specific_and_copy_permissions_to_the_user(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $talent = User::query()->where('email', 'talent@example.com')->firstOrFail();
        $talentRole = \App\Models\UserRole::query()->where('slug', 'candidate-viewer')->firstOrFail();
        $adminRole = \App\Models\UserRole::query()->where('slug', 'operations-administrator')->firstOrFail();

        $this->actingAs($administrator)->put(route('admin.accounts.role', $talent->id), ['user_role_id' => $talentRole->id])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $talent->id, 'user_role_id' => $talentRole->id]);
        $this->assertDatabaseHas('portal_menu_user', ['user_id' => $talent->id, 'can_view' => true]);

        $this->put(route('admin.accounts.role', $talent->id), ['user_role_id' => $adminRole->id])->assertSessionHasErrors('user_role_id');
    }

    public function test_role_template_changes_do_not_overwrite_individual_permissions(): void
    {
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();
        $menu = \App\Models\PortalMenu::query()->where('slug', 'job-postings')->firstOrFail();

        $recruiter->role->menus()->updateExistingPivot($menu->id, ['can_view' => false]);

        $this->assertTrue(app(PortalAccess::class)->menu($recruiter->fresh(['role','userType']), $menu, 'view'));
    }

    public function test_super_admin_has_full_permissions_without_permission_rows(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $administrator->permittedModules()->detach();
        $administrator->permittedMenus()->detach();

        foreach (PortalModule::query()->get() as $module) $this->assertTrue(app(PortalAccess::class)->module($administrator->fresh(['role','userType']), $module));
        $this->actingAs($administrator)->get(route('admin.roles.index'))->assertOk()->assertSee('Available roles');
        $this->get(route('recruiter.dashboard'))->assertOk();
        $this->get(route('talent.dashboard'))->assertOk();
    }

    public function test_admin_dashboard_exposes_permission_control_center_and_audit_log(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('ACCESS CONTROL CENTER')
            ->assertSee('Permission workflow')
            ->assertSee(route('admin.roles.index'), false)
            ->assertSee(route('admin.permission-audit'), false);

        $this->get(route('admin.permission-audit'))->assertOk()->assertSee('Permission audit log');
    }

    public function test_individual_permission_change_is_recorded_in_permission_audit(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $talent = User::query()->where('email', 'talent@example.com')->firstOrFail();
        $career = PortalModule::query()->where('slug', 'career')->firstOrFail();
        $findJobs = \App\Models\PortalMenu::query()->where('slug', 'find-jobs')->firstOrFail();

        $this->actingAs($administrator)->put(route('admin.accounts.permissions.update', $talent->id), [
            'modules' => [$career->id => 1],
            'menus' => [$findJobs->id => ['view' => 1]],
        ])->assertRedirect();

        $this->assertDatabaseHas('permission_audits', [
            'actor_id' => $administrator->id,
            'target_user_id' => $talent->id,
            'event' => 'individual_permissions_updated',
        ]);
        $this->assertNotNull($talent->fresh()->permissions_customized_at);
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
        $this->assertNotNull(User::where('email', 'new-recruiter@example.com')->first()->user_role_id);
        $this->assertNotNull(User::where('email', 'new-candidate@example.com')->first()->user_role_id);
    }

    public function test_only_administrators_can_manage_the_access_matrix(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.access'))->assertOk()->assertSee('Access Management');
        $this->actingAs($recruiter)->get(route('admin.access'))->assertForbidden();
    }

    public function test_dashboard_top_bar_contains_theme_module_and_account_menus(): void
    {
        $user = User::query()->where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($user)->get(route('recruiter.dashboard'))
            ->assertOk()
            ->assertSee('Change theme')
            ->assertSee('Your available workspaces')
            ->assertSee(route('account.profile'), false)
            ->assertSee(route('account.password'), false);
    }

    public function test_user_can_update_profile_and_password(): void
    {
        $user = User::query()->where('email', 'talent@example.com')->firstOrFail();

        $this->actingAs($user)->patch(route('account.profile.update'), [
            'name' => 'Updated Talent',
            'email' => 'updated@example.com',
        ])->assertRedirect();

        $this->put(route('account.password.update'), [
            'current_password' => 'password',
            'password' => 'NewPassword123',
            'password_confirmation' => 'NewPassword123',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Talent', 'email' => 'updated@example.com']);
    }

    public function test_user_can_save_persistent_error_display_settings(): void
    {
        $user = User::query()->where('email', 'talent@example.com')->firstOrFail();

        $this->actingAs($user)->put(route('account.error-settings.update'), [
            'placement' => 'dialog', 'font_family' => 'mono', 'font_size' => 18,
            'text_color' => '#111111', 'background_color' => '#ffeeee', 'accent_color' => '#cc0000',
            'density' => 'spacious', 'motion' => 'fade', 'show_icon' => '1',
            'allow_dismiss' => '0', 'group_messages' => '1', 'auto_dismiss_seconds' => 10,
        ])->assertRedirect();

        $this->assertDatabaseHas('user_error_settings', [
            'user_id' => $user->id, 'placement' => 'dialog', 'font_family' => 'mono',
            'font_size' => 18, 'accent_color' => '#cc0000', 'auto_dismiss_seconds' => 10,
        ]);

        $this->get(route('account.error-settings'))->assertOk()->assertSee('Dialogue box')->assertSee('value="#cc0000"', false);
    }

    public function test_saved_error_preferences_control_validation_error_renderer(): void
    {
        $user = User::query()->where('email', 'recruiter@example.com')->firstOrFail();
        $user->errorSetting()->create(['placement' => 'bottom_right', 'font_size' => 17]);

        $this->actingAs($user)->from(route('account.profile'))->patch(route('account.profile.update'), [
            'name' => '', 'email' => 'not-an-email',
        ])->assertRedirect(route('account.profile'));

        $this->get(route('account.profile'))
            ->assertOk()
            ->assertSee('error-display--bottom_right', false)
            ->assertSee('--error-size:17px', false)
            ->assertSee('We couldn’t complete that action');
    }

    public function test_only_administrators_can_open_account_review(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.accounts.index'))
            ->assertOk()->assertSee('Account review')->assertSee('Demo Recruiter');
        $this->actingAs($recruiter)->get(route('admin.accounts.index'))->assertForbidden();
    }

    public function test_administrator_can_suspend_an_account_with_an_audit_record(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $talent = User::query()->where('email', 'talent@example.com')->firstOrFail();

        $this->actingAs($administrator)->patch(route('admin.accounts.status', $talent->id), [
            'status' => 'suspended', 'reason' => 'Identity verification requires review.',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $talent->id, 'account_status' => 'suspended', 'is_active' => false]);
        $this->assertDatabaseHas('user_account_reviews', ['user_id' => $talent->id, 'reviewed_by' => $administrator->id, 'action' => 'status_changed', 'to_status' => 'suspended']);

        auth()->logout();
        $this->post(route('login.store'), ['email' => $talent->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_deleted_account_can_be_restored_for_review(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $recruiter = User::query()->where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($administrator)->delete(route('admin.accounts.destroy', $recruiter->id), ['reason' => 'Duplicate company account.'])
            ->assertRedirect(route('admin.accounts.index'));
        $this->assertSoftDeleted('users', ['id' => $recruiter->id]);

        $this->post(route('admin.accounts.restore', $recruiter->id))->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $recruiter->id, 'account_status' => 'pending_review', 'is_active' => false, 'deleted_at' => null]);
        $this->assertDatabaseHas('user_account_reviews', ['user_id' => $recruiter->id, 'action' => 'restored', 'to_status' => 'pending_review']);
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
