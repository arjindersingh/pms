<?php

namespace App\Http\Controllers;

use App\Enums\UserCategory;
use App\Models\EmploymentType;
use App\Models\JobSector;
use App\Models\OrganizationCategory;
use App\Models\OrganizationPost;
use App\Models\QualificationLevel;
use App\Models\Skill;
use App\Models\WorkMode;
use App\Services\JobMatchService;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobSearchProfileController extends Controller
{
    public function talent(Request $r, JobMatchService $m): View
    {
        return $this->edit($r, 'talent', $m);
    }

    public function recruiter(Request $r, JobMatchService $m): View
    {
        return $this->edit($r, 'recruiter', $m);
    }

    public function updateTalent(Request $r): RedirectResponse
    {
        return $this->update($r, 'talent');
    }

    public function updateRecruiter(Request $r): RedirectResponse
    {
        return $this->update($r, 'recruiter');
    }

    private function edit(Request $r, string $type, JobMatchService $m): View
    {
        $this->authorizeType($r, $type);
        $profile = $r->user()->jobSearchProfile()->with(['jobTitles', 'skills', 'employmentTypes', 'workModes', 'qualificationLevels', 'organizationCategories', 'organizationPosts'])->firstOrNew(['profile_type' => $type]);

        return view('job-search.edit', ['profile' => $profile, 'type' => $type, 'sectors' => JobSector::available()->with(['specializations' => fn ($q) => $q->available()->with(['titles' => fn ($t) => $t->available()])])->get(), 'skills' => Skill::available()->get(), 'employmentTypes' => EmploymentType::available()->get(), 'workModes' => WorkMode::available()->get(), 'qualificationLevels' => QualificationLevel::available()->get(), 'organizationCategories' => OrganizationCategory::available()->get(), 'organizationPosts' => OrganizationPost::available()->with('category')->get(), 'matches' => $profile->exists ? $m->matches($profile) : collect()]);
    }

    private function update(Request $r, string $type): RedirectResponse
    {
        $this->authorizeType($r, $type);
        $data = $r->validate(['headline' => ['required', 'string', 'max:180'], 'summary' => ['nullable', 'string', 'max:3000'], 'job_titles' => ['required', 'array', 'min:1'], 'job_titles.*' => ['integer', 'exists:job_titles,id'], 'skills' => ['array'], 'skills.*' => ['integer', 'exists:skills,id'], 'employment_types' => ['array'], 'employment_types.*' => ['integer', 'exists:employment_types,id'], 'work_modes' => ['array'], 'work_modes.*' => ['integer', 'exists:work_modes,id'], 'qualification_levels' => ['array'], 'qualification_levels.*' => ['integer', 'exists:qualification_levels,id'], 'organization_categories' => ['array'], 'organization_categories.*' => ['integer', 'exists:organization_categories,id'], 'organization_posts' => ['array'], 'organization_posts.*' => ['integer', 'exists:organization_posts,id'], 'min_experience_years' => ['required', 'integer', 'min:0', 'max:60'], 'max_experience_years' => ['nullable', 'integer', 'gte:min_experience_years', 'max:60'], 'min_annual_salary' => ['nullable', 'numeric', 'min:0'], 'max_annual_salary' => ['nullable', 'numeric', 'gte:min_annual_salary'], 'currency' => ['required', Rule::in(Currency::CODES)], 'preferred_locations_text' => ['nullable', 'string', 'max:1000']]);
        $locations = collect(preg_split('/[\r\n,]+/', $data['preferred_locations_text'] ?? ''))->map(fn ($location) => trim($location))->filter()->unique()->values()->all();
        $profile = $r->user()->jobSearchProfile()->updateOrCreate([], collect($data)->except(['job_titles', 'skills', 'employment_types', 'work_modes', 'qualification_levels', 'organization_categories', 'organization_posts', 'preferred_locations_text'])->all() + ['profile_type' => $type, 'preferred_locations' => $locations, 'location_flexible' => $r->boolean('location_flexible'), 'salary_negotiable' => $r->boolean('salary_negotiable'), 'is_active' => $r->boolean('is_active')]);
        foreach (['jobTitles' => 'job_titles', 'skills' => 'skills', 'employmentTypes' => 'employment_types', 'workModes' => 'work_modes', 'qualificationLevels' => 'qualification_levels', 'organizationCategories' => 'organization_categories', 'organizationPosts' => 'organization_posts'] as $relation => $field) {
            $profile->{$relation}()->sync($data[$field] ?? []);
        }

        return back()->with('status', 'Search criteria saved. Match results have been refreshed.');
    }

    private function authorizeType(Request $r, string $type): void
    {
        $expected = $type === 'talent' ? UserCategory::Talent : UserCategory::Recruiter;
        abort_unless($r->user()->userType?->category === $expected, 403);
    }
}
