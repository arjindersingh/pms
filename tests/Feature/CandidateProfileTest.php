<?php

namespace Tests\Feature;

use App\Models\CandidateProfile;
use App\Models\Country;
use App\Models\Degree;
use App\Models\EducationalInstitution;
use App\Models\EducationAuthority;
use App\Models\QualificationLevel;
use App\Models\State;
use App\Models\Subject;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

        $subject = Subject::where('code', 'COMPUTER_SCIENCE')->firstOrFail();
        $degree = Degree::where('code', 'BTECH')->firstOrFail();
        $institution = EducationalInstitution::where('code', 'IIT_ROPAR')->firstOrFail();
        $authority = EducationAuthority::where('code', 'PTU')->firstOrFail();
        $this->actingAs($candidate)->post(route('talent.profile.education'), [
            'qualification_level_id' => QualificationLevel::where('code', 'UG')->value('id'),
            'degree_id' => $degree->id, 'specialization' => 'Computer Science', 'educational_institution_id' => $institution->id, 'education_authority_id' => $authority->id,
            'subjects' => [$subject->id],
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('candidate_educations', ['candidate_profile_id' => $profile->id, 'degree_name' => 'Bachelor of Technology (B.Tech.)']);
        $this->assertDatabaseHas('candidate_educations', ['candidate_profile_id' => $profile->id, 'institution_name' => 'Indian Institute of Technology Ropar', 'board_university' => 'I.K. Gujral Punjab Technical University']);

        $education = $profile->educations()->where('degree_id', $degree->id)->firstOrFail();
        $this->assertDatabaseHas('candidate_education_subject', ['candidate_education_id' => $education->id, 'subject_id' => $subject->id]);
        $this->get(route('talent.profile.edit', 'education'))
            ->assertOk()->assertSee('Added education')->assertSee('Computer Science')->assertSee('education-table');
        $subject = Subject::where('code', 'MATHEMATICS')->firstOrFail();
        $this->postJson(route('talent.profile.education.subjects.store', $education), ['subject_id' => $subject->id])
            ->assertOk()->assertJsonPath('subject.name', 'Mathematics');
        $this->assertDatabaseHas('candidate_education_subject', ['candidate_education_id' => $education->id, 'subject_id' => $subject->id]);

        $this->deleteJson(route('talent.profile.education.subjects.destroy', [$education, $subject]))
            ->assertOk()->assertJson(['removed' => true]);
        $this->assertDatabaseMissing('candidate_education_subject', ['candidate_education_id' => $education->id, 'subject_id' => $subject->id]);
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

    public function test_candidate_can_upload_and_remove_profile_photograph(): void
    {
        Storage::fake('public');
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $this->actingAs($candidate)->get(route('talent.profile.edit', 'photograph'))->assertOk()->assertSee('Use camera');
        $profile = $candidate->candidateProfile;

        $photo = UploadedFile::fake()->createWithContent('portrait.png', $this->plainPng(600, 600));
        $this->post(route('talent.profile.photograph'), ['photo' => $photo])
            ->assertRedirect()->assertSessionHasNoErrors();
        $profile->refresh();
        $path = $profile->photo_path;
        $this->assertStringStartsWith("candidates/{$profile->profile_code}/profile/", $path);
        Storage::disk('public')->assertExists($path);
        $this->get(route('talent.profile.edit', 'photograph'))
            ->assertOk()
            ->assertSee('src="/storage/'.$path.'"', false);

        $this->delete(route('talent.profile.photograph.remove'))->assertRedirect();
        Storage::disk('public')->assertMissing($path);
        $this->assertNull($profile->fresh()->photo_path);
    }

    private function plainPng(int $width, int $height): string
    {
        $signature = "\x89PNG\r\n\x1a\n";
        $ihdr = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        $pixels = str_repeat("\x00".str_repeat("\x78\x8c\xc8", $width), $height);

        return $signature.$this->pngChunk('IHDR', $ihdr).$this->pngChunk('IDAT', gzcompress($pixels)).$this->pngChunk('IEND', '');
    }

    private function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
    }

    public function test_reseeding_backfills_new_menu_permissions_for_existing_candidates(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $menuId = DB::table('portal_menus')->where('slug', 'candidate-profile')->value('id');
        DB::table('portal_menu_user')->where('user_id', $candidate->id)->where('portal_menu_id', $menuId)->delete();

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('portal_menu_user', [
            'user_id' => $candidate->id,
            'portal_menu_id' => $menuId,
            'can_view' => true,
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
