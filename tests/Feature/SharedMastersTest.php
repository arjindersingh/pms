<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\District;
use App\Models\EducationalInstitution;
use App\Models\OrganizationCategory;
use App\Models\OrganizationPost;
use App\Models\QualificationLevel;
use App\Models\State;
use App\Models\User;
use App\Support\SharedMasterRegistry;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharedMastersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_qualification_levels_are_seeded_with_requested_codes(): void
    {
        $this->assertSame(13, QualificationLevel::count());
        $this->assertDatabaseHas('qualification_levels', ['code' => 'UG', 'display_name' => 'Graduation / Bachelor’s']);
        $this->assertDatabaseHas('qualification_levels', ['code' => 'POST_DOC', 'display_name' => 'Postdoctoral Research']);
    }

    public function test_dropdown_master_values_are_alphabetical_with_other_entries_last(): void
    {
        $qualificationLevels = QualificationLevel::available()->pluck('display_name');
        $regularQualificationLevels = $qualificationLevels->reject(fn (string $name) => str_starts_with(strtolower($name), 'other'));

        $this->assertSame(
            $regularQualificationLevels->sort(SORT_NATURAL | SORT_FLAG_CASE)->values()->all(),
            $regularQualificationLevels->values()->all(),
        );
        $this->assertSame('Other Qualification', $qualificationLevels->last());

        $institutions = EducationalInstitution::available()->pluck('display_name');
        $this->assertSame('Other / Institution not listed', $institutions->last());
    }

    public function test_world_countries_and_subdivisions_are_seeded(): void
    {
        $india = Country::query()->where('code', 'IN')->firstOrFail();

        $this->assertSame(249, Country::query()->count());
        $this->assertSame(5046, State::query()->count());
        $this->assertSame(36, $india->states()->count());
        $this->assertDatabaseHas('countries', ['code' => 'ZW', 'display_name' => 'Zimbabwe', 'is_active' => true]);
    }

    public function test_administrator_can_manage_country_state_and_district_hierarchy(): void
    {
        $administrator = User::query()->where('email', 'admin@example.com')->firstOrFail();
        $unitedStates = Country::query()->where('code', 'US')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.shared-masters.index', ['type' => 'states', 'parent' => $unitedStates->id]))
            ->assertOk()
            ->assertSee('States / Provinces')
            ->assertSee('California')
            ->assertSee('Show states / provinces for country');

        $this->post(route('admin.shared-masters.store', 'states'), [
            'country_id' => $unitedStates->id,
            'code' => 'TEST',
            'display_name' => 'Test Province',
            'sort_order' => 99990,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $state = State::query()->whereBelongsTo($unitedStates)->where('code', 'TEST')->firstOrFail();

        $this->post(route('admin.shared-masters.store', 'districts'), [
            'state_id' => $state->id,
            'code' => 'CENTRAL',
            'display_name' => 'Central District',
            'sort_order' => 10,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $district = District::query()->whereBelongsTo($state)->where('code', 'CENTRAL')->firstOrFail();
        $this->put(route('admin.shared-masters.update', ['districts', $district]), [
            'state_id' => $state->id,
            'code' => 'CENTRAL',
            'display_name' => 'Central District',
            'sort_order' => 10,
            'is_active' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($district->fresh()->is_active);
    }

    public function test_administrator_can_manage_shared_master_values(): void
    {
        $administrator = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.shared-masters.index'))
            ->assertOk()->assertSee('Shared masters')->assertSee('Graduation / Bachelor’s');

        $this->actingAs($administrator)->post(route('admin.shared-masters.store', 'qualification-levels'), [
            'code' => 'FOUNDATION', 'short_name' => 'Foundation', 'display_name' => 'Foundation Programme',
            'description' => 'Pre-degree programme', 'sort_order' => 135, 'is_active' => '1',
        ])->assertRedirect(route('admin.shared-masters.index', ['type' => 'qualification-levels']))->assertSessionHasNoErrors();

        $record = QualificationLevel::where('code', 'FOUNDATION')->firstOrFail();
        $this->actingAs($administrator)->put(route('admin.shared-masters.update', ['qualification-levels', $record]), [
            'code' => 'FOUNDATION', 'display_name' => 'Foundation Course', 'sort_order' => 135, 'is_active' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('qualification_levels', ['id' => $record->id, 'display_name' => 'Foundation Course', 'is_active' => false]);

        $this->actingAs($administrator)->delete(route('admin.shared-masters.destroy', ['qualification-levels', $record]))->assertRedirect();
        $this->assertSoftDeleted('qualification_levels', ['id' => $record->id]);
    }

    public function test_administrator_sidebar_lists_each_shared_master_type(): void
    {
        $administrator = User::where('email', 'admin@example.com')->firstOrFail();

        $response = $this->actingAs($administrator)->get(route('admin.shared-masters.index', ['type' => 'organization-categories']));

        $response->assertOk()->assertSee('class="master-add-row"', false);

        foreach (SharedMasterRegistry::TYPES as $key => $type) {
            $response->assertSee(route('admin.shared-masters.index', ['type' => $key]), false);
            $response->assertSee($type['label']);
        }
    }

    public function test_administrator_can_manage_organisation_categories_used_by_recruiters(): void
    {
        $administrator = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.shared-masters.index', ['type' => 'organization-categories']))
            ->assertOk()
            ->assertSee('Organisation Categories')
            ->assertSee('Hospital / Healthcare');

        $this->post(route('admin.shared-masters.store', 'organization-categories'), [
            'code' => 'GOVERNMENT_DEPARTMENT',
            'display_name' => 'Government Department',
            'sort_order' => 90,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $category = OrganizationCategory::where('code', 'government_department')->firstOrFail();
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $this->actingAs($recruiter)->get(route('recruiter.profile.organizations'))
            ->assertOk()
            ->assertSee('Government Department');

        $this->actingAs($administrator)->put(route('admin.shared-masters.update', ['organization-categories', $category]), [
            'code' => 'government_department',
            'display_name' => 'Government Office',
            'sort_order' => 90,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('organization_categories', ['id' => $category->id, 'display_name' => 'Government Office']);

        $this->delete(route('admin.shared-masters.destroy', ['organization-categories', $category]))->assertRedirect();
        $this->assertSoftDeleted('organization_categories', ['id' => $category->id]);
    }

    public function test_administrator_can_manage_posts_within_an_organisation_category(): void
    {
        $administrator = User::where('email', 'admin@example.com')->firstOrFail();
        $school = OrganizationCategory::where('code', 'school')->firstOrFail();
        $hospital = OrganizationCategory::where('code', 'hospital')->firstOrFail();

        $this->actingAs($administrator)->get(route('admin.shared-masters.index', ['type' => 'organization-posts']))
            ->assertOk()
            ->assertSee('Organisation Posts')
            ->assertSee('Teacher')
            ->assertSee('Nurse')
            ->assertSee('Organisation Category');

        $this->post(route('admin.shared-masters.store', 'organization-posts'), [
            'organization_category_id' => $school->id,
            'code' => 'school_counsellor',
            'display_name' => 'School Counsellor',
            'sort_order' => 40,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $post = OrganizationPost::where('code', 'school_counsellor')->firstOrFail();
        $this->assertSame($school->id, $post->organization_category_id);

        $this->put(route('admin.shared-masters.update', ['organization-posts', $post]), [
            'organization_category_id' => $hospital->id,
            'code' => 'patient_counsellor',
            'display_name' => 'Patient Counsellor',
            'sort_order' => 40,
            'is_active' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('organization_posts', [
            'id' => $post->id,
            'organization_category_id' => $hospital->id,
            'code' => 'patient_counsellor',
        ]);

        $this->delete(route('admin.shared-masters.destroy', ['organization-posts', $post]))->assertRedirect();
        $this->assertSoftDeleted('organization_posts', ['id' => $post->id]);
    }

    public function test_master_type_cannot_be_used_to_select_an_arbitrary_model(): void
    {
        $administrator = User::where('email', 'admin@example.com')->firstOrFail();
        $this->actingAs($administrator)->get('/admin/shared-masters?type=users')->assertNotFound();
    }

    public function test_non_administrator_cannot_manage_shared_masters(): void
    {
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $this->actingAs($talent)->get(route('admin.shared-masters.index'))->assertForbidden();
    }
}
