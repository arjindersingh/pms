<?php

namespace App\Http\Controllers\Talent;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\EmploymentType;
use App\Models\Gender;
use App\Models\Language;
use App\Models\MaritalStatus;
use App\Models\ProficiencyLevel;
use App\Models\QualificationLevel;
use App\Models\Skill;
use App\Models\StudyMode;
use App\Models\WorkMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CandidateProfileController extends Controller
{
    private const TABS = ['personal' => 'Personal', 'contact' => 'Contact & Address', 'education' => 'Education', 'experience' => 'Experience', 'skills' => 'Skills & Languages', 'preferences' => 'Job Preferences'];

    public function edit(Request $request, string $tab = 'personal'): View
    {
        abort_unless(isset(self::TABS[$tab]), 404);
        $profile = $request->user()->candidateProfile()->firstOrCreate([], ['profile_code' => 'CAN-'.str_pad((string) $request->user()->id, 7, '0', STR_PAD_LEFT)]);
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'employmentTypes', 'workModes']);

        return view('talent.profile.edit', ['profile' => $profile, 'tab' => $tab, 'tabs' => self::TABS] + $this->masters());
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
        $data = $request->validate(['qualification_level_id' => ['required', 'exists:qualification_levels,id'], 'degree_name' => ['required', 'string', 'max:150'], 'specialization' => ['nullable', 'string', 'max:150'], 'institution_name' => ['required', 'string', 'max:200'], 'board_university' => ['nullable', 'string', 'max:200'], 'country_id' => ['nullable', 'exists:countries,id'], 'study_mode_id' => ['nullable', 'exists:study_modes,id'], 'start_year' => ['nullable', 'integer', 'between:1950,2100'], 'passing_year' => ['nullable', 'integer', 'between:1950,2100'], 'currently_studying' => ['nullable', 'boolean'], 'result' => ['nullable', 'string', 'max:50']]);
        $data['currently_studying'] = $request->boolean('currently_studying');
        $request->user()->candidateProfile->educations()->create($data);

        return back()->with('status', 'Education added.');
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
            'education' => $profile->educations()->whereKey($record)->delete(),'experience' => $profile->experiences()->whereKey($record)->delete(),'skill' => $profile->skills()->detach($record),'language' => $profile->languages()->detach($record),default => abort(404)
        };

        return back()->with('status', 'Entry removed.');
    }

    private function masters(): array
    {
        return ['countries' => Country::available()->get(), 'genders' => Gender::available()->get(), 'maritalStatuses' => MaritalStatus::available()->get(), 'qualificationLevels' => QualificationLevel::available()->get(), 'studyModes' => StudyMode::available()->get(), 'employmentTypes' => EmploymentType::available()->get(), 'workModes' => WorkMode::available()->get(), 'skills' => Skill::available()->get(), 'languages' => Language::available()->get(), 'proficiencyLevels' => ProficiencyLevel::available()->get()];
    }
}
