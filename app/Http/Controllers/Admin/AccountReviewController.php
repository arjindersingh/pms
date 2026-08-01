<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountReviewController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->with('userType')->withCount('accountReviews')->withTrashed();

        $query->when($request->filled('search'), fn ($q) => $q->where(fn ($inner) => $inner
            ->where('name', 'like', '%'.$request->string('search').'%')
            ->orWhere('email', 'like', '%'.$request->string('search').'%')));
        $query->when($request->filled('category'), fn ($q) => $q->whereHas('userType', fn ($type) => $type->where('category', $request->string('category'))));
        $query->when($request->filled('status'), function ($q) use ($request) {
            $request->string('status')->value() === 'deleted'
                ? $q->onlyTrashed()
                : $q->whereNull('deleted_at')->where('account_status', $request->string('status'));
        });

        return view('admin.accounts.index', [
            'users' => $query->latest()->paginate(15)->withQueryString(),
            'statuses' => UserAccountStatus::cases(),
            'counts' => ['all' => User::withTrashed()->count(), 'active' => User::where('account_status', 'active')->count(), 'attention' => User::whereIn('account_status', ['pending_review', 'suspended', 'locked'])->count(), 'deleted' => User::onlyTrashed()->count()],
        ]);
    }

    public function show(int $user): View
    {
        $account = User::withTrashed()->with(['userType', 'role', 'accountReviews.reviewer'])->findOrFail($user);

        $roles = UserRole::where('category', $account->userType->category)->where('is_active', true)
            ->when(! request()->user()->isSuperAdmin(), fn ($query) => $query->where('is_super_admin', false))->orderBy('name')->get();

        return view('admin.accounts.show', ['account' => $account, 'statuses' => UserAccountStatus::cases(), 'roles' => $roles]);
    }

    public function updateStatus(Request $request, int $user): RedirectResponse
    {
        $account = User::withTrashed()->findOrFail($user);
        abort_if($account->is($request->user()), 422, 'You cannot change your own account status.');
        abort_if($account->trashed(), 422, 'Restore this account before changing its status.');

        $validated = $request->validate([
            'status' => ['required', Rule::enum(UserAccountStatus::class)],
            'reason' => ['nullable', 'string', 'max:2000', Rule::requiredIf($request->input('status') !== 'active')],
        ]);
        $status = UserAccountStatus::from($validated['status']);
        $from = $account->account_status?->value ?? 'active';

        DB::transaction(function () use ($account, $request, $status, $from, $validated): void {
            $account->update(['account_status' => $status, 'is_active' => $status->allowsLogin(), 'status_reason' => $validated['reason'] ?? null, 'status_changed_at' => now(), 'status_changed_by' => $request->user()->id, 'last_reviewed_at' => now(), 'last_reviewed_by' => $request->user()->id]);
            $account->accountReviews()->create(['reviewed_by' => $request->user()->id, 'action' => 'status_changed', 'from_status' => $from, 'to_status' => $status->value, 'reason' => $validated['reason'] ?? null]);
        });

        return back()->with('status', "{$account->name}'s account is now {$status->label()}.");
    }

    public function destroy(Request $request, int $user): RedirectResponse
    {
        $account = User::findOrFail($user);
        abort_if($account->is($request->user()), 422, 'You cannot delete your own account.');
        $validated = $request->validate(['reason' => ['required', 'string', 'max:2000']]);

        DB::transaction(function () use ($account, $request, $validated): void {
            $account->accountReviews()->create(['reviewed_by' => $request->user()->id, 'action' => 'deleted', 'from_status' => $account->account_status->value, 'to_status' => 'deleted', 'reason' => $validated['reason']]);
            $account->update(['is_active' => false, 'status_reason' => $validated['reason'], 'status_changed_at' => now(), 'status_changed_by' => $request->user()->id]);
            $account->delete();
        });

        return redirect()->route('admin.accounts.index')->with('status', 'Account moved to deleted accounts.');
    }

    public function restore(Request $request, int $user): RedirectResponse
    {
        $account = User::onlyTrashed()->findOrFail($user);
        DB::transaction(function () use ($account, $request): void {
            $account->restore();
            $account->update(['account_status' => UserAccountStatus::PendingReview, 'is_active' => false, 'status_reason' => 'Restored and awaiting administrator review.', 'status_changed_at' => now(), 'status_changed_by' => $request->user()->id]);
            $account->accountReviews()->create(['reviewed_by' => $request->user()->id, 'action' => 'restored', 'from_status' => 'deleted', 'to_status' => 'pending_review', 'reason' => 'Restored and awaiting administrator review.']);
        });

        return back()->with('status', 'Account restored into Pending review status.');
    }
}
