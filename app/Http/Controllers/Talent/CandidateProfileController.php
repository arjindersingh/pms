<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Degree;
use App\Models\EducationalInstitution;
use App\Models\EducationAuthority;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\ProficiencyLevel;
use App\Models\QualificationLevel;
use App\Models\Skill;
use App\Models\StudyMode;
use App\Models\Subject;
use App\Models\WorkMode;
use App\Models\PublicationType;
use App\Models\PublicationMode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateProfileController extends Controller
{
    private const TABS = ['personal' => 'Personal', 'photograph' => 'Photograph', 'contact' => 'Contact & Address', 'education' => 'Education', 'experience' => 'Experience', 'publications' => 'Publications', 'skills' => 'Skills & Languages', 'preferences' => 'Job Preferences'];

    public function edit(Request $request, string $tab = 'personal'): View
    {
        abort_unless(isset(self::TABS[$tab]), 404);
        $profile = $request->user()->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $request->user()->id, 7, '0', STR_PAD_LEFT)]);
        $profile->load(['educations.subjects', 'educations.qualificationLevel', 'experiences', 'publications.type', 'publications.mode', 'skills', 'languages', 'employmentTypes', 'workModes']);

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
            'publication_type_id' => ['required', Rule::exists('publication_types','id')->where('is_active',true)->whereNull('deleted_at')],
            'publication_mode_id' => ['nullable', Rule::exists('publication_modes','id')->where('is_active',true)->whereNull('deleted_at')],
            'area_of_publication' => ['required','string','max:180'], 'publication_count' => ['required','integer','min:1','max:100000'],
            'title' => ['nullable','string','max:300'], 'publisher_name' => ['nullable','string','max:200'], 'published_on' => ['nullable','date','before_or_equal:today'],
            'edition_or_volume' => ['nullable','string','max:100'], 'identifier' => ['nullable','string','max:150'], 'publication_url' => ['nullable','url','max:500'],
            'co_authors' => ['nullable','string','max:2000'], 'description' => ['nullable','string','max:3000'], 'is_peer_reviewed' => ['nullable','boolean'], 'is_verified' => ['nullable','boolean'],
        ]);
        $data['is_peer_reviewed']=$request->boolean('is_peer_reviewed'); $data['is_verified']=$request->boolean('is_verified');
        $profile = $request->user()->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $request->user()->id, 7, '0', STR_PAD_LEFT)]);
        $profile->publications()->create($data);
        return back()->with('status','Publication information added.');
    }

    public function removeEducationSubject(Request $request, int $education, int $subject): JsonResponse
    {
        $record = $request->user()->candidateProfile->educations()->findOrFail($education);
        $record->subjects()->detach($subject);

        return response()->json(['removed' => true]);
    }

    public function skill(Request $request): RedirectResponse
    {
        $data = $request->validate(['skill_id' => ['required', 'exists:skills,id'], 'proficiency_level_id' => ['nullable', 'exists:proficiency_levels,id'], 'years_experience' => ['nullable', 'numeric', 'between:0,70'], 'is_primary' => ['nullable', 'boolean']]);
        $request->user()->candidateProfile->skills()->syncWithoutDetaching([$data['skill_id'] => ['proficiency_level_id' => $data['proficiency_level_id'] ?? null, 'years_experience' => $data['years_experience'] ?? null, 'is_primary' => $request->boolean('is_primary')]]);

        return back()->with('status', 'Skill added.');
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
        match ($collection) {
            'education' => $profile->educations()->whereKey($record)->delete(),'experience' => $profile->experiences()->whereKey($record)->delete(),'publication' => $profile->publications()->whereKey($record)->delete(),'skill' => $profile->skills()->detach($record),'language' => $profile->languages()->detach($record),default => abort(404)
        };

        return back()->with('status', 'Entry removed.');
    }

    private function masters(): array
    {
        return ['countries' => Country::available()->get(), 'genders' => Gender::available()->get(), 'maritalStatuses' => MaritalStatus::available()->get(), 'qualificationLevels' => QualificationLevel::available()->get(), 'degrees' => Degree::available()->get(), 'educationalInstitutions' => EducationalInstitution::available()->get(), 'educationAuthorities' => EducationAuthority::available()->get(), 'studyModes' => StudyMode::available()->get(), 'employmentTypes' => EmploymentType::available()->get(), 'workModes' => WorkMode::available()->get(), 'skills' => Skill::available()->get(), 'subjects' => Subject::available()->get(), 'languages' => Language::available()->get(), 'proficiencyLevels' => ProficiencyLevel::available()->get()];
    }
}
