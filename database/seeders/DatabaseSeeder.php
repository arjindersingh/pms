<?php

namespace Database\Seeders;

use App\Enums\UserCategory;
use App\Models\AcademicClass;
use App\Models\Country;
use App\Models\District;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\ProficiencyLevel;
use App\Models\QualificationLevel;
use App\Models\SharedMaster;
use App\Models\Skill;
use App\Models\State;
use App\Models\StudyMode;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserType;
use App\Models\WorkMode;
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
        $sharedData = $this->menu($administration, 'Shared Data', 'shared-data', null, 'bi-database', 30);
        $monetization = $this->menu($administration, 'Monetization', 'monetization', null, 'bi-cash-coin', 40);
        $adminMenus = [
            $adminDashboard, $accessControl, $userManagement, $permissionSetup, $sharedData, $monetization,
            $this->menu($administration, 'Account Review', 'account-review', 'admin.accounts.index', 'bi-person-check', 30, $accessControl),
            $this->menu($administration, 'Roles & Permissions', 'role-management', 'admin.roles.index', 'bi-person-gear', 40, $accessControl),
            $this->menu($administration, 'Permission Audit', 'permission-audit', 'admin.permission-audit', 'bi-clock-history', 50, $accessControl),
            $this->menu($administration, 'User Types', 'user-types', null, 'bi-diagram-3', 10, $userManagement),
            $this->menu($administration, 'Member Directory', 'member-directory', null, 'bi-person-lines-fill', 20, $userManagement),
            $this->menu($administration, 'Session Reports', 'session-reports', 'admin.sessions.index', 'bi-pc-display-horizontal', 30, $userManagement),
            $this->menu($administration, 'Module Access', 'module-access', 'admin.access', 'bi-grid', 10, $permissionSetup),
            $this->menu($administration, 'Menu Permissions', 'menu-permissions', 'admin.access', 'bi-list-check', 20, $permissionSetup),
            $this->menu($administration, 'Shared Masters', 'shared-masters', 'admin.shared-masters.index', 'bi-collection', 10, $sharedData),
            $this->menu($administration, 'Google Ads', 'google-ads', 'admin.ads.edit', 'bi-badge-ad', 10, $monetization),
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

        $administratorRoles = $this->seedRoles(UserCategory::Administrator, $administration, $adminMenus, [
            ['Super Administrator', 'super-admin', true, true, 'Unrestricted access to every module and action.'],
            ['Platform Administrator', 'platform-administrator', false, true, 'Manages platform configuration, users, access, and shared data.'],
            ['Operations Manager', 'operations-manager', false, true, 'Manages day-to-day placement portal operations.'],
            ['Job Moderator', 'job-moderator', false, false, 'Reviews job postings for quality and policy compliance.'],
            ['Recruiter Verification Officer', 'recruiter-verification-officer', false, false, 'Reviews recruiter accounts and organization verification.'],
            ['Candidate Verification Officer', 'candidate-verification-officer', false, false, 'Reviews candidate profiles and submitted documents.'],
            ['Marketing Manager', 'marketing-manager', false, false, 'Manages portal marketing and promotional activity.'],
            ['Finance and Commission Manager', 'finance-and-commission-manager', false, false, 'Oversees finance, billing, and commission operations.'],
            ['Support Executive', 'support-executive', false, false, 'Supports recruiter and candidate users.'],
            ['Auditor', 'auditor', false, false, 'Read-only oversight of portal activity and access records.'],
        ]);
        $recruiterRoles = $this->seedRoles(UserCategory::Recruiter, $recruitment, $recruiterMenus, [
            ['Organization Owner', 'organization-owner', false, true, 'Owns the organization account and its recruitment workspace.'],
            ['Recruiter Administrator', 'recruiter-administrator', false, true, 'Administers recruiters and hiring activity for the organization.'],
            ['Hiring Manager', 'hiring-manager', false, true, 'Manages jobs, applicants, and hiring decisions.'],
            ['Recruiter', 'recruiter', false, false, 'Runs day-to-day sourcing and recruitment activity.'],
            ['Interviewer', 'interviewer', false, false, 'Reviews assigned candidates and records interview feedback.'],
            ['Recruiter Finance User', 'recruiter-finance-user', false, false, 'Reviews organization billing and recruitment finance information.'],
            ['Organization Viewer', 'organization-viewer', false, false, 'Read-only access to the organization recruitment workspace.'],
        ]);
        $candidateRoles = $this->seedRoles(UserCategory::Talent, $career, $talentMenus, [
            ['Candidate', 'candidate', false, false, 'Standard candidate career and application access.'],
        ]);

        $assign = app(RolePermissionService::class);
        $assign->assign($this->user('Portal Administrator', 'admin@example.com', $superAdmin), $administratorRoles['super-admin'], null, false);
        $assign->assign($this->user('Placement Officer', 'officer@example.com', $placementOfficer), $administratorRoles['operations-manager'], null, false);
        $assign->assign($this->user('Demo Recruiter', 'recruiter@example.com', $corporateRecruiter), $recruiterRoles['hiring-manager'], null, false);
        $assign->assign($this->user('Agency Recruiter', 'agency@example.com', $staffingAgency), $recruiterRoles['recruiter'], null, false);
        $assign->assign($this->user('Demo Talent', 'talent@example.com', $graduate), $candidateRoles['candidate'], null, false);
        $assign->assign($this->user('Experienced Candidate', 'candidate@example.com', $experienced), $candidateRoles['candidate'], null, false);

        $this->seedSharedMasters();
    }

    private function seedSharedMasters(): void
    {
        $this->masterRecords(QualificationLevel::class, [
            ['SEC', 'Secondary'], ['SR_SEC', 'Senior Secondary'], ['CERT', 'Certificate'], ['ITI', 'ITI / Vocational'],
            ['DIP', 'Diploma'], ['ADV_DIP', 'Advanced Diploma'], ['UG', 'Graduation / Bachelor’s'], ['PG_DIP', 'Postgraduate Diploma'],
            ['PG', 'Postgraduation / Master’s'], ['MPHIL', 'M.Phil.'], ['DOC', 'Doctorate / Ph.D.'], ['POST_DOC', 'Postdoctoral Research'], ['OTHER', 'Other Qualification'],
        ]);
        $this->masterRecords(Gender::class, [['MALE', 'Male'], ['FEMALE', 'Female'], ['NON_BINARY', 'Non-binary'], ['UNDISCLOSED', 'Prefer not to disclose']]);
        $this->masterRecords(MaritalStatus::class, [['SINGLE', 'Single'], ['MARRIED', 'Married'], ['DIVORCED', 'Divorced'], ['WIDOWED', 'Widowed']]);
        $this->masterRecords(AcademicClass::class, [['CLASS_8', 'Class 8'], ['CLASS_10', 'Class 10'], ['CLASS_12', 'Class 12']]);
        $this->masterRecords(Country::class, [['IN', 'India'], ['US', 'United States'], ['GB', 'United Kingdom'], ['CA', 'Canada'], ['AU', 'Australia'], ['NZ', 'New Zealand'], ['AE', 'United Arab Emirates'], ['SG', 'Singapore'], ['DE', 'Germany'], ['FR', 'France'], ['IE', 'Ireland'], ['JP', 'Japan'], ['CN', 'China'], ['BD', 'Bangladesh'], ['BT', 'Bhutan'], ['NP', 'Nepal'], ['LK', 'Sri Lanka'], ['PK', 'Pakistan'], ['ZA', 'South Africa'], ['SA', 'Saudi Arabia'], ['QA', 'Qatar'], ['OM', 'Oman'], ['KW', 'Kuwait'], ['MY', 'Malaysia'], ['ID', 'Indonesia']]);
        $this->masterRecords(Language::class, [['EN', 'English'], ['HI', 'Hindi'], ['PA', 'Punjabi'], ['BN', 'Bengali'], ['MR', 'Marathi'], ['TE', 'Telugu'], ['TA', 'Tamil'], ['GU', 'Gujarati'], ['UR', 'Urdu'], ['KN', 'Kannada'], ['ML', 'Malayalam'], ['OR', 'Odia'], ['AS', 'Assamese']]);
        $this->masterRecords(StudyMode::class, [['REGULAR', 'Regular'], ['DISTANCE', 'Distance'], ['ONLINE', 'Online'], ['PART_TIME', 'Part-time']]);
        $this->masterRecords(EmploymentType::class, [['FULL_TIME', 'Full-time'], ['PART_TIME', 'Part-time'], ['PERMANENT', 'Permanent'], ['CONTRACT', 'Contract'], ['INTERNSHIP', 'Internship'], ['APPRENTICE', 'Apprenticeship'], ['TEMPORARY', 'Temporary'], ['FREELANCE', 'Freelance'], ['CONSULTANCY', 'Consultancy']]);
        $this->masterRecords(WorkMode::class, [['ONSITE', 'On-site'], ['REMOTE', 'Remote'], ['HYBRID', 'Hybrid'], ['FIELD', 'Field-based']]);
        $this->masterRecords(ProficiencyLevel::class, [['BASIC', 'Basic'], ['CONVERSATIONAL', 'Conversational'], ['WORKING', 'Working proficiency'], ['PROFESSIONAL', 'Professional proficiency'], ['FLUENT', 'Fluent'], ['NATIVE', 'Native / Bilingual']]);
        $this->masterRecords(Skill::class, [['COMMUNICATION', 'Communication'], ['LEADERSHIP', 'Leadership'], ['TEAMWORK', 'Teamwork'], ['MS_OFFICE', 'Microsoft Office'], ['EXCEL', 'Microsoft Excel'], ['PHP', 'PHP'], ['LARAVEL', 'Laravel'], ['JAVASCRIPT', 'JavaScript'], ['PYTHON', 'Python'], ['SQL', 'SQL'], ['MARKETING', 'Marketing'], ['SALES', 'Sales'], ['ACCOUNTING', 'Accounting'], ['TEACHING', 'Teaching']]);
        $this->seedIndianGeography();
    }

    private function seedIndianGeography(): void
    {
        $india = Country::where('code', 'IN')->firstOrFail();
        $states = [['AN', 'Andaman and Nicobar Islands'], ['AP', 'Andhra Pradesh'], ['AR', 'Arunachal Pradesh'], ['AS', 'Assam'], ['BR', 'Bihar'], ['CH', 'Chandigarh'], ['CG', 'Chhattisgarh'], ['DH', 'Dadra and Nagar Haveli and Daman and Diu'], ['DL', 'Delhi'], ['GA', 'Goa'], ['GJ', 'Gujarat'], ['HR', 'Haryana'], ['HP', 'Himachal Pradesh'], ['JK', 'Jammu and Kashmir'], ['JH', 'Jharkhand'], ['KA', 'Karnataka'], ['KL', 'Kerala'], ['LA', 'Ladakh'], ['LD', 'Lakshadweep'], ['MP', 'Madhya Pradesh'], ['MH', 'Maharashtra'], ['MN', 'Manipur'], ['ML', 'Meghalaya'], ['MZ', 'Mizoram'], ['NL', 'Nagaland'], ['OD', 'Odisha'], ['PY', 'Puducherry'], ['PB', 'Punjab'], ['RJ', 'Rajasthan'], ['SK', 'Sikkim'], ['TN', 'Tamil Nadu'], ['TS', 'Telangana'], ['TR', 'Tripura'], ['UP', 'Uttar Pradesh'], ['UK', 'Uttarakhand'], ['WB', 'West Bengal']];
        foreach ($states as $i => [$code,$name]) {
            State::updateOrCreate(['country_id' => $india->id, 'code' => $code], ['display_name' => $name, 'short_name' => $name, 'sort_order' => ($i + 1) * 10, 'is_active' => true]);
        }
        $punjab = State::where('country_id', $india->id)->where('code', 'PB')->firstOrFail();
        foreach (['Amritsar', 'Barnala', 'Bathinda', 'Faridkot', 'Fatehgarh Sahib', 'Fazilka', 'Ferozepur', 'Gurdaspur', 'Hoshiarpur', 'Jalandhar', 'Kapurthala', 'Ludhiana', 'Malerkotla', 'Mansa', 'Moga', 'Pathankot', 'Patiala', 'Rupnagar', 'Sahibzada Ajit Singh Nagar', 'Sangrur', 'Shaheed Bhagat Singh Nagar', 'Sri Muktsar Sahib', 'Tarn Taran'] as $i => $name) {
            District::updateOrCreate(['state_id' => $punjab->id, 'code' => strtoupper(str_replace(' ', '_', $name))], ['display_name' => $name, 'short_name' => $name, 'sort_order' => ($i + 1) * 10, 'is_active' => true]);
        }
    }

    /** @param class-string<SharedMaster> $model */
    private function masterRecords(string $model, array $records): void
    {
        foreach ($records as $position => [$code, $name]) {
            $model::withTrashed()->updateOrCreate(['code' => $code], ['short_name' => $name, 'display_name' => $name, 'sort_order' => ($position + 1) * 10, 'is_active' => true, 'deleted_at' => null]);
        }
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

    /**
     * @param  array<int, PortalMenu>  $menus
     * @param  array<int, array{string, string, bool, bool, string}>  $definitions
     * @return array<string, UserRole>
     */
    private function seedRoles(UserCategory $category, PortalModule $module, array $menus, array $definitions): array
    {
        $roles = [];

        foreach ($definitions as [$name, $slug, $super, $manage, $description]) {
            $role = $this->role($category, $name, $slug, $super, $description);
            $this->roleTemplate($role, $module, $menus, $manage);
            $roles[$slug] = $role;
        }

        return $roles;
    }

    /** @param array<int, PortalMenu> $menus */
    private function roleTemplate(UserRole $role, PortalModule $module, array $menus, bool $manage): void
    {
        $role->modules()->syncWithoutDetaching([$module->id => ['enabled' => true]]);
        foreach ($menus as $menu) {
            $role->menus()->syncWithoutDetaching([$menu->id => ['can_view' => true, 'can_create' => $manage, 'can_update' => $manage, 'can_delete' => $manage]]);
        }
    }

    private function user(string $name, string $email, UserType $type): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'user_type_id' => $type->id, 'password' => Hash::make('password'), 'email_verified_at' => now(), 'is_active' => true],
        );
    }
}
