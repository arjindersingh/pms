<?php

namespace Database\Seeders;

use App\Enums\UserCategory;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\User;
use App\Models\UserType;
use App\Models\UserRole;
use App\Services\RolePermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $administrator = $this->type(UserCategory::Administrator, 'Administrator', 'administrator');
        $recruiter = $this->type(UserCategory::Recruiter, 'Recruiter', 'recruiter');
        $talent = $this->type(UserCategory::Talent, 'Talent', 'talent');

        $superAdmin = $this->type(UserCategory::Administrator, 'Super Administrator', 'super-administrator', $administrator);
        $placementOfficer = $this->type(UserCategory::Administrator, 'Placement Officer', 'placement-officer', $administrator);
        $corporateRecruiter = $this->type(UserCategory::Recruiter, 'Corporate Recruiter', 'corporate-recruiter', $recruiter);
        $staffingAgency = $this->type(UserCategory::Recruiter, 'Staffing Agency', 'staffing-agency', $recruiter);
        $graduate = $this->type(UserCategory::Talent, 'Graduate', 'graduate', $talent);
        $experienced = $this->type(UserCategory::Talent, 'Experienced Professional', 'experienced-professional', $talent);

        $administration = $this->module('Administration', 'administration', 'bi-shield-lock', 10);
        $recruitment = $this->module('Recruitment', 'recruitment', 'bi-building', 20);
        $career = $this->module('Career', 'career', 'bi-briefcase', 30);

        $adminDashboard = $this->menu($administration, 'Dashboard', 'admin-dashboard', 'admin.dashboard', 'bi-speedometer2', 10);
        $accessControl = $this->menu($administration, 'Access Control', 'access-control', null, 'bi-shield-lock', 20);
        $userManagement = $this->menu($administration, 'User Management', 'user-management', null, 'bi-people', 10, $accessControl);
        $permissionSetup = $this->menu($administration, 'Permission Setup', 'permission-setup', null, 'bi-sliders', 20, $accessControl);
        $adminMenus = [
            $adminDashboard, $accessControl, $userManagement, $permissionSetup,
            $this->menu($administration, 'Account Review', 'account-review', 'admin.accounts.index', 'bi-person-check', 30, $accessControl),
            $this->menu($administration, 'Roles & Permissions', 'role-management', 'admin.roles.index', 'bi-person-gear', 40, $accessControl),
            $this->menu($administration, 'Permission Audit', 'permission-audit', 'admin.permission-audit', 'bi-clock-history', 50, $accessControl),
            $this->menu($administration, 'User Types', 'user-types', null, 'bi-diagram-3', 10, $userManagement),
            $this->menu($administration, 'Member Directory', 'member-directory', null, 'bi-person-lines-fill', 20, $userManagement),
            $this->menu($administration, 'Module Access', 'module-access', 'admin.access', 'bi-grid', 10, $permissionSetup),
            $this->menu($administration, 'Menu Permissions', 'menu-permissions', 'admin.access', 'bi-list-check', 20, $permissionSetup),
        ];

        $recruiterDashboard = $this->menu($recruitment, 'Dashboard', 'recruiter-dashboard', 'recruiter.dashboard', 'bi-speedometer2', 10);
        $hiringWorkspace = $this->menu($recruitment, 'Hiring Workspace', 'hiring-workspace', null, 'bi-briefcase', 20);
        $jobs = $this->menu($recruitment, 'Jobs', 'jobs', null, 'bi-megaphone', 10, $hiringWorkspace);
        $candidates = $this->menu($recruitment, 'Candidates', 'candidates', null, 'bi-people', 20, $hiringWorkspace);
        $recruiterMenus = [
            $recruiterDashboard, $hiringWorkspace, $jobs, $candidates,
            $this->menu($recruitment, 'All Job Postings', 'job-postings', null, 'bi-card-list', 10, $jobs),
            $this->menu($recruitment, 'Create a Job', 'create-job', null, 'bi-plus-square', 20, $jobs),
            $this->menu($recruitment, 'Applications', 'recruiter-applications', null, 'bi-inboxes', 10, $candidates),
            $this->menu($recruitment, 'Shortlisted Talent', 'shortlisted-talent', null, 'bi-person-check', 20, $candidates),
        ];

        $talentDashboard = $this->menu($career, 'Dashboard', 'talent-dashboard', 'talent.dashboard', 'bi-speedometer2', 10);
        $careerWorkspace = $this->menu($career, 'My Career', 'career-workspace', null, 'bi-compass', 20);
        $opportunities = $this->menu($career, 'Opportunities', 'opportunities', null, 'bi-search', 10, $careerWorkspace);
        $applications = $this->menu($career, 'Applications', 'applications', null, 'bi-file-earmark-person', 20, $careerWorkspace);
        $talentMenus = [
            $talentDashboard, $careerWorkspace, $opportunities, $applications,
            $this->menu($career, 'Recommended Jobs', 'find-jobs', null, 'bi-stars', 10, $opportunities),
            $this->menu($career, 'Saved Jobs', 'saved-jobs', null, 'bi-bookmark-heart', 20, $opportunities),
            $this->menu($career, 'Active Applications', 'my-applications', null, 'bi-hourglass-split', 10, $applications),
            $this->menu($career, 'Application History', 'application-history', null, 'bi-clock-history', 20, $applications),
        ];

        $this->grant($administrator, $administration, $adminMenus, true);
        $this->grant($recruiter, $recruitment, $recruiterMenus, true);
        $this->grant($talent, $career, $talentMenus, false);

        $superAdminRole = $this->role(UserCategory::Administrator, 'Super Admin', 'super-admin', true, 'Unrestricted access to every module and action.');
        $adminRole = $this->role(UserCategory::Administrator, 'Operations Administrator', 'operations-administrator', false, 'Manages users, access, and portal operations.');
        $hiringManager = $this->role(UserCategory::Recruiter, 'Hiring Manager', 'hiring-manager', false, 'Full recruitment workflow permissions.');
        $recruiterMember = $this->role(UserCategory::Recruiter, 'Recruiter Member', 'recruiter-member', false, 'Day-to-day recruiter access.');
        $candidate = $this->role(UserCategory::Talent, 'Candidate', 'candidate', false, 'Standard career and application access.');
        $candidateViewer = $this->role(UserCategory::Talent, 'Candidate Viewer', 'candidate-viewer', false, 'Read-only career access.');

        $this->roleTemplate($adminRole, $administration, $adminMenus, true);
        $this->roleTemplate($hiringManager, $recruitment, $recruiterMenus, true);
        $this->roleTemplate($recruiterMember, $recruitment, $recruiterMenus, false);
        $this->roleTemplate($candidate, $career, $talentMenus, false);
        $this->roleTemplate($candidateViewer, $career, $talentMenus, false);

        $assign = app(RolePermissionService::class);
        $assign->assign($this->user('Portal Administrator', 'admin@example.com', $superAdmin), $superAdminRole, null, false);
        $assign->assign($this->user('Placement Officer', 'officer@example.com', $placementOfficer), $adminRole, null, false);
        $assign->assign($this->user('Demo Recruiter', 'recruiter@example.com', $corporateRecruiter), $hiringManager, null, false);
        $assign->assign($this->user('Agency Recruiter', 'agency@example.com', $staffingAgency), $recruiterMember, null, false);
        $assign->assign($this->user('Demo Talent', 'talent@example.com', $graduate), $candidate, null, false);
        $assign->assign($this->user('Experienced Candidate', 'candidate@example.com', $experienced), $candidateViewer, null, false);
    }

    private function type(UserCategory $category, string $name, string $slug, ?UserType $parent = null): UserType
    {
        return UserType::query()->updateOrCreate(
            ['slug' => $slug],
            ['category' => $category, 'name' => $name, 'parent_id' => $parent?->id, 'is_active' => true],
        );
    }

    private function module(string $name, string $slug, string $icon, int $position): PortalModule
    {
        return PortalModule::query()->updateOrCreate(
            ['slug' => $slug],
            compact('name', 'icon', 'position') + ['is_active' => true],
        );
    }

    private function menu(PortalModule $module, string $name, string $slug, ?string $route, string $icon, int $position, ?PortalMenu $parent = null): PortalMenu
    {
        return PortalMenu::query()->updateOrCreate(
            ['slug' => $slug],
            ['portal_module_id' => $module->id, 'parent_id' => $parent?->id, 'name' => $name, 'route_name' => $route, 'icon' => $icon, 'position' => $position, 'is_active' => true],
        );
    }

    /** @param array<int, PortalMenu> $menus */
    private function grant(UserType $type, PortalModule $module, array $menus, bool $manage): void
    {
        $type->modules()->syncWithoutDetaching([$module->id => ['enabled' => true]]);

        foreach ($menus as $menu) {
            $type->menus()->syncWithoutDetaching([
                $menu->id => [
                    'can_view' => true,
                    'can_create' => $manage,
                    'can_update' => $manage,
                    'can_delete' => $manage,
                ],
            ]);
        }
    }

    private function role(UserCategory $category, string $name, string $slug, bool $super = false, ?string $description = null): UserRole
    {
        return UserRole::query()->updateOrCreate(['slug' => $slug], ['category' => $category, 'name' => $name, 'description' => $description, 'is_super_admin' => $super, 'is_active' => true]);
    }

    /** @param array<int, PortalMenu> $menus */
    private function roleTemplate(UserRole $role, PortalModule $module, array $menus, bool $manage): void
    {
        $role->modules()->syncWithoutDetaching([$module->id => ['enabled' => true]]);
        foreach ($menus as $menu) $role->menus()->syncWithoutDetaching([$menu->id => ['can_view' => true, 'can_create' => $manage, 'can_update' => $manage, 'can_delete' => $manage]]);
    }

    private function user(string $name, string $email, UserType $type): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'user_type_id' => $type->id, 'password' => Hash::make('password'), 'email_verified_at' => now(), 'is_active' => true],
        );
    }
}
