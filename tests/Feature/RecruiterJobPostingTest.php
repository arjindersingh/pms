<?php

namespace Tests\Feature;

use App\Models\JobPosting;
use App\Models\OrganizationPost;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruiterJobPostingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_recruiter_can_manage_flexible_category_specific_job_postings(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $profile = $recruiter->recruiterProfile()->create(['phone' => '1', 'country' => 'India']);
        $school = $profile->organizations()->create([
            'name' => 'City School', 'organization_type' => 'school', 'placement_contact_name' => 'Head',
            'placement_email' => 'head@example.com', 'placement_phone' => '1', 'address_line_1' => 'Road',
            'city' => 'City', 'state' => 'State', 'postal_code' => '1', 'country' => 'India',
        ]);
        $teacher = OrganizationPost::where('code', 'teacher')->firstOrFail();

        $this->actingAs($recruiter)->get(route('recruiter.job-postings.create'))
            ->assertOk()->assertSee('Talent Acquisition Hub')->assertSee('Teacher')->assertSee('Custom post title');

        $this->post(route('recruiter.job-postings.store'), [
            'recruiter_organization_id' => $school->id, 'organization_post_id' => $teacher->id,
            'vacancies' => 2, 'currency' => 'INR', 'description' => 'Teach senior classes.', 'status' => 'published',
        ])->assertRedirect(route('recruiter.job-postings.index'))->assertSessionHasNoErrors();

        $posting = JobPosting::firstOrFail();
        $this->assertSame('Teacher', $posting->title);
        $this->assertNotNull($posting->published_at);

        $this->put(route('recruiter.job-postings.update', $posting), [
            'recruiter_organization_id' => $school->id, 'custom_title' => 'Robotics Mentor',
            'vacancies' => 1, 'currency' => 'INR', 'description' => 'Lead the robotics lab.', 'status' => 'draft',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('job_postings', ['id' => $posting->id, 'title' => 'Robotics Mentor', 'status' => 'draft']);

        $this->delete(route('recruiter.job-postings.destroy', $posting))->assertRedirect();
        $this->assertDatabaseMissing('job_postings', ['id' => $posting->id]);
    }

    public function test_recruiter_cannot_use_a_post_from_another_organisation_category(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $profile = $recruiter->recruiterProfile()->create(['phone' => '1', 'country' => 'India']);
        $school = $profile->organizations()->create([
            'name' => 'City School', 'organization_type' => 'school', 'placement_contact_name' => 'Head',
            'placement_email' => 'head@example.com', 'placement_phone' => '1', 'address_line_1' => 'Road',
            'city' => 'City', 'state' => 'State', 'postal_code' => '1', 'country' => 'India',
        ]);
        $doctor = OrganizationPost::where('code', 'doctor')->firstOrFail();

        $this->actingAs($recruiter)->post(route('recruiter.job-postings.store'), [
            'recruiter_organization_id' => $school->id, 'organization_post_id' => $doctor->id,
            'vacancies' => 1, 'currency' => 'INR', 'description' => 'Invalid category post.', 'status' => 'draft',
        ])->assertStatus(422);
    }
}
