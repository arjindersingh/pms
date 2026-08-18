<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\OrganizationCategory;
use App\Models\RecruiterOrganization;
use App\Models\State;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function basic(Request $request): View
    {
        return $this->section($request, 'basic');
    }

    public function contact(Request $request): View
    {
        return $this->section($request, 'contact');
    }

    public function organizations(Request $request): View
    {
        return $this->section($request, 'organizations');
    }

    public function updateBasic(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'designation' => ['nullable', 'string', 'max:120'],
            'professional_summary' => ['nullable', 'string', 'max:3000'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
        ]);
        $request->user()->recruiterProfile()->updateOrCreate([], $data);

        return back()->with('status', 'Basic details saved.');
    }

    public function updateContact(Request $request): RedirectResponse
    {
        [$countryId, $stateId] = $this->locationIds($request);
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'], 'whatsapp' => ['nullable', 'string', 'max:30'],
            'work_email' => ['nullable', 'email', 'max:255'],
            'preferred_contact_method' => ['required', Rule::in(['email', 'phone', 'whatsapp'])],
            'address_line_1' => ['nullable', 'string', 'max:255'], 'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'], 'state' => ['nullable', Rule::exists('states', 'display_name')->where('country_id', $countryId)->where('is_active', true)->whereNull('deleted_at')],
            'district' => ['nullable', Rule::exists('districts', 'display_name')->where('state_id', $stateId)->where('is_active', true)->whereNull('deleted_at')],
            'postal_code' => ['nullable', 'string', 'max:20'], 'country' => ['required', Rule::exists('countries', 'display_name')->where('is_active', true)->whereNull('deleted_at')],
        ]);
        $request->user()->recruiterProfile()->updateOrCreate([], $data);

        return back()->with('status', 'Contact details saved.');
    }

    public function storeOrganization(Request $request): RedirectResponse
    {
        return $this->saveOrganization($request);
    }

    public function updateOrganization(Request $request, RecruiterOrganization $organization): RedirectResponse
    {
        $this->owns($request, $organization);

        return $this->saveOrganization($request, $organization);
    }

    public function destroyOrganization(Request $request, RecruiterOrganization $organization): RedirectResponse
    {
        $this->owns($request, $organization);
        $organization->delete();

        return back()->with('status', 'Organization removed.');
    }

    private function saveOrganization(Request $request, ?RecruiterOrganization $organization = null): RedirectResponse
    {
        [$countryId, $stateId] = $this->locationIds($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'], 'organization_type' => ['required', Rule::exists('organization_categories', 'code')->where('is_active', true)],
            'other_type' => ['nullable', 'required_if:organization_type,other', 'string', 'max:100'],
            'legal_name' => ['nullable', 'string', 'max:180'], 'registration_number' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'url', 'max:255'], 'industry' => ['nullable', 'string', 'max:120'],
            'organization_size' => ['nullable', 'string', 'max:40'], 'description' => ['nullable', 'string', 'max:3000'],
            'hoi_name' => ['nullable', 'string', 'max:150'], 'hoi_designation' => ['nullable', 'string', 'max:120'],
            'hoi_email' => ['nullable', 'email', 'max:255'], 'hoi_phone' => ['nullable', 'string', 'max:30'],
            'placement_contact_name' => ['required', 'string', 'max:150'], 'placement_contact_designation' => ['nullable', 'string', 'max:120'],
            'placement_email' => ['required', 'email', 'max:255'], 'placement_phone' => ['required', 'string', 'max:30'],
            'alternate_phone' => ['nullable', 'string', 'max:30'], 'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'], 'city' => ['required', 'string', 'max:100'],
            'state' => ['required', Rule::exists('states', 'display_name')->where('country_id', $countryId)->where('is_active', true)->whereNull('deleted_at')],
            'district' => ['nullable', Rule::exists('districts', 'display_name')->where('state_id', $stateId)->where('is_active', true)->whereNull('deleted_at')],
            'postal_code' => ['required', 'string', 'max:20'], 'country' => ['required', Rule::exists('countries', 'display_name')->where('is_active', true)->whereNull('deleted_at')],
        ]);
        $profile = $request->user()->recruiterProfile()->firstOrCreate([], ['country' => 'India']);
        $data += ['is_primary' => $request->boolean('is_primary'), 'is_active' => $request->boolean('is_active')];
        if ($data['is_primary']) {
            $profile->organizations()->update(['is_primary' => false]);
        }
        $organization ? $organization->update($data) : $profile->organizations()->create($data);

        return back()->with('status', $organization ? 'Organization updated.' : 'Organization added.');
    }

    private function owns(Request $request, RecruiterOrganization $organization): void
    {
        abort_unless($organization->recruiterProfile?->user_id === $request->user()->id, 404);
    }

    private function section(Request $request, string $section): View
    {
        $profile = $request->user()->recruiterProfile()->firstOrNew();
        if (! $profile->exists) {
            $profile->full_name = $request->user()->name;
            $profile->country = 'India';
        }
        if ($section === 'organizations') {
            $profile->load('organizations');
        }

        return view('recruiter.profile.edit', compact('profile', 'section') + [
            'organizationTypes' => OrganizationCategory::available()->get(),
            'countries' => Country::available()->get(),
        ]);
    }

    /** @return array{?int, ?int} */
    private function locationIds(Request $request): array
    {
        $countryId = Country::available()->where('display_name', $request->string('country')->toString())->value('id');
        $stateId = $countryId
            ? State::available()->where('country_id', $countryId)->where('display_name', $request->string('state')->toString())->value('id')
            : null;

        return [$countryId, $stateId];
    }
}
