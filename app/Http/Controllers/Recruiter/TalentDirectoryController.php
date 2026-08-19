<?php

namespace App\Http\Controllers\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\CandidateProfile;
use App\Models\RecruiterCandidateCommunication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TalentDirectoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPlanFeature('talent_directory'), 403, 'Your current plan does not include the talent directory.');

        $profiles = CandidateProfile::query()
            ->where('is_public', true)
            ->with(['user', 'skills', 'experiences'])
            ->when($request->string('search')->isNotEmpty(), function ($query) use ($request) {
                $search = $request->string('search')->toString();
                $query->where(fn ($candidate) => $candidate
                    ->where('headline', 'like', "%{$search}%")
                    ->orWhereHas('skills', fn ($skills) => $skills->where('display_name', 'like', "%{$search}%")));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('recruiter.talent.index', ['profiles' => $profiles, 'canViewFullProfile' => $request->user()->hasPlanFeature('full_profile')]);
    }

    public function show(Request $request, CandidateProfile $candidateProfile): View
    {
        abort_unless($candidateProfile->is_public, 404);
        abort_unless($request->user()->hasPlanFeature('full_profile'), 403, 'Upgrade your plan to open full talent profiles.');
        $candidateProfile->load(['user', 'skills', 'languages', 'educations', 'experiences', 'projects', 'talents']);
        $candidateCanReceiveMessages = $candidateProfile->user->hasPlanFeature('receive_portal_messages');
        $candidateCanReceiveInvitations = $candidateProfile->user->hasPlanFeature('receive_interview_invitations');

        return view('recruiter.talent.show', [
            'profile' => $candidateProfile,
            'canViewContact' => $request->user()->hasPlanFeature('contact_details'),
            'canMessage' => $candidateCanReceiveMessages && $request->user()->hasPlanFeature('portal_messages'),
            'canInvite' => $candidateCanReceiveInvitations && $request->user()->hasPlanFeature('interview_invitations'),
            'candidateCanReceive' => $candidateCanReceiveMessages || $candidateCanReceiveInvitations,
        ]);
    }

    public function contact(Request $request, CandidateProfile $candidateProfile): RedirectResponse
    {
        abort_unless($candidateProfile->is_public, 404);
        $data = $request->validate([
            'type' => ['required', Rule::in(['message', 'interview'])],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
            'scheduled_at' => ['nullable', 'required_if:type,interview', 'date', 'after:now'],
            'meeting_location' => ['nullable', 'required_if:type,interview', 'string', 'max:255'],
        ]);
        $feature = $data['type'] === 'interview' ? 'interview_invitations' : 'portal_messages';
        $candidateFeature = $data['type'] === 'interview' ? 'receive_interview_invitations' : 'receive_portal_messages';
        abort_unless($request->user()->hasPlanFeature($feature), 403, 'Your current plan does not include this service.');
        abort_unless($candidateProfile->user->hasPlanFeature($candidateFeature), 422, 'This candidate cannot receive this communication type on their current plan.');

        RecruiterCandidateCommunication::create($data + [
            'recruiter_id' => $request->user()->id,
            'candidate_id' => $candidateProfile->user_id,
            'status' => 'sent',
        ]);

        return back()->with('status', $data['type'] === 'interview' ? 'Interview invitation sent.' : 'Message sent in the portal.');
    }
}
