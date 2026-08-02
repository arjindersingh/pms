<?php

namespace Database\Seeders;

use App\Enums\UserCategory;
use App\Models\AcademicClass;
use App\Models\ConsentType;
use App\Models\Country;
use App\Models\DeclarationType;
use App\Models\Degree;
use App\Models\District;
use App\Models\EducationalInstitution;
use App\Models\EducationAuthority;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\HobbyCategory;
use App\Models\InterestLevel;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\ProficiencyLevel;
use App\Models\ProjectType;
use App\Models\QualificationLevel;
use App\Models\RecognitionLevel;
use App\Models\ReferenceType;
use App\Models\SharedMaster;
use App\Models\Skill;
use App\Models\SkillGroup;
use App\Models\SocialPlatform;
use App\Models\State;
use App\Models\StudyMode;
use App\Models\Subject;
use App\Models\SubscriptionPlan;
use App\Models\Talent;
use App\Models\TalentCategory;
use App\Models\User;
use App\Models\UserRole;
use App\Models\UserType;
use App\Models\WorkMode;
use App\Services\RolePermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $companyProfile = $this->menu($administration, 'Company Profile', 'company-profile', 'admin.company.edit', 'bi-buildings', 15);
        $accessControl = $this->menu($administration, 'Access Control', 'access-control', null, 'bi-shield-lock', 20);
        $userManagement = $this->menu($administration, 'User Management', 'user-management', null, 'bi-people', 10, $accessControl);
        $permissionSetup = $this->menu($administration, 'Permission Setup', 'permission-setup', null, 'bi-sliders', 20, $accessControl);
        $sharedData = $this->menu($administration, 'Shared Data', 'shared-data', null, 'bi-database', 30);
        $monetization = $this->menu($administration, 'Monetization', 'monetization', null, 'bi-cash-coin', 40);
        $adminMenus = [
            $adminDashboard, $companyProfile, $accessControl, $userManagement, $permissionSetup, $sharedData, $monetization,
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
            $this->menu($administration, 'Subscription Plans', 'subscription-plans', 'admin.subscription-plans.index', 'bi-credit-card', 20, $monetization),
            $this->menu($administration, 'Payment Settings', 'payment-settings', 'admin.payments.edit', 'bi-wallet2', 30, $monetization),
        ];

        $recruiterDashboard = $this->menu($recruitment, 'Dashboard', 'recruiter-dashboard', 'recruiter.dashboard', 'bi-speedometer2', 10);
        $hiringWorkspace = $this->menu($recruitment, 'Hiring Workspace', 'hiring-workspace', null, 'bi-briefcase', 20);
        $jobs = $this->menu($recruitment, 'Jobs', 'jobs', null, 'bi-megaphone', 10, $hiringWorkspace);
        $candidates = $this->menu($recruitment, 'Candidates', 'candidates', null, 'bi-people', 20, $hiringWorkspace);
        $recruiterMenus = [
            $recruiterDashboard, $hiringWorkspace, $jobs, $candidates,
            $this->menu($recruitment, 'Candidate Search', 'candidate-search', 'recruiter.candidate-search.edit', 'bi-person-bounding-box', 15, $hiringWorkspace),
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
            $this->menu($career, 'Job Preferences', 'job-preferences', 'talent.job-preferences.edit', 'bi-sliders2', 15, $careerWorkspace),
            $this->menu($career, 'Candidate Profile', 'candidate-profile', 'talent.profile.edit', 'bi-person-vcard', 10, $careerWorkspace),
            $this->menu($career, 'Recommended Jobs', 'find-jobs', null, 'bi-stars', 10, $opportunities),
            $this->menu($career, 'Saved Jobs', 'saved-jobs', null, 'bi-bookmark-heart', 20, $opportunities),
            $this->menu($career, 'Active Applications', 'my-applications', null, 'bi-hourglass-split', 10, $applications),
            $this->menu($career, 'Application History', 'application-history', null, 'bi-clock-history', 20, $applications),
        ];

        $this->seedSubscriptionPlanMenus(UserCategory::Recruiter, $recruiterMenus);
        $this->seedSubscriptionPlanMenus(UserCategory::Talent, $talentMenus);

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

        $this->backfillMissingRolePermissions();

        $this->seedSharedMasters();
    }

    private function seedSharedMasters(): void
    {
        $this->masterRecords(QualificationLevel::class, [
            ['SEC', 'Secondary'], ['SR_SEC', 'Senior Secondary'], ['CERT', 'Certificate'], ['ITI', 'ITI / Vocational'],
            ['DIP', 'Diploma'], ['ADV_DIP', 'Advanced Diploma'], ['UG', 'Graduation / Bachelor’s'], ['PG_DIP', 'Postgraduate Diploma'],
            ['PG', 'Postgraduation / Master’s'], ['MPHIL', 'M.Phil.'], ['DOC', 'Doctorate / Ph.D.'], ['POST_DOC', 'Postdoctoral Research'], ['OTHER', 'Other Qualification'],
        ]);
        $this->seedDegrees();
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
        $this->masterRecords(SkillGroup::class, [
            ['PROGRAMMING', 'Programming'], ['WEB_DEVELOPMENT', 'Web Development'], ['MOBILE_DEVELOPMENT', 'Mobile Development'],
            ['DATABASE', 'Database'], ['NETWORKING', 'Networking'], ['CYBERSECURITY', 'Cybersecurity'],
            ['ARTIFICIAL_INTELLIGENCE', 'Artificial Intelligence'], ['DATA_SCIENCE', 'Data Science'], ['CLOUD_COMPUTING', 'Cloud Computing'],
            ['OFFICE_APPLICATIONS', 'Office Applications'], ['GRAPHIC_DESIGN', 'Graphic Design'], ['MULTIMEDIA', 'Multimedia'],
            ['TEACHING', 'Teaching'], ['RESEARCH', 'Research'], ['FINANCE', 'Finance'], ['ACCOUNTING', 'Accounting'],
            ['MANAGEMENT', 'Management'], ['HUMAN_RESOURCES', 'Human Resources'], ['MARKETING', 'Marketing'], ['SALES', 'Sales'],
            ['COMMUNICATION', 'Communication'], ['LEADERSHIP', 'Leadership'], ['ANALYTICAL_SKILLS', 'Analytical Skills'],
            ['TECHNICAL_SKILLS', 'Technical Skills'], ['SOFT_SKILLS', 'Soft Skills'], ['TRADE_SKILLS', 'Trade Skills'],
        ]);
        $this->masterRecords(ProjectType::class, [
            ['ACADEMIC', 'Academic'], ['PROFESSIONAL', 'Professional'], ['PERSONAL', 'Personal'],
            ['RESEARCH', 'Research'], ['CLIENT', 'Client'], ['OPEN_SOURCE', 'Open source'],
            ['STARTUP', 'Startup'], ['SOCIAL_IMPACT', 'Social impact'], ['FINAL_YEAR_PROJECT', 'Final-year project'],
        ]);
        $this->masterRecords(RecognitionLevel::class, [
            ['INSTITUTION', 'Institution'], ['DISTRICT', 'District'], ['STATE', 'State'], ['REGIONAL', 'Regional'],
            ['NATIONAL', 'National'], ['INTERNATIONAL', 'International'], ['CORPORATE', 'Corporate'], ['PROFESSIONAL', 'Professional'],
        ]);
        $this->masterRecords(TalentCategory::class, [
            ['PERFORMING_ARTS', 'Performing Arts'], ['COMMUNICATION', 'Communication'], ['VISUAL_CREATIVE_ARTS', 'Visual & Creative Arts'],
            ['TECHNOLOGY', 'Technology'], ['SPORTS_FITNESS', 'Sports & Fitness'], ['MANAGEMENT', 'Management'],
            ['CULINARY', 'Culinary Arts'], ['CRAFTS', 'Crafts'], ['OTHER', 'Other'],
        ]);
        $this->masterRecords(Talent::class, [
            ['MUSIC', 'Music'], ['DANCE', 'Dance'], ['THEATRE', 'Theatre'], ['PUBLIC_SPEAKING', 'Public speaking'],
            ['DEBATE', 'Debate'], ['PHOTOGRAPHY', 'Photography'], ['VIDEOGRAPHY', 'Videography'], ['WRITING', 'Writing'],
            ['PAINTING', 'Painting'], ['ANCHORING', 'Anchoring'], ['CODING', 'Coding'], ['SPORTS', 'Sports'],
            ['EVENT_MANAGEMENT', 'Event management'], ['COOKING', 'Cooking'], ['DESIGN', 'Design'], ['CRAFT', 'Craft'],
        ]);
        $this->masterRecords(HobbyCategory::class, [
            ['LEARNING', 'Learning & Knowledge'], ['TRAVEL_OUTDOORS', 'Travel & Outdoors'], ['SPORTS_FITNESS', 'Sports & Fitness'],
            ['CREATIVE', 'Creative Arts'], ['ENTERTAINMENT', 'Entertainment'], ['TECHNOLOGY', 'Technology'],
            ['SOCIAL_COMMUNITY', 'Social & Community'], ['LIFESTYLE', 'Lifestyle'], ['OTHER', 'Other'],
        ]);
        $this->masterRecords(InterestLevel::class, [
            ['CASUAL', 'Casual'], ['INTERESTED', 'Interested'], ['ENTHUSIAST', 'Enthusiast'], ['PASSIONATE', 'Passionate'],
        ]);
        $this->masterRecords(Hobby::class, [
            ['READING', 'Reading'], ['TRAVELLING', 'Travelling'], ['SPORTS', 'Sports'], ['MUSIC', 'Music'],
            ['MOVIES', 'Movies'], ['GARDENING', 'Gardening'], ['PHOTOGRAPHY', 'Photography'], ['COOKING', 'Cooking'],
            ['BLOGGING', 'Blogging'], ['GAMING', 'Gaming'], ['VOLUNTEERING', 'Volunteering'], ['FITNESS', 'Fitness'],
            ['ART', 'Art'], ['WRITING', 'Writing'],
        ]);
        $this->masterRecords(ReferenceType::class, [
            ['ACADEMIC', 'Academic'], ['PROFESSIONAL', 'Professional'], ['PREVIOUS_EMPLOYER', 'Previous employer'],
            ['SUPERVISOR', 'Supervisor'], ['MENTOR', 'Mentor'], ['PERSONAL', 'Personal'], ['CHARACTER_REFERENCE', 'Character reference'],
        ]);
        $this->masterRecords(SocialPlatform::class, [
            ['LINKEDIN', 'LinkedIn'], ['GITHUB', 'GitHub'], ['GITLAB', 'GitLab'], ['BEHANCE', 'Behance'],
            ['DRIBBBLE', 'Dribbble'], ['RESEARCHGATE', 'ResearchGate'], ['GOOGLE_SCHOLAR', 'Google Scholar'],
            ['ORCID', 'ORCID'], ['STACK_OVERFLOW', 'Stack Overflow'], ['YOUTUBE', 'YouTube'],
            ['PERSONAL_WEBSITE', 'Personal website'], ['PORTFOLIO_WEBSITE', 'Portfolio website'], ['OTHER', 'Other'],
        ]);
        $this->masterRecords(DeclarationType::class, [
            ['INFORMATION_CORRECT', 'Information is correct'], ['DOCUMENTS_AUTHENTIC', 'Documents are authentic'],
        ]);
        $this->masterRecords(ConsentType::class, [
            ['PRIVACY_POLICY', 'Privacy policy accepted'], ['TERMS_CONDITIONS', 'Terms and conditions accepted'],
            ['RECRUITER_CONTACT', 'Recruiter contact allowed'], ['BACKGROUND_VERIFICATION', 'Background verification allowed'],
            ['COMMISSION_POLICY', 'Commission policy accepted'], ['PLACEMENT_POLICY', 'Placement policy accepted'],
        ]);
        $this->masterRecords(Subject::class, [['ENGLISH', 'English'], ['HINDI', 'Hindi'], ['PUNJABI', 'Punjabi'], ['MATHEMATICS', 'Mathematics'], ['PHYSICS', 'Physics'], ['CHEMISTRY', 'Chemistry'], ['BIOLOGY', 'Biology'], ['COMPUTER_SCIENCE', 'Computer Science'], ['INFORMATICS', 'Informatics Practices'], ['ECONOMICS', 'Economics'], ['ACCOUNTANCY', 'Accountancy'], ['BUSINESS_STUDIES', 'Business Studies'], ['HISTORY', 'History'], ['GEOGRAPHY', 'Geography'], ['POLITICAL_SCIENCE', 'Political Science'], ['SOCIOLOGY', 'Sociology'], ['PSYCHOLOGY', 'Psychology'], ['ENVIRONMENTAL_SCIENCE', 'Environmental Science'], ['ENGINEERING', 'Engineering'], ['MANAGEMENT', 'Management']]);
        $this->masterRecords(EducationalInstitution::class, [['OTHER_INSTITUTION', 'Other / Institution not listed'], ['GOVT_SCHOOL', 'Government School'], ['KENDRIYA_VIDYALAYA', 'Kendriya Vidyalaya'], ['JAWAHAR_NAVODAYA', 'Jawahar Navodaya Vidyalaya'], ['GOVT_COLLEGE', 'Government College'], ['DAV_COLLEGE', 'DAV College'], ['KHALSA_COLLEGE', 'Khalsa College'], ['GNDU_CAMPUS', 'Guru Nanak Dev University Campus'], ['PUNJABI_UNIVERSITY_CAMPUS', 'Punjabi University Campus'], ['PANJAB_UNIVERSITY_CAMPUS', 'Panjab University Campus'], ['IIT_ROPAR', 'Indian Institute of Technology Ropar'], ['NIT_JALANDHAR', 'Dr. B. R. Ambedkar National Institute of Technology Jalandhar'], ['THAPAR', 'Thapar Institute of Engineering and Technology'], ['LPU', 'Lovely Professional University'], ['CHANDIGARH_UNIVERSITY', 'Chandigarh University']]);
        $this->seedEducationAuthorities();
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

    private function seedDegrees(): void
    {
        $records = [
            'SEC' => [['MATRIC', 'Matric / Class 10']],
            'SR_SEC' => [['CLASS_12', 'Senior Secondary / Class 12']],
            'CERT' => [['CERT_GENERAL', 'Certificate Course'], ['CERT_COMPUTER', 'Computer Certificate'], ['CERT_LANGUAGE', 'Language Certificate']],
            'ITI' => [['ITI_ELECTRICIAN', 'ITI Electrician'], ['ITI_FITTER', 'ITI Fitter'], ['ITI_COPA', 'ITI COPA'], ['ITI_WELDER', 'ITI Welder'], ['ITI_MECHANIC', 'ITI Mechanic'], ['ITI_PLUMBER', 'ITI Plumber'], ['ITI_DRAUGHTSMAN', 'ITI Draughtsman']],
            'DIP' => [['DIP_ENGINEERING', 'Diploma in Engineering'], ['DIP_COMPUTER', 'Diploma in Computer Applications'], ['DIP_EDUCATION', 'Diploma in Education'], ['DIP_NURSING', 'Diploma in Nursing'], ['DIP_PHARMACY', 'Diploma in Pharmacy'], ['DIP_MANAGEMENT', 'Diploma in Management']],
            'ADV_DIP' => [['ADV_DIP_ENGINEERING', 'Advanced Diploma in Engineering'], ['ADV_DIP_COMPUTER', 'Advanced Diploma in Computer Applications'], ['ADV_DIP_MANAGEMENT', 'Advanced Diploma in Management']],
            'UG' => [['BA', 'Bachelor of Arts (B.A.)'], ['BSC', 'Bachelor of Science (B.Sc.)'], ['BCOM', 'Bachelor of Commerce (B.Com.)'], ['BBA', 'Bachelor of Business Administration (BBA)'], ['BCA', 'Bachelor of Computer Applications (BCA)'], ['BTECH', 'Bachelor of Technology (B.Tech.)'], ['BE', 'Bachelor of Engineering (B.E.)'], ['BED', 'Bachelor of Education (B.Ed.)'], ['LLB', 'Bachelor of Laws (LL.B.)'], ['BPHARM', 'Bachelor of Pharmacy (B.Pharm.)'], ['BARCH', 'Bachelor of Architecture (B.Arch.)'], ['BDS', 'Bachelor of Dental Surgery (BDS)'], ['MBBS', 'Bachelor of Medicine and Surgery (MBBS)'], ['BSC_NURSING', 'B.Sc. Nursing'], ['BSW', 'Bachelor of Social Work (BSW)'], ['BFA', 'Bachelor of Fine Arts (BFA)'], ['BVOC', 'Bachelor of Vocation (B.Voc.)']],
            'PG_DIP' => [['PGDCA', 'Postgraduate Diploma in Computer Applications (PGDCA)'], ['PGDM', 'Postgraduate Diploma in Management (PGDM)'], ['PGD_GENERAL', 'Postgraduate Diploma']],
            'PG' => [['MA', 'Master of Arts (M.A.)'], ['MSC', 'Master of Science (M.Sc.)'], ['MCOM', 'Master of Commerce (M.Com.)'], ['MBA', 'Master of Business Administration (MBA)'], ['MCA', 'Master of Computer Applications (MCA)'], ['MTECH', 'Master of Technology (M.Tech.)'], ['ME', 'Master of Engineering (M.E.)'], ['MED', 'Master of Education (M.Ed.)'], ['LLM', 'Master of Laws (LL.M.)'], ['MPHARM', 'Master of Pharmacy (M.Pharm.)'], ['MSW', 'Master of Social Work (MSW)'], ['MARCH', 'Master of Architecture (M.Arch.)'], ['MPH', 'Master of Public Health (MPH)'], ['MFA', 'Master of Fine Arts (MFA)']],
            'MPHIL' => [['MPHIL_GENERAL', 'Master of Philosophy (M.Phil.)']],
            'DOC' => [['PHD', 'Doctor of Philosophy (Ph.D.)'], ['EDD', 'Doctor of Education (Ed.D.)'], ['DBA', 'Doctor of Business Administration (DBA)']],
            'POST_DOC' => [['POSTDOC_FELLOWSHIP', 'Postdoctoral Fellowship / Research']],
            'OTHER' => [['OTHER_COURSE', 'Other Qualification / Course']],
        ];
        foreach ($records as $levelCode => $degrees) {
            $level = QualificationLevel::where('code', $levelCode)->firstOrFail();
            foreach ($degrees as $position => [$code, $name]) {
                Degree::withTrashed()->updateOrCreate(['code' => $code], ['qualification_level_id' => $level->id, 'short_name' => $name, 'display_name' => $name, 'sort_order' => ($position + 1) * 10, 'is_active' => true, 'deleted_at' => null]);
            }
        }
    }

    private function seedEducationAuthorities(): void
    {
        $groups = [
            'board' => [['CBSE', 'Central Board of Secondary Education (CBSE)'], ['CISCE', 'Council for the Indian School Certificate Examinations (CISCE)'], ['NIOS', 'National Institute of Open Schooling (NIOS)'], ['PSEB', 'Punjab School Education Board (PSEB)'], ['HBSE', 'Board of School Education Haryana'], ['HPBOSE', 'Himachal Pradesh Board of School Education'], ['UPMSP', 'Uttar Pradesh Madhyamik Shiksha Parishad'], ['RBSE', 'Board of Secondary Education Rajasthan']],
            'university' => [['GNDU', 'Guru Nanak Dev University'], ['PUNJABI_UNIVERSITY', 'Punjabi University'], ['PANJAB_UNIVERSITY', 'Panjab University'], ['PTU', 'I.K. Gujral Punjab Technical University'], ['BFUHS', 'Baba Farid University of Health Sciences'], ['PSBTE', 'Punjab State Board of Technical Education and Industrial Training'], ['IGNOU', 'Indira Gandhi National Open University'], ['DU', 'University of Delhi'], ['JNU', 'Jawaharlal Nehru University'], ['UGC_RECOGNIZED', 'Other UGC Recognized University'], ['OTHER_AUTHORITY', 'Other Board / University']],
        ];
        $position = 0;
        foreach ($groups as $type => $records) {
            foreach ($records as [$code, $name]) {
                EducationAuthority::withTrashed()->updateOrCreate(['code' => $code], ['authority_type' => $type, 'short_name' => $code, 'display_name' => $name, 'sort_order' => (++$position) * 10, 'is_active' => true, 'deleted_at' => null]);
            }
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

    /** @param array<int, PortalMenu> $menus */
    private function seedSubscriptionPlanMenus(UserCategory $category, array $menus): void
    {
        foreach (SubscriptionPlan::where('category', $category)->get() as $plan) {
            $limit = $plan->slug === 'free' ? 2 : ($plan->slug === 'intermediate' ? max(2, (int) ceil(count($menus) * .65)) : count($menus));
            $plan->menus()->sync(collect($menus)->mapWithKeys(function (PortalMenu $menu, int $index) use ($plan, $limit) {
                $enabled = $index < $limit || ($plan->slug === 'free' && $menu->route_name !== null);
                $essential = in_array($menu->slug, ['job-preferences', 'candidate-search'], true);

                return [$menu->id => ['can_view' => $enabled, 'can_create' => $enabled && ($plan->slug !== 'free' || $essential), 'can_update' => $enabled && ($plan->slug !== 'free' || $essential), 'can_delete' => $enabled && $plan->slug === 'full']];
            })->all());
        }
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

    /** Add newly introduced role permissions without replacing user customizations. */
    private function backfillMissingRolePermissions(): void
    {
        User::query()->whereNotNull('user_role_id')->whereNull('permissions_customized_at')->each(function (User $user): void {
            foreach (DB::table('portal_module_user_role')->where('user_role_id', $user->user_role_id)->get() as $permission) {
                DB::table('portal_module_user')->insertOrIgnore([
                    'user_id' => $user->id, 'portal_module_id' => $permission->portal_module_id,
                    'enabled' => $permission->enabled, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            foreach (DB::table('portal_menu_user_role')->where('user_role_id', $user->user_role_id)->get() as $permission) {
                DB::table('portal_menu_user')->insertOrIgnore([
                    'user_id' => $user->id, 'portal_menu_id' => $permission->portal_menu_id,
                    'can_view' => $permission->can_view, 'can_create' => $permission->can_create,
                    'can_update' => $permission->can_update, 'can_delete' => $permission->can_delete,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });
    }

    private function user(string $name, string $email, UserType $type): User
    {
        return User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'user_type_id' => $type->id, 'password' => Hash::make('password'), 'email_verified_at' => now(), 'is_active' => true],
        );
    }
}
