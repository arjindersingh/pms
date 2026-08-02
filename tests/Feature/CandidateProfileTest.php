<?php

namespace Tests\Feature;

use App\Models\CandidateProfile;
use App\Models\ConsentType;
use App\Models\Country;
use App\Models\DeclarationType;
use App\Models\Degree;
use App\Models\EducationalInstitution;
use App\Models\EducationAuthority;
use App\Models\Hobby;
use App\Models\HobbyCategory;
use App\Models\InterestLevel;
use App\Models\ProficiencyLevel;
use App\Models\ProjectType;
use App\Models\QualificationLevel;
use App\Models\RecognitionLevel;
use App\Models\ReferenceType;
use App\Models\Skill;
use App\Models\SkillGroup;
use App\Models\SocialPlatform;
use App\Models\State;
use App\Models\Subject;
use App\Models\Talent;
use App\Models\TalentCategory;
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

    public function test_candidate_can_add_and_update_a_detailed_skill(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $skill = Skill::where('code', 'LARAVEL')->firstOrFail();
        $group = SkillGroup::where('code', 'WEB_DEVELOPMENT')->firstOrFail();
        $proficiency = ProficiencyLevel::where('code', 'PROFESSIONAL')->firstOrFail();

        $this->actingAs($candidate)->post(route('talent.profile.skill'), [
            'skill_group_id' => $group->id,
            'skill_id' => $skill->id,
            'proficiency_level_id' => $proficiency->id,
            'years_experience' => 3.5,
            'remarks' => 'Built and maintained production Laravel applications.',
            'is_primary' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('candidate_profile_skill', [
            'candidate_profile_id' => $profile->id,
            'skill_id' => $skill->id,
            'skill_group_id' => $group->id,
            'proficiency_level_id' => $proficiency->id,
            'years_experience' => 3.5,
            'remarks' => 'Built and maintained production Laravel applications.',
            'is_primary' => true,
        ]);

        $this->get(route('talent.profile.edit', 'skills'))
            ->assertOk()
            ->assertSee('Web Development')
            ->assertSee('Built and maintained production Laravel applications.');

        $this->post(route('talent.profile.skill'), [
            'skill_group_id' => $group->id,
            'skill_id' => $skill->id,
            'years_experience' => 4,
            'remarks' => 'Updated experience.',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $profile->skills()->whereKey($skill->id)->count());
        $this->assertDatabaseHas('candidate_profile_skill', [
            'candidate_profile_id' => $profile->id,
            'skill_id' => $skill->id,
            'years_experience' => 4,
            'remarks' => 'Updated experience.',
            'is_primary' => false,
        ]);
    }

    public function test_candidate_can_add_a_project_with_skills_team_and_evidence(): void
    {
        Storage::fake('public');
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $type = ProjectType::where('code', 'OPEN_SOURCE')->firstOrFail();
        $skills = Skill::whereIn('code', ['LARAVEL', 'JAVASCRIPT'])->get();
        $screenshot = UploadedFile::fake()->createWithContent('dashboard.png', $this->plainPng(800, 500));
        $document = UploadedFile::fake()->createWithContent('project-notes.pdf', '%PDF-1.4 project notes');

        $this->actingAs($candidate)->post(route('talent.profile.project'), [
            'project_type_id' => $type->id,
            'title' => 'Community Placement Portal',
            'candidate_role' => 'Lead developer',
            'organization_client' => 'Open Community',
            'team_size' => 3,
            'started_on' => '2025-01-01',
            'currently_active' => '1',
            'description' => 'A community-managed placement platform.',
            'objectives' => 'Connect candidates and employers.',
            'candidate_contribution' => 'Designed and implemented the application.',
            'outcome' => 'Released the first public version.',
            'project_url' => 'https://example.com/project',
            'repository_url' => 'https://github.com/example/project',
            'demo_url' => 'https://demo.example.com',
            'is_featured' => '1',
            'skills' => $skills->pluck('id')->all(),
            'team_members' => [[
                'name' => 'Sam Collaborator',
                'role' => 'Designer',
                'organization' => 'Open Community',
                'profile_url' => 'https://example.com/sam',
            ]],
            'screenshots' => [$screenshot],
            'supporting_documents' => [$document],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $project = $profile->projects()->firstOrFail();
        $this->assertTrue($project->currently_active);
        $this->assertTrue($project->is_featured);
        $this->assertNull($project->ended_on);
        $this->assertCount(2, $project->skills);
        $this->assertDatabaseHas('candidate_project_team_members', ['candidate_project_id' => $project->id, 'name' => 'Sam Collaborator', 'role' => 'Designer']);
        Storage::disk('public')->assertExists($project->screenshots[0]);
        Storage::disk('public')->assertExists($project->supporting_documents[0]);

        $this->get(route('talent.profile.edit', 'projects'))
            ->assertOk()
            ->assertSee('Community Placement Portal')
            ->assertSee('Open source')
            ->assertSee('Sam Collaborator');

        $screenshotPath = $project->screenshots[0];
        $documentPath = $project->supporting_documents[0];
        $this->delete(route('talent.profile.remove', ['project', $project]))->assertRedirect();
        $this->assertDatabaseMissing('candidate_projects', ['id' => $project->id]);
        Storage::disk('public')->assertMissing($screenshotPath);
        Storage::disk('public')->assertMissing($documentPath);
    }

    public function test_candidate_can_add_each_award_and_achievement_type(): void
    {
        Storage::fake('public');
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $level = RecognitionLevel::where('code', 'NATIONAL')->firstOrFail();

        foreach (['award', 'honour', 'scholarship', 'competition'] as $index => $kind) {
            $payload = [
                'kind' => $kind,
                'title' => ucfirst($kind).' achievement',
                'award_type' => 'Merit',
                'issuing_organization' => 'National Skills Council',
                'recognition_level_id' => $level->id,
                'awarded_on' => '2025-06-15',
                'rank_position' => $kind === 'competition' ? 'First place' : null,
                'description' => 'Recognition for outstanding work.',
            ];
            if ($index === 0) {
                $payload['certificate'] = UploadedFile::fake()->createWithContent('certificate.pdf', '%PDF-1.4 certificate');
            }
            $this->actingAs($candidate)->post(route('talent.profile.recognition'), $payload)
                ->assertRedirect()->assertSessionHasNoErrors();
        }

        $this->assertSame(1, $profile->awards()->count());
        $this->assertSame(1, $profile->honours()->count());
        $this->assertSame(1, $profile->scholarships()->count());
        $this->assertSame(1, $profile->competitionResults()->count());
        $award = $profile->awards()->firstOrFail();
        $this->assertSame('pending', $award->verification_status);
        Storage::disk('public')->assertExists($award->certificate_path);

        $this->get(route('talent.profile.edit', 'recognitions'))
            ->assertOk()
            ->assertSee('Award achievement')
            ->assertSee('Honour achievement')
            ->assertSee('Scholarship achievement')
            ->assertSee('Competition achievement')
            ->assertSee('Pending verification');

        $certificatePath = $award->certificate_path;
        $this->delete(route('talent.profile.remove', ['recognition', $award]))->assertRedirect();
        Storage::disk('public')->assertMissing($certificatePath);
        $this->assertDatabaseMissing('candidate_recognitions', ['id' => $award->id]);
    }

    public function test_candidate_can_add_and_update_a_talent(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $talent = Talent::where('code', 'PUBLIC_SPEAKING')->firstOrFail();
        $category = TalentCategory::where('code', 'COMMUNICATION')->firstOrFail();
        $proficiency = ProficiencyLevel::where('code', 'PROFESSIONAL')->firstOrFail();

        $this->actingAs($candidate)->post(route('talent.profile.talent'), [
            'talent_id' => $talent->id,
            'talent_category_id' => $category->id,
            'proficiency_level_id' => $proficiency->id,
            'years_practised' => 6.5,
            'achievements' => 'Delivered keynote talks to audiences of more than 500 people.',
            'evidence_url' => 'https://example.com/speaking-reel',
            'is_featured' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('candidate_profile_talent', [
            'candidate_profile_id' => $profile->id,
            'talent_id' => $talent->id,
            'talent_category_id' => $category->id,
            'proficiency_level_id' => $proficiency->id,
            'years_practised' => 6.5,
            'is_featured' => true,
        ]);
        $this->get(route('talent.profile.edit', 'talents'))
            ->assertOk()
            ->assertSee('Public speaking')
            ->assertSee('Delivered keynote talks')
            ->assertSee('View evidence');

        $this->post(route('talent.profile.talent'), [
            'talent_id' => $talent->id,
            'talent_category_id' => $category->id,
            'years_practised' => 7,
            'achievements' => 'Updated achievement.',
        ])->assertSessionHasNoErrors();
        $this->assertSame(1, $profile->talents()->whereKey($talent->id)->count());
        $this->assertDatabaseHas('candidate_profile_talent', ['candidate_profile_id' => $profile->id, 'talent_id' => $talent->id, 'years_practised' => 7, 'is_featured' => false]);

        $this->delete(route('talent.profile.remove', ['talent', $talent]))->assertRedirect();
        $this->assertDatabaseMissing('candidate_profile_talent', ['candidate_profile_id' => $profile->id, 'talent_id' => $talent->id]);
    }

    public function test_candidate_can_add_and_update_a_hobby(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $hobby = Hobby::where('code', 'PHOTOGRAPHY')->firstOrFail();
        $category = HobbyCategory::where('code', 'CREATIVE')->firstOrFail();
        $interest = InterestLevel::where('code', 'PASSIONATE')->firstOrFail();

        $this->actingAs($candidate)->post(route('talent.profile.hobby'), [
            'hobby_id' => $hobby->id,
            'hobby_category_id' => $category->id,
            'interest_level_id' => $interest->id,
            'years_active' => 5.5,
            'description' => 'Photographing community events and natural landscapes.',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('candidate_profile_hobby', ['candidate_profile_id' => $profile->id, 'hobby_id' => $hobby->id, 'hobby_category_id' => $category->id, 'interest_level_id' => $interest->id, 'years_active' => 5.5]);
        $this->get(route('talent.profile.edit', 'hobbies'))->assertOk()->assertSee('Photography')->assertSee('Photographing community events');

        $this->post(route('talent.profile.hobby'), ['hobby_id' => $hobby->id, 'hobby_category_id' => $category->id, 'years_active' => 6, 'description' => 'Updated interest.'])->assertSessionHasNoErrors();
        $this->assertSame(1, $profile->hobbies()->whereKey($hobby->id)->count());
        $this->assertDatabaseHas('candidate_profile_hobby', ['candidate_profile_id' => $profile->id, 'hobby_id' => $hobby->id, 'years_active' => 6, 'description' => 'Updated interest.']);

        $this->delete(route('talent.profile.remove', ['hobby', $hobby]))->assertRedirect();
        $this->assertDatabaseMissing('candidate_profile_hobby', ['candidate_profile_id' => $profile->id, 'hobby_id' => $hobby->id]);
    }

    public function test_candidate_can_add_and_remove_a_professional_membership(): void
    {
        Storage::fake('public');
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $document = UploadedFile::fake()->createWithContent('membership.pdf', '%PDF-1.4 membership');

        $this->actingAs($candidate)->post(route('talent.profile.membership'), [
            'organization_name' => 'Association of Computing Professionals',
            'membership_type' => 'Professional Member',
            'membership_number' => 'ACP-12345',
            'started_on' => '2022-04-01',
            'expires_on' => '2030-04-01',
            'is_lifetime' => '1',
            'candidate_role' => 'Chapter coordinator',
            'membership_status' => 'active',
            'supporting_document' => $document,
        ])->assertRedirect()->assertSessionHasNoErrors();

        $membership = $profile->professionalMemberships()->firstOrFail();
        $this->assertTrue($membership->is_lifetime);
        $this->assertNull($membership->expires_on);
        Storage::disk('public')->assertExists($membership->supporting_document_path);
        $this->get(route('talent.profile.edit', 'memberships'))->assertOk()->assertSee('Association of Computing Professionals')->assertSee('Chapter coordinator')->assertSee('Lifetime');

        $path = $membership->supporting_document_path;
        $this->delete(route('talent.profile.remove', ['membership', $membership]))->assertRedirect();
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseMissing('candidate_professional_memberships', ['id' => $membership->id]);
    }

    public function test_candidate_can_manage_references_and_only_one_is_primary(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $professional = ReferenceType::where('code', 'PROFESSIONAL')->firstOrFail();

        $this->actingAs($candidate)->post(route('talent.profile.reference'), [
            'reference_type_id' => $professional->id,
            'name' => 'First Reference',
            'designation' => 'Engineering Manager',
            'organization' => 'Example Technologies',
            'relationship_to_candidate' => 'Former manager',
            'email' => 'first@example.com',
            'mobile' => '+1 555 0100',
            'years_known' => 4.5,
            'consent_received' => '1',
            'permission_to_contact' => '1',
            'is_primary' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->post(route('talent.profile.reference'), [
            'reference_type_id' => $professional->id,
            'name' => 'Second Reference',
            'email' => 'second@example.com',
            'consent_received' => '1',
            'permission_to_contact' => '1',
            'is_primary' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(2, $profile->references()->count());
        $this->assertSame(1, $profile->references()->where('is_primary', true)->count());
        $this->assertTrue($profile->references()->where('name', 'Second Reference')->firstOrFail()->is_primary);
        $this->assertFalse($profile->references()->where('name', 'First Reference')->firstOrFail()->is_primary);
        $this->get(route('talent.profile.edit', 'references'))->assertOk()->assertSee('First Reference')->assertSee('Second Reference')->assertSee('Contact permitted');

        $this->post(route('talent.profile.reference'), [
            'reference_type_id' => $professional->id,
            'name' => 'No Consent Reference',
            'permission_to_contact' => '1',
        ])->assertSessionHasErrors('consent_received');
        $this->assertDatabaseMissing('candidate_references', ['name' => 'No Consent Reference']);

        $reference = $profile->references()->where('name', 'First Reference')->firstOrFail();
        $this->delete(route('talent.profile.remove', ['reference', $reference]))->assertRedirect();
        $this->assertDatabaseMissing('candidate_references', ['id' => $reference->id]);
    }

    public function test_candidate_can_manage_social_and_professional_profiles(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $linkedin = SocialPlatform::where('code', 'LINKEDIN')->firstOrFail();
        $github = SocialPlatform::where('code', 'GITHUB')->firstOrFail();
        $other = SocialPlatform::where('code', 'OTHER')->firstOrFail();

        $this->actingAs($candidate)->post(route('talent.profile.social'), [
            'social_platform_id' => $linkedin->id,
            'username' => 'demo-candidate',
            'profile_url' => 'https://www.linkedin.com/in/demo-candidate',
            'is_primary' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('talent.profile.social'), [
            'social_platform_id' => $github->id,
            'username' => 'demo-dev',
            'profile_url' => 'https://github.com/demo-dev',
            'is_primary' => '1',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame(2, $profile->socialProfiles()->count());
        $this->assertSame(1, $profile->socialProfiles()->where('is_primary', true)->count());
        $this->assertTrue($profile->socialProfiles()->where('social_platform_id', $github->id)->firstOrFail()->is_primary);
        $this->assertFalse($profile->socialProfiles()->where('social_platform_id', $linkedin->id)->firstOrFail()->is_primary);
        $this->get(route('talent.profile.edit', 'social'))->assertOk()->assertSee('LinkedIn')->assertSee('GitHub')->assertSee('demo-dev');

        $this->post(route('talent.profile.social'), [
            'social_platform_id' => $other->id,
            'profile_url' => 'https://example.com/other-profile',
        ])->assertSessionHasErrors('custom_platform_name');

        $githubProfile = $profile->socialProfiles()->where('social_platform_id', $github->id)->firstOrFail();
        $this->delete(route('talent.profile.remove', ['social-profile', $githubProfile]))->assertRedirect();
        $this->assertDatabaseMissing('candidate_social_profiles', ['id' => $githubProfile->id]);
    }

    public function test_candidate_declarations_and_consents_create_immutable_audit_records(): void
    {
        $candidate = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $candidate->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $candidate->id, 7, '0', STR_PAD_LEFT)]);
        $declaration = DeclarationType::where('code', 'INFORMATION_CORRECT')->firstOrFail();
        $consent = ConsentType::where('code', 'RECRUITER_CONTACT')->firstOrFail();

        $this->actingAs($candidate)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.42', 'HTTP_USER_AGENT' => 'Candidate Test Browser'])
            ->post(route('talent.profile.declaration'), ['declaration_type_id' => $declaration->id, 'is_accepted' => '1'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.42', 'HTTP_USER_AGENT' => 'Candidate Test Browser'])
            ->post(route('talent.profile.consent'), ['consent_type_id' => $consent->id, 'is_accepted' => '1'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->post(route('talent.profile.consent'), ['consent_type_id' => $consent->id, 'is_accepted' => '0'])
            ->assertRedirect()->assertSessionHasNoErrors();

        $declarationRecord = $profile->declarations()->firstOrFail();
        $this->assertTrue($declarationRecord->is_accepted);
        $this->assertNotNull($declarationRecord->accepted_at);
        $this->assertSame('1.0', $declarationRecord->declaration_version);
        $this->assertSame('203.0.113.42', $declarationRecord->ip_address);
        $this->assertSame('Candidate Test Browser', $declarationRecord->user_agent);
        $this->assertSame(2, $profile->consentRecords()->count());
        $this->assertTrue($profile->consentRecords()->oldest('id')->firstOrFail()->is_accepted);
        $this->assertFalse($profile->consentRecords()->latest('id')->firstOrFail()->is_accepted);
        $this->assertNull($profile->consentRecords()->latest('id')->firstOrFail()->accepted_at);

        $this->get(route('talent.profile.edit', 'declarations'))
            ->assertOk()->assertSee('Information is correct')->assertSee('Recruiter contact allowed')->assertSee('Candidate Test Browser')->assertSee('AUDIT TRAIL');
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
        $this->assertSame(26, SkillGroup::count());
        $this->assertSame(8, RecognitionLevel::count());
        $this->assertSame(9, TalentCategory::count());
        $this->assertSame(16, Talent::count());
        $this->assertSame(9, HobbyCategory::count());
        $this->assertSame(4, InterestLevel::count());
        $this->assertSame(14, Hobby::count());
        $this->assertSame(7, ReferenceType::count());
        $this->assertSame(13, SocialPlatform::count());
        $this->assertSame(2, DeclarationType::count());
        $this->assertSame(6, ConsentType::count());
        $this->assertDatabaseHas('districts', ['display_name' => 'Ludhiana']);
        $this->assertDatabaseHas('employment_types', ['code' => 'FULL_TIME']);
        $this->assertDatabaseHas('languages', ['code' => 'PA']);
        $this->assertDatabaseHas('skill_groups', ['code' => 'ARTIFICIAL_INTELLIGENCE', 'display_name' => 'Artificial Intelligence']);
        $this->assertDatabaseHas('skill_groups', ['code' => 'TRADE_SKILLS', 'display_name' => 'Trade Skills']);
    }

    public function test_recruiter_cannot_open_candidate_profile_editor(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $this->actingAs($recruiter)->get(route('talent.profile.edit'))->assertForbidden();
    }
}
