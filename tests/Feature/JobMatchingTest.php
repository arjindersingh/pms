<?php

namespace Tests\Feature;

use App\Models\JobSearchProfile;
use App\Models\JobTitle;
use App\Models\OrganizationCategory;
use App\Models\OrganizationPost;
use App\Models\Skill;
use App\Models\User;
use App\Services\JobMatchService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobMatchingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_job_taxonomy_is_managed_through_shared_masters(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.shared-masters.index', ['type' => 'job-titles']))->assertOk()->assertSee('Job Titles')->assertSee('Bank Clerk')->assertSee('Banking');
    }

    public function test_tuition_jobs_are_available_to_applicants_and_recruiters(): void
    {
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $this->actingAs($talent)->get(route('talent.job-preferences.edit'))->assertOk()->assertSee('Tutoring &amp; Tuition Services', false)->assertSee('Home Tuition')->assertSee('Home Tutor')->assertSee('Online Tuition')->assertSee('Online Tutor')->assertSee('Tuition Centers')->assertSee('Organisation types')->assertSee('Organisation posts')->assertSee('Search and select job titles');
    }

    public function test_talent_and_recruiter_use_same_criteria_and_receive_match_score(): void
    {
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $title = JobTitle::where('code', 'BANK_CLERK')->firstOrFail();
        $skill = Skill::firstOrFail();
        $organizationCategory = OrganizationCategory::firstOrFail();
        $organizationPost = OrganizationPost::where('organization_category_id', $organizationCategory->id)->firstOrFail();
        foreach ([[$talent, 'talent'], [$recruiter, 'recruiter']] as [$user,$type]) {
            $profile = JobSearchProfile::create(['user_id' => $user->id, 'profile_type' => $type, 'headline' => $type.' banking', 'min_experience_years' => 1, 'max_experience_years' => 5, 'min_annual_salary' => 300000, 'max_annual_salary' => 600000, 'currency' => 'INR']);
            $profile->jobTitles()->sync([$title->id]);
            $profile->skills()->sync([$skill->id]);
            $profile->organizationCategories()->sync([$organizationCategory->id]);
            $profile->organizationPosts()->sync([$organizationPost->id]);
        }$matches = app(JobMatchService::class)->matches($talent->jobSearchProfile);
        $this->assertCount(1, $matches);
        $this->assertGreaterThanOrEqual(50, $matches->first()['score']);
        $this->assertContains('Organisation type', $matches->first()['reasons']);
        $this->assertContains('Organisation post', $matches->first()['reasons']);
    }

    public function test_free_users_can_save_matching_preferences(): void
    {
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $title = JobTitle::where('code', 'TEACHER')->firstOrFail();
        $organizationCategory = OrganizationCategory::firstOrFail();
        $organizationPost = OrganizationPost::where('organization_category_id', $organizationCategory->id)->firstOrFail();
        $this->actingAs($talent)->put(route('talent.job-preferences.update'), ['headline' => 'Teaching role', 'job_titles' => [$title->id], 'organization_categories' => [$organizationCategory->id], 'organization_posts' => [$organizationPost->id], 'min_experience_years' => 0, 'currency' => 'INR', 'is_active' => 1])->assertRedirect()->assertSessionHasNoErrors();
        $profile = JobSearchProfile::where('user_id', $talent->id)->firstOrFail();
        $this->assertSame([$organizationCategory->id], $profile->organizationCategories()->pluck('organization_categories.id')->all());
        $this->assertSame([$organizationPost->id], $profile->organizationPosts()->pluck('organization_posts.id')->all());
    }
}
