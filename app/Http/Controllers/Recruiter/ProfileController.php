<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\RecruiterOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $profile = $request->user()->recruiterProfile()->firstOrNew();
        $profile->load('organizations');
        return view('recruiter.profile.edit', ['profile' => $profile, 'organizationTypes' => RecruiterOrganization::TYPES]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'designation' => ['nullable','string','max:120'], 'phone' => ['required','string','max:30'],
            'alternate_phone' => ['nullable','string','max:30'], 'whatsapp' => ['nullable','string','max:30'],
            'work_email' => ['nullable','email','max:255'], 'linkedin_url' => ['nullable','url','max:255'],
            'professional_summary' => ['nullable','string','max:3000'],
            'preferred_contact_method' => ['required', Rule::in(['email','phone','whatsapp'])],
            'address_line_1' => ['nullable','string','max:255'], 'address_line_2' => ['nullable','string','max:255'],
            'city' => ['nullable','string','max:100'], 'state' => ['nullable','string','max:100'],
            'postal_code' => ['nullable','string','max:20'], 'country' => ['required','string','max:100'],
        ]);
        $request->user()->recruiterProfile()->updateOrCreate([], $data);
        return back()->with('status', 'Recruiter contact profile saved.');
    }

    public function storeOrganization(Request $request): RedirectResponse { return $this->saveOrganization($request); }

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
        $data = $request->validate([
            'name' => ['required','string','max:180'], 'organization_type' => ['required', Rule::in(array_keys(RecruiterOrganization::TYPES))],
            'other_type' => ['nullable','required_if:organization_type,other','string','max:100'],
            'legal_name' => ['nullable','string','max:180'], 'registration_number' => ['nullable','string','max:100'],
            'website' => ['nullable','url','max:255'], 'industry' => ['nullable','string','max:120'],
            'organization_size' => ['nullable','string','max:40'], 'description' => ['nullable','string','max:3000'],
            'placement_contact_name' => ['required','string','max:150'], 'placement_contact_designation' => ['nullable','string','max:120'],
            'placement_email' => ['required','email','max:255'], 'placement_phone' => ['required','string','max:30'],
            'alternate_phone' => ['nullable','string','max:30'], 'address_line_1' => ['required','string','max:255'],
            'address_line_2' => ['nullable','string','max:255'], 'city' => ['required','string','max:100'],
            'state' => ['required','string','max:100'], 'postal_code' => ['required','string','max:20'], 'country' => ['required','string','max:100'],
        ]);
        $profile = $request->user()->recruiterProfile()->firstOrCreate([], ['country' => 'India']);
        $data += ['is_primary' => $request->boolean('is_primary'), 'is_active' => $request->boolean('is_active')];
        if ($data['is_primary']) $profile->organizations()->update(['is_primary' => false]);
        $organization ? $organization->update($data) : $profile->organizations()->create($data);
        return back()->with('status', $organization ? 'Organization updated.' : 'Organization added.');
    }

    private function owns(Request $request, RecruiterOrganization $organization): void
    {
        abort_unless($organization->recruiterProfile?->user_id === $request->user()->id, 404);
    }
}
