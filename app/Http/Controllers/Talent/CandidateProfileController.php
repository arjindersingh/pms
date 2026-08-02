<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\ConsentType;
use App\Models\Country;
use App\Models\DeclarationType;
use App\Models\Degree;
use App\Models\EducationalInstitution;
use App\Models\EducationAuthority;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Hobby;
use App\Models\HobbyCategory;
use App\Models\InterestLevel;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\ProficiencyLevel;
use App\Models\ProjectType;
use App\Models\PublicationMode;
use App\Models\PublicationType;
use App\Models\QualificationLevel;
use App\Models\RecognitionLevel;
use App\Models\ReferenceType;
use App\Models\Skill;
use App\Models\SkillGroup;
use App\Models\SocialPlatform;
use App\Models\StudyMode;
use App\Models\Subject;
use App\Models\Talent;
use App\Models\TalentCategory;
use App\Models\WorkMode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateProfileController extends Controller
{
    private const DECLARATION_VERSION = '1.0';

    private const TABS = ['personal' => 'Personal', 'photograph' => 'Photograph', 'contact' => 'Contact & Address', 'education' => 'Education', 'experience' => 'Experience', 'projects' => 'Projects', 'recognitions' => 'Awards & Achievements', 'memberships' => 'Professional Memberships', 'references' => 'References', 'social' => 'Social & Professional Profiles', 'declarations' => 'Declarations & Consent', 'publications' => 'Publications', 'skills' => 'Skills & Languages', 'talents' => 'Talents', 'hobbies' => 'Hobbies & Interests', 'preferences' => 'Job Preferences'];

    public function edit(Request $request, string $tab = 'personal'): View
    {
        abort_unless(isset(self::TABS[$tab]), 404);
        $profile = $request->user()->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $request->user()->id, 7, '0', STR_PAD_LEFT)]);
        $profile->load(['educations.subjects', 'educations.qualificationLevel', 'experiences', 'projects.type', 'projects.skills', 'projects.teamMembers', 'recognitions.level', 'professionalMemberships', 'references.type', 'socialProfiles.platform', 'declarations.type', 'consentRecords.type', 'publications.type', 'publications.mode', 'skills', 'talents', 'hobbies', 'languages', 'employmentTypes', 'workModes']);

        return view('talent.profile.edit', ['profile' => $profile, 'tab' => $tab, 'tabs' => self::TABS, 'publicationTypes' => PublicationType::available()->get(), 'publicationModes' => PublicationMode::available()->get()] + $this->masters());
    }

    public function update(Request $request, string $tab): RedirectResponse
    {
        abort_unless(isset(self::TABS[$tab]), 404);
        $profile = $request->user()->candidateProfile()->firstOrFail();
        $rules = match ($tab) {
            'personal' => ['first_name' => ['required', 'string', 'max:100'], 'middle_name' => ['nullable', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'], 'date_of_birth' => ['nullable', 'date', 'before:today'], 'gender_id' => ['nullable', 'exists:genders,id'], 'marital_status_id' => ['nullable', 'exists:marital_statuses,id'], 'nationality_country_id' => ['nullable', 'exists:countries,id'], 'headline' => ['nullable', 'string', 'max:180'], 'career_objective' => ['nullable', 'string', 'max:1500'], 'professional_summary' => ['nullable', 'string', 'max:3000'], 'is_public' => ['nullable', 'boolean']],
            'contact' => ['mobile' => ['nullable', 'string', 'max:30'], 'whatsapp' => ['nullable', 'string', 'max:30'], 'alternate_email' => ['nullable', 'email', 'max:255'], 'linkedin_url' => ['nullable', 'url', 'max:255'], 'portfolio_url' => ['nullable', 'url', 'max:255'], 'address_line_1' => ['nullable', 'string', 'max:255'], 'address_line_2' => ['nullable', 'string', 'max:255'], 'city' => ['nullable', 'string', 'max:100'], 'state' => ['nullable', 'string', 'max:100'], 'country_id' => ['nullable', 'exists:countries,id'], 'postal_code' => ['nullable', 'string', 'max:20'], 'email_allowed' => ['nullable', 'boolean'], 'sms_allowed' => ['nullable', 'boolean'], 'whatsapp_allowed' => ['nullable', 'boolean'], 'job_alerts_allowed' => ['nullable', 'boolean']],
            'preferences' => ['availability_status' => ['nullable', Rule::in(['available', 'employed', 'not-looking'])], 'available_from' => ['nullable', 'date'], 'willing_to_relocate' => ['nullable', 'boolean'], 'willing_to_travel' => ['nullable', 'boolean'], 'expected_salary_min' => ['nullable', 'numeric', 'min:0'], 'expected_salary_max' => ['nullable', 'numeric', 'gte:expected_salary_min'], 'employment_types' => ['array'], 'employment_types.*' => ['exists:employment_types,id'], 'work_modes' => ['array'], 'work_modes.*' => ['exists:work_modes,id']],
            default => [],
        };
        abort_if($rules === [], 405);
        $data = $request->validate($rules);
        foreach (['is_public', 'email_allowed', 'sms_allowed', 'whatsapp_allowed', 'job_alerts_allowed', 'willing_to_relocate', 'willing_to_travel'] as $field) {
            if (array_key_exists($field, $rules)) {
                $data[$field] = $request->boolean($field);
            }
        }
        if ($tab === 'personal') {
            $request->user()->update(['name' => trim($data['first_name'].' '.$data['last_name'])]);
        }
        if ($tab === 'preferences') {
            $profile->employmentTypes()->sync($data['employment_types'] ?? []);
            $profile->workModes()->sync($data['work_modes'] ?? []);
            unset($data['employment_types'],$data['work_modes']);
        }
        $profile->update($data);

        return back()->with('status', self::TABS[$tab].' saved.');
    }

    public function education(Request $request): RedirectResponse
    {
        $data = $request->validate(['qualification_level_id' => ['required', 'exists:qualification_levels,id'], 'degree_id' => ['required', Rule::exists('degrees', 'id')->where(fn ($query) => $query->where('qualification_level_id', $request->input('qualification_level_id'))->where('is_active', true)->whereNull('deleted_at'))], 'specialization' => ['nullable', 'string', 'max:150'], 'educational_institution_id' => ['required', Rule::exists('educational_institutions', 'id')->where('is_active', true)->whereNull('deleted_at')], 'education_authority_id' => ['required', Rule::exists('education_authorities', 'id')->where('is_active', true)->whereNull('deleted_at')], 'country_id' => ['nullable', 'exists:countries,id'], 'study_mode_id' => ['nullable', 'exists:study_modes,id'], 'start_year' => ['nullable', 'integer', 'between:1950,2100'], 'passing_year' => ['nullable', 'integer', 'between:1950,2100'], 'currently_studying' => ['nullable', 'boolean'], 'result' => ['nullable', 'string', 'max:50'], 'subjects' => ['nullable', 'array', 'max:30'], 'subjects.*' => ['integer', 'distinct', 'exists:subjects,id']]);
        $subjects = $data['subjects'] ?? [];
        unset($data['subjects']);
        $data['degree_name'] = Degree::findOrFail($data['degree_id'])->display_name;
        $data['institution_name'] = EducationalInstitution::findOrFail($data['educational_institution_id'])->display_name;
        $data['board_university'] = EducationAuthority::findOrFail($data['education_authority_id'])->display_name;
        $data['currently_studying'] = $request->boolean('currently_studying');
        $education = $request->user()->candidateProfile->educations()->create($data);
        $education->subjects()->sync($subjects);

        return back()->with('status', 'Education added.');
    }

    public function photograph(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=240,min_height=240,max_width=6000,max_height=6000'],
        ], ['photo.dimensions' => 'Please use a photograph that is at least 240 × 240 pixels.']);
        $profile = $request->user()->candidateProfile()->firstOrFail();
        $extension = $validated['photo']->extension() ?: 'jpg';
        $path = $validated['photo']->storeAs("candidates/{$profile->profile_code}/profile", 'photograph-'.now()->format('YmdHis').'.'.$extension, 'public');

        if ($profile->photo_path && $profile->photo_path !== $path) {
            Storage::disk('public')->delete($profile->photo_path);
        }
        $profile->update(['photo_path' => $path, 'photo_updated_at' => now()]);

        return back()->with('status', 'Profile photograph saved.');
    }

    public function removePhotograph(Request $request): RedirectResponse
    {
        $profile = $request->user()->candidateProfile()->firstOrFail();
        if ($profile->photo_path) {
            Storage::disk('public')->delete($profile->photo_path);
        }
        $profile->update(['photo_path' => null, 'photo_updated_at' => null]);

        return back()->with('status', 'Profile photograph removed.');
    }

    public function experience(Request $request): RedirectResponse
    {
        $data = $request->validate(['organization_name' => ['required', 'string', 'max:200'], 'designation' => ['required', 'string', 'max:150'], 'employment_type_id' => ['nullable', 'exists:employment_types,id'], 'work_mode_id' => ['nullable', 'exists:work_modes,id'], 'country_id' => ['nullable', 'exists:countries,id'], 'city' => ['nullable', 'string', 'max:100'], 'started_on' => ['required', 'date'], 'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'], 'currently_working' => ['nullable', 'boolean'], 'description' => ['nullable', 'string', 'max:2000']]);
        $data['currently_working'] = $request->boolean('currently_working');
        if ($data['currently_working']) {
            $data['ended_on'] = null;
        } $request->user()->candidateProfile->experiences()->create($data);

        return back()->with('status', 'Experience added.');
    }

    public function project(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_type_id' => ['nullable', Rule::exists('project_types', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:200'],
            'candidate_role' => ['nullable', 'string', 'max:150'],
            'organization_client' => ['nullable', 'string', 'max:200'],
            'team_size' => ['nullable', 'integer', 'between:1,10000'],
            'started_on' => ['nullable', 'date'],
            'ended_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'currently_active' => ['nullable', 'boolean'],
            'description' => ['nullable', 'string', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'candidate_contribution' => ['nullable', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'project_url' => ['nullable', 'url', 'max:500'],
            'repository_url' => ['nullable', 'url', 'max:500'],
            'demo_url' => ['nullable', 'url', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
            'skills' => ['nullable', 'array', 'max:50'],
            'skills.*' => ['integer', 'distinct', Rule::exists('skills', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'team_members' => ['nullable', 'array', 'max:100'],
            'team_members.*.name' => ['nullable', 'string', 'max:150'],
            'team_members.*.role' => ['nullable', 'string', 'max:150'],
            'team_members.*.organization' => ['nullable', 'string', 'max:200'],
            'team_members.*.profile_url' => ['nullable', 'url', 'max:500'],
            'screenshots' => ['nullable', 'array', 'max:10'],
            'screenshots.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'supporting_documents' => ['nullable', 'array', 'max:10'],
            'supporting_documents.*' => ['file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip', 'max:10240'],
        ]);

        $skills = $data['skills'] ?? [];
        $members = collect($data['team_members'] ?? [])->filter(fn (array $member) => filled($member['name'] ?? null))->values();
        unset($data['skills'], $data['team_members'], $data['screenshots'], $data['supporting_documents']);
        $data['currently_active'] = $request->boolean('currently_active');
        $data['is_featured'] = $request->boolean('is_featured');
        if ($data['currently_active']) {
            $data['ended_on'] = null;
        }

        $profile = $request->user()->candidateProfile()->firstOrFail();
        $project = DB::transaction(function () use ($profile, $data, $skills, $members) {
            $project = $profile->projects()->create($data);
            $project->skills()->sync($skills);
            $project->teamMembers()->createMany($members->all());

            return $project;
        });

        $directory = "candidates/{$profile->profile_code}/projects/{$project->id}";
        $screenshots = collect($request->file('screenshots', []))->map(fn ($file) => $file->store($directory.'/screenshots', 'public'))->all();
        $documents = collect($request->file('supporting_documents', []))->map(fn ($file) => $file->store($directory.'/documents', 'public'))->all();
        $project->update(['screenshots' => $screenshots ?: null, 'supporting_documents' => $documents ?: null]);

        return back()->with('status', 'Project added.');
    }

    public function recognition(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'kind' => ['required', Rule::in(['award', 'honour', 'scholarship', 'competition'])],
            'title' => ['required', 'string', 'max:200'],
            'award_type' => ['nullable', 'string', 'max:120'],
            'issuing_organization' => ['nullable', 'string', 'max:200'],
            'recognition_level_id' => ['nullable', Rule::exists('recognition_levels', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'awarded_on' => ['nullable', 'date', 'before_or_equal:today'],
            'rank_position' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:3000'],
            'certificate' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        unset($data['certificate']);
        $data['verification_status'] = 'pending';
        $profile = $request->user()->candidateProfile()->firstOrFail();
        $recognition = $profile->recognitions()->create($data);
        if ($request->hasFile('certificate')) {
            $path = $request->file('certificate')->store("candidates/{$profile->profile_code}/recognitions/{$recognition->id}", 'public');
            $recognition->update(['certificate_path' => $path]);
        }

        return back()->with('status', 'Award or achievement added.');
    }

    public function professionalMembership(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'organization_name' => ['required', 'string', 'max:200'],
            'membership_type' => ['nullable', 'string', 'max:150'],
            'membership_number' => ['nullable', 'string', 'max:150'],
            'started_on' => ['nullable', 'date'],
            'expires_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'is_lifetime' => ['nullable', 'boolean'],
            'candidate_role' => ['nullable', 'string', 'max:150'],
            'membership_status' => ['required', Rule::in(['active', 'expired', 'pending', 'suspended', 'inactive'])],
            'supporting_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx', 'max:10240'],
        ]);
        unset($data['supporting_document']);
        $data['is_lifetime'] = $request->boolean('is_lifetime');
        if ($data['is_lifetime']) {
            $data['expires_on'] = null;
        }

        $profile = $request->user()->candidateProfile()->firstOrFail();
        $membership = $profile->professionalMemberships()->create($data);
        if ($request->hasFile('supporting_document')) {
            $path = $request->file('supporting_document')->store("candidates/{$profile->profile_code}/memberships/{$membership->id}", 'public');
            $membership->update(['supporting_document_path' => $path]);
        }

        return back()->with('status', 'Professional membership added.');
    }

    public function reference(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'reference_type_id' => ['nullable', Rule::exists('reference_types', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:150'],
            'designation' => ['nullable', 'string', 'max:150'],
            'organization' => ['nullable', 'string', 'max:200'],
            'relationship_to_candidate' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'years_known' => ['nullable', 'numeric', 'between:0,100'],
            'permission_to_contact' => ['nullable', 'boolean'],
            'consent_received' => ['nullable', 'boolean', 'accepted_if:permission_to_contact,1'],
            'is_primary' => ['nullable', 'boolean'],
        ], ['consent_received.accepted_if' => 'Consent must be received before permission to contact can be enabled.']);
        foreach (['permission_to_contact', 'consent_received', 'is_primary'] as $field) {
            $data[$field] = $request->boolean($field);
        }

        $profile = $request->user()->candidateProfile()->firstOrFail();
        DB::transaction(function () use ($profile, $data) {
            if ($data['is_primary']) {
                $profile->references()->where('is_primary', true)->update(['is_primary' => false]);
            }
            $profile->references()->create($data);
        });

        return back()->with('status', 'Reference added.');
    }

    public function socialProfile(Request $request): RedirectResponse
    {
        $profile = $request->user()->candidateProfile()->firstOrFail();
        $otherPlatformId = SocialPlatform::where('code', 'OTHER')->value('id');
        $data = $request->validate([
            'social_platform_id' => ['required', Rule::exists('social_platforms', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'custom_platform_name' => [Rule::requiredIf(fn () => (int) $request->input('social_platform_id') === (int) $otherPlatformId), 'nullable', 'string', 'max:100'],
            'username' => ['nullable', 'string', 'max:150'],
            'profile_url' => ['required', 'url', 'max:500', Rule::unique('candidate_social_profiles')->where(fn ($query) => $query->where('candidate_profile_id', $profile->id)->where('social_platform_id', $request->input('social_platform_id')))],
            'is_primary' => ['nullable', 'boolean'],
        ]);
        $data['is_primary'] = $request->boolean('is_primary');
        if ((int) $data['social_platform_id'] !== (int) $otherPlatformId) {
            $data['custom_platform_name'] = null;
        }

        DB::transaction(function () use ($profile, $data) {
            if ($data['is_primary']) {
                $profile->socialProfiles()->where('is_primary', true)->update(['is_primary' => false]);
            }
            $profile->socialProfiles()->create($data);
        });

        return back()->with('status', 'Social or professional profile added.');
    }

    public function declaration(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'declaration_type_id' => ['required', Rule::exists('declaration_types', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'is_accepted' => ['required', 'boolean'],
        ]);
        $profile = $request->user()->candidateProfile()->firstOrFail();
        $accepted = $request->boolean('is_accepted');
        $profile->declarations()->create([
            'declaration_type_id' => $data['declaration_type_id'],
            'declaration_version' => self::DECLARATION_VERSION,
            'is_accepted' => $accepted,
            'accepted_at' => $accepted ? now() : null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
        ]);

        return back()->with('status', 'Declaration response recorded.');
    }

    public function consent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'consent_type_id' => ['required', Rule::exists('consent_types', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'is_accepted' => ['required', 'boolean'],
        ]);
        $profile = $request->user()->candidateProfile()->firstOrFail();
        $accepted = $request->boolean('is_accepted');
        $profile->consentRecords()->create([
            'consent_type_id' => $data['consent_type_id'],
            'declaration_version' => self::DECLARATION_VERSION,
            'is_accepted' => $accepted,
            'accepted_at' => $accepted ? now() : null,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 2000),
        ]);

        return back()->with('status', 'Consent response recorded.');
    }

    public function addEducationSubject(Request $request, int $education): JsonResponse
    {
        $data = $request->validate(['subject_id' => ['required', 'exists:subjects,id']]);
        $record = $request->user()->candidateProfile->educations()->findOrFail($education);
        $record->subjects()->syncWithoutDetaching([$data['subject_id']]);
        $subject = Subject::findOrFail($data['subject_id']);

        return response()->json(['subject' => ['id' => $subject->id, 'name' => $subject->display_name]]);
    }

    public function publication(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'publication_type_id' => ['required', Rule::exists('publication_types', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'publication_mode_id' => ['nullable', Rule::exists('publication_modes', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'area_of_publication' => ['required', 'string', 'max:180'], 'publication_count' => ['required', 'integer', 'min:1', 'max:100000'],
            'title' => ['nullable', 'string', 'max:300'], 'publisher_name' => ['nullable', 'string', 'max:200'], 'published_on' => ['nullable', 'date', 'before_or_equal:today'],
            'edition_or_volume' => ['nullable', 'string', 'max:100'], 'identifier' => ['nullable', 'string', 'max:150'], 'publication_url' => ['nullable', 'url', 'max:500'],
            'co_authors' => ['nullable', 'string', 'max:2000'], 'description' => ['nullable', 'string', 'max:3000'], 'is_peer_reviewed' => ['nullable', 'boolean'], 'is_verified' => ['nullable', 'boolean'],
        ]);
        $data['is_peer_reviewed'] = $request->boolean('is_peer_reviewed');
        $data['is_verified'] = $request->boolean('is_verified');
        $profile = $request->user()->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $request->user()->id, 7, '0', STR_PAD_LEFT)]);
        $profile->publications()->create($data);

        return back()->with('status', 'Publication information added.');
    }

    public function removeEducationSubject(Request $request, int $education, int $subject): JsonResponse
    {
        $record = $request->user()->candidateProfile->educations()->findOrFail($education);
        $record->subjects()->detach($subject);

        return response()->json(['removed' => true]);
    }

    public function skill(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'skill_group_id' => ['nullable', Rule::exists('skill_groups', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'skill_id' => ['required', Rule::exists('skills', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'proficiency_level_id' => ['nullable', Rule::exists('proficiency_levels', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'years_experience' => ['nullable', 'numeric', 'between:0,70'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
        $request->user()->candidateProfile->skills()->syncWithoutDetaching([$data['skill_id'] => [
            'skill_group_id' => $data['skill_group_id'] ?? null,
            'proficiency_level_id' => $data['proficiency_level_id'] ?? null,
            'years_experience' => $data['years_experience'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'is_primary' => $request->boolean('is_primary'),
        ]]);

        return back()->with('status', 'Skill added.');
    }

    public function talent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'talent_id' => ['required', Rule::exists('talents', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'talent_category_id' => ['nullable', Rule::exists('talent_categories', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'proficiency_level_id' => ['nullable', Rule::exists('proficiency_levels', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'years_practised' => ['nullable', 'numeric', 'between:0,100'],
            'achievements' => ['nullable', 'string', 'max:3000'],
            'evidence_url' => ['nullable', 'url', 'max:500'],
            'is_featured' => ['nullable', 'boolean'],
        ]);
        $request->user()->candidateProfile->talents()->syncWithoutDetaching([$data['talent_id'] => [
            'talent_category_id' => $data['talent_category_id'] ?? null,
            'proficiency_level_id' => $data['proficiency_level_id'] ?? null,
            'years_practised' => $data['years_practised'] ?? null,
            'achievements' => $data['achievements'] ?? null,
            'evidence_url' => $data['evidence_url'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
        ]]);

        return back()->with('status', 'Talent added or updated.');
    }

    public function hobby(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'hobby_id' => ['required', Rule::exists('hobbies', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'hobby_category_id' => ['nullable', Rule::exists('hobby_categories', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'interest_level_id' => ['nullable', Rule::exists('interest_levels', 'id')->where('is_active', true)->whereNull('deleted_at')],
            'years_active' => ['nullable', 'numeric', 'between:0,100'],
            'description' => ['nullable', 'string', 'max:3000'],
        ]);
        $request->user()->candidateProfile->hobbies()->syncWithoutDetaching([$data['hobby_id'] => [
            'hobby_category_id' => $data['hobby_category_id'] ?? null,
            'interest_level_id' => $data['interest_level_id'] ?? null,
            'years_active' => $data['years_active'] ?? null,
            'description' => $data['description'] ?? null,
        ]]);

        return back()->with('status', 'Hobby added or updated.');
    }

    public function language(Request $request): RedirectResponse
    {
        $data = $request->validate(['language_id' => ['required', 'exists:languages,id'], 'proficiency_level_id' => ['nullable', 'exists:proficiency_levels,id'], 'is_native' => ['nullable', 'boolean']]);
        $request->user()->candidateProfile->languages()->syncWithoutDetaching([$data['language_id'] => ['proficiency_level_id' => $data['proficiency_level_id'] ?? null, 'is_native' => $request->boolean('is_native')]]);

        return back()->with('status', 'Language added.');
    }

    public function remove(Request $request, string $collection, int $record): RedirectResponse
    {
        $profile = $request->user()->candidateProfile;
        if ($collection === 'project') {
            $project = $profile->projects()->findOrFail($record);
            Storage::disk('public')->delete(array_merge($project->screenshots ?? [], $project->supporting_documents ?? []));
            $project->delete();

            return back()->with('status', 'Project removed.');
        }
        if ($collection === 'recognition') {
            $recognition = $profile->recognitions()->findOrFail($record);
            if ($recognition->certificate_path) {
                Storage::disk('public')->delete($recognition->certificate_path);
            }
            $recognition->delete();

            return back()->with('status', 'Award or achievement removed.');
        }
        if ($collection === 'membership') {
            $membership = $profile->professionalMemberships()->findOrFail($record);
            if ($membership->supporting_document_path) {
                Storage::disk('public')->delete($membership->supporting_document_path);
            }
            $membership->delete();

            return back()->with('status', 'Professional membership removed.');
        }
        match ($collection) {
            'education' => $profile->educations()->whereKey($record)->delete(),'experience' => $profile->experiences()->whereKey($record)->delete(),'publication' => $profile->publications()->whereKey($record)->delete(),'reference' => $profile->references()->whereKey($record)->delete(),'social-profile' => $profile->socialProfiles()->whereKey($record)->delete(),'skill' => $profile->skills()->detach($record),'talent' => $profile->talents()->detach($record),'hobby' => $profile->hobbies()->detach($record),'language' => $profile->languages()->detach($record),default => abort(404)
        };

        return back()->with('status', 'Entry removed.');
    }

    private function masters(): array
    {
        return ['countries' => Country::available()->get(), 'genders' => Gender::available()->get(), 'maritalStatuses' => MaritalStatus::available()->get(), 'qualificationLevels' => QualificationLevel::available()->get(), 'degrees' => Degree::available()->get(), 'educationalInstitutions' => EducationalInstitution::available()->get(), 'educationAuthorities' => EducationAuthority::available()->get(), 'studyModes' => StudyMode::available()->get(), 'employmentTypes' => EmploymentType::available()->get(), 'workModes' => WorkMode::available()->get(), 'projectTypes' => ProjectType::available()->get(), 'recognitionLevels' => RecognitionLevel::available()->get(), 'referenceTypes' => ReferenceType::available()->get(), 'socialPlatforms' => SocialPlatform::available()->get(), 'declarationTypes' => DeclarationType::available()->get(), 'consentTypes' => ConsentType::available()->get(), 'skillGroups' => SkillGroup::available()->get(), 'skills' => Skill::available()->get(), 'talentCategories' => TalentCategory::available()->get(), 'talents' => Talent::available()->get(), 'hobbyCategories' => HobbyCategory::available()->get(), 'hobbies' => Hobby::available()->get(), 'interestLevels' => InterestLevel::available()->get(), 'subjects' => Subject::available()->get(), 'languages' => Language::available()->get(), 'proficiencyLevels' => ProficiencyLevel::available()->get()];
    }
}
