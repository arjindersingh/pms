<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use App\Models\OrganizationCategory;
use App\Models\OrganizationPost;
use App\Models\RecruiterOrganization;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        $profile = $request->user()->recruiterProfile;

        return view('recruiter.job-postings.index', [
            'postings' => $profile?->jobPostings()->with('organization')->latest()->get() ?? collect(),
        ]);
    }

    public function create(Request $request): View
    {
        return $this->form($request, new JobPosting);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->recruiterProfile;
        abort_unless($profile, 422, 'Add an organisation before creating a job posting.');
        $profile->jobPostings()->create($this->validated($request, $profile->id));

        return redirect()->route('recruiter.job-postings.index')->with('status', 'Job posting created.');
    }

    public function edit(Request $request, JobPosting $jobPosting): View
    {
        $this->owns($request, $jobPosting);

        return $this->form($request, $jobPosting);
    }

    public function update(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->owns($request, $jobPosting);
        $jobPosting->update($this->validated($request, $jobPosting->recruiter_profile_id));

        return redirect()->route('recruiter.job-postings.index')->with('status', 'Job posting updated.');
    }

    public function destroy(Request $request, JobPosting $jobPosting): RedirectResponse
    {
        $this->owns($request, $jobPosting);
        $jobPosting->delete();

        return back()->with('status', 'Job posting removed.');
    }

    private function form(Request $request, JobPosting $jobPosting): View
    {
        $profile = $request->user()->recruiterProfile;

        return view('recruiter.job-postings.form', [
            'jobPosting' => $jobPosting,
            'organizations' => $profile?->organizations()->where('is_active', true)->get() ?? collect(),
            'categories' => OrganizationCategory::available()->with(['posts' => fn ($query) => $query->available()])->get(),
        ]);
    }

    private function validated(Request $request, int $profileId): array
    {
        $data = $request->validate([
            'recruiter_organization_id' => ['required', Rule::exists('recruiter_organizations', 'id')->where('recruiter_profile_id', $profileId)],
            'organization_post_id' => ['nullable', 'integer', 'exists:organization_posts,id'],
            'custom_title' => ['nullable', 'required_without:organization_post_id', 'string', 'max:150'],
            'employment_type' => ['nullable', 'string', 'max:40'], 'work_mode' => ['nullable', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:180'], 'vacancies' => ['required', 'integer', 'min:1', 'max:10000'],
            'salary_min' => ['nullable', 'numeric', 'min:0'], 'salary_max' => ['nullable', 'numeric', 'gte:salary_min'],
            'currency' => ['required', Rule::in(Currency::CODES)], 'description' => ['required', 'string', 'max:10000'],
            'requirements' => ['nullable', 'string', 'max:10000'], 'application_deadline' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['draft', 'published', 'closed'])],
        ]);
        $organization = RecruiterOrganization::findOrFail($data['recruiter_organization_id']);
        $category = OrganizationCategory::where('code', $organization->organization_type)->firstOrFail();
        $post = isset($data['organization_post_id']) ? OrganizationPost::available()->findOrFail($data['organization_post_id']) : null;
        abort_if($post && $post->organization_category_id !== $category->id, 422, 'The selected post does not belong to this organisation category.');
        $data['organization_category_id'] = $category->id;
        $data['title'] = $post?->display_name ?? $data['custom_title'];
        $data['published_at'] = $data['status'] === 'published' ? now() : null;
        unset($data['custom_title']);

        return $data;
    }

    private function owns(Request $request, JobPosting $jobPosting): void
    {
        abort_unless($jobPosting->recruiterProfile?->user_id === $request->user()->id, 404);
    }
}
