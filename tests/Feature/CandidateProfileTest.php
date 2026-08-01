<?php

namespace Tests\Feature;

use App\Models\CandidateProfile;
use App\Models\Country;
use App\Models\QualificationLevel;
use App\Models\State;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_candidate_can_complete_profile_tabs_and_add_education(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $this->actingAs($candidate)->get(route('talent.profile.edit'))->assertOk()->assertSee('Candidate Profile')->assertSee('Search nationality');

        $this->actingAs($candidate)->put(route('talent.profile.update', 'personal'), [
            'first_name' => 'Demo', 'last_name' => 'Candidate', 'headline' => 'Graduate software developer', 'is_public' => '1',
        ])->assertSessionHasNoErrors();
        $profile = CandidateProfile::where('user_id', $candidate->id)->firstOrFail();
        $this->assertTrue($profile->fresh()->is_public);

        $this->actingAs($candidate)->post(route('talent.profile.education'), [
            'qualification_level_id' => QualificationLevel::where('code', 'UG')->value('id'),
            'degree_name' => 'B.Tech.', 'specialization' => 'Computer Science', 'institution_name' => 'Example Institute',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('candidate_educations', ['candidate_profile_id' => $profile->id, 'degree_name' => 'B.Tech.']);
    }

    public function test_candidate_profile_is_available_in_talent_sidebar(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();

        $this->actingAs($candidate)->get(route('talent.dashboard'))
            ->assertOk()
            ->assertSee('Candidate Profile')
            ->assertSee(route('talent.profile.edit'), false);

        $this->assertDatabaseHas('portal_menus', [
            'slug' => 'candidate-profile',
            'route_name' => 'talent.profile.edit',
        ]);
    }

    public function test_geography_and_form_masters_are_seeded_for_fresh_deployments(): void
    {
        $india = Country::where('code', 'IN')->firstOrFail();
        $this->assertSame(36, State::where('country_id', $india->id)->count());
        $this->assertDatabaseHas('districts', ['display_name' => 'Ludhiana']);
        $this->assertDatabaseHas('employment_types', ['code' => 'FULL_TIME']);
        $this->assertDatabaseHas('languages', ['code' => 'PA']);
    }

    public function test_recruiter_cannot_open_candidate_profile_editor(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $this->actingAs($recruiter)->get(route('talent.profile.edit'))->assertForbidden();
    }
}
