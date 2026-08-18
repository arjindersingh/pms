<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\State;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruiterProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_recruiter_can_save_common_details_and_multiple_categorized_organizations(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $this->actingAs($recruiter)->get(route('recruiter.profile.edit'))->assertOk()->assertSee('Recruiter Profile')->assertSee('Hospital / Healthcare');
        $this->put(route('recruiter.profile.update'), ['phone' => '9876543210', 'work_email' => 'hr@example.com', 'preferred_contact_method' => 'phone', 'country' => 'India'])->assertSessionHasNoErrors();
        $base = ['placement_contact_name' => 'Placement Head', 'placement_email' => 'placement@example.com', 'placement_phone' => '9876543210', 'address_line_1' => 'Main Road', 'city' => 'Amritsar', 'state' => 'Punjab', 'district' => 'Amritsar', 'postal_code' => '143001', 'country' => 'India', 'is_active' => 1];
        $this->post(route('recruiter.organizations.store'), $base + ['name' => 'City College', 'organization_type' => 'college', 'is_primary' => 1])->assertSessionHasNoErrors();
        $this->post(route('recruiter.organizations.store'), $base + ['name' => 'General Hospital', 'organization_type' => 'hospital'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('recruiter_profiles', ['user_id' => $recruiter->id, 'phone' => '9876543210']);
        $this->assertDatabaseHas('recruiter_organizations', ['name' => 'City College', 'organization_type' => 'college', 'district' => 'Amritsar', 'is_primary' => true]);
        $this->assertDatabaseHas('recruiter_organizations', ['name' => 'General Hospital', 'organization_type' => 'hospital']);
    }

    public function test_recruiter_cannot_modify_another_recruiters_organization(): void
    {
        $owner = User::where('email', 'recruiter@example.com')->firstOrFail();
        $other = User::where('email', 'agency@example.com')->firstOrFail();
        $profile = $owner->recruiterProfile()->create(['phone' => '1', 'country' => 'India']);
        $organization = $profile->organizations()->create(['name' => 'Private School', 'organization_type' => 'school', 'placement_contact_name' => 'A', 'placement_email' => 'a@example.com', 'placement_phone' => '1', 'address_line_1' => 'Road', 'city' => 'City', 'state' => 'State', 'postal_code' => '1', 'country' => 'India']);
        $this->actingAs($other)->delete(route('recruiter.organizations.destroy', $organization))->assertNotFound();
    }

    public function test_recruiter_profile_sections_are_separate_from_account_profile_and_include_hoi(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($recruiter)->get(route('recruiter.profile.basic'))
            ->assertOk()
            ->assertSee('Basic Detail')
            ->assertSee('This can differ from your account name.')
            ->assertSee(route('recruiter.profile.basic'), false)
            ->assertSee(route('recruiter.profile.contact'), false)
            ->assertSee(route('recruiter.profile.organizations'), false);
        $this->get(route('recruiter.profile.contact'))->assertOk()->assertSee('Contact Detail');
        $this->get(route('recruiter.profile.organizations'))->assertOk()
            ->assertSee('Organisations')->assertSee('Head of Institution (HOI)');

        $this->put(route('recruiter.profile.basic.update'), [
            'full_name' => 'Professional Recruiter Name',
            'designation' => 'Hiring Lead',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('recruiter_profiles', ['user_id' => $recruiter->id, 'full_name' => 'Professional Recruiter Name']);
        $this->assertDatabaseHas('users', ['id' => $recruiter->id, 'name' => 'Demo Recruiter']);
    }

    public function test_recruiter_location_fields_follow_country_state_and_district_hierarchy(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $india = Country::where('code', 'IN')->firstOrFail();
        $punjab = State::whereBelongsTo($india)->where('code', 'PB')->firstOrFail();

        $this->actingAs($recruiter)->get(route('recruiter.profile.contact'))
            ->assertOk()
            ->assertSee('data-location-country', false)
            ->assertSee('data-location-state', false)
            ->assertSee('data-location-district', false);

        $this->get(route('locations.states', $india))
            ->assertOk()
            ->assertJsonFragment(['display_name' => 'Punjab']);
        $this->get(route('locations.districts', $punjab))
            ->assertOk()
            ->assertJsonFragment(['display_name' => 'Amritsar']);

        $this->put(route('recruiter.profile.contact.update'), [
            'phone' => '9876543210',
            'preferred_contact_method' => 'phone',
            'country' => 'India',
            'state' => 'Punjab',
            'district' => 'Amritsar',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('recruiter_profiles', [
            'user_id' => $recruiter->id,
            'country' => 'India',
            'state' => 'Punjab',
            'district' => 'Amritsar',
        ]);

        $this->put(route('recruiter.profile.contact.update'), [
            'phone' => '9876543210',
            'preferred_contact_method' => 'phone',
            'country' => 'India',
            'state' => 'Punjab',
            'district' => 'Not a Punjab district',
        ])->assertSessionHasErrors('district');
    }

    public function test_recruiter_profile_sections_are_visible_in_the_sidebar(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($recruiter)->get(route('recruiter.dashboard'))
            ->assertOk()
            ->assertDontSee('Talent acquisition workspace')
            ->assertDontSee('<div class="sidebar-section-label"><span class="sidebar-label">Recruitment</span></div>', false)
            ->assertSee('Profile')
            ->assertSee('Basic Detail')
            ->assertSee('Contact Detail')
            ->assertSee('Organisations')
            ->assertSee(route('recruiter.profile.basic'), false)
            ->assertSee(route('recruiter.profile.contact'), false)
            ->assertSee(route('recruiter.profile.organizations'), false);
    }
}
