@extends('layouts.administrator')
@section('title', 'Review '.$account->name)
@section('content')
<a class="account-back" href="{{ route('admin.accounts.index') }}"><i class="bi bi-arrow-left"></i> Back to accounts</a>
<div class="account-review-head mt-3">
    <div class="account-profile-summary"><span class="profile-avatar review-avatar">{{ collect(explode(' ', $account->name))->map(fn($word) => mb_substr($word,0,1))->take(2)->implode('') }}</span><div><span class="dashboard-kicker">ACCOUNT REVIEW</span><h1>{{ $account->name }}</h1><p>{{ $account->email }} · {{ $account->userType->name }}</p></div></div>
    @php($displayStatus = $account->trashed() ? 'deleted' : $account->account_status->value)
    <span class="account-status account-status--{{ $displayStatus }}"><i></i>{{ $account->trashed() ? 'Deleted' : $account->account_status->label() }}</span>
</div>
@if(session('status'))<div class="alert alert-success mt-4">{{ session('status') }}</div>@endif

<div class="account-review-grid mt-4">
    <div>
        <section class="dashboard-card mb-4">
            <div class="card-heading"><div><span>ACCOUNT DETAILS</span><h2>Review snapshot</h2></div></div>
            <dl class="account-details"><div><dt>Category</dt><dd>{{ $account->userType->category->label() }}</dd></div><div><dt>User type</dt><dd>{{ $account->userType->name }}</dd></div><div><dt>Joined</dt><dd>{{ $account->created_at->format('M j, Y') }}</dd></div><div><dt>Email verified</dt><dd>{{ $account->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</dd></div><div><dt>Last reviewed</dt><dd>{{ $account->last_reviewed_at?->format('M j, Y g:i A') ?? 'Never' }}</dd></div><div><dt>Current reason</dt><dd>{{ $account->status_reason ?: 'No review note recorded' }}</dd></div></dl>
        </section>
        <section class="dashboard-card">
            <div class="card-heading"><div><span>AUDIT TRAIL</span><h2>Review history</h2></div><span class="account-event-count">{{ $account->accountReviews->count() }} events</span></div>
            <div class="review-timeline">
                @forelse($account->accountReviews as $review)<div class="review-event"><span class="review-event-icon"><i class="bi {{ $review->action === 'deleted' ? 'bi-trash3' : ($review->action === 'restored' ? 'bi-arrow-counterclockwise' : 'bi-arrow-left-right') }}"></i></span><div><strong>{{ Str::headline($review->action) }}</strong><span>@if($review->from_status){{ Str::headline($review->from_status) }} → {{ Str::headline($review->to_status) }}@endif</span><p>{{ $review->reason ?: 'No reason provided.' }}</p><small>{{ $review->reviewer?->name ?? 'Former administrator' }} · {{ $review->created_at->format('M j, Y g:i A') }}</small></div></div>@empty<div class="account-empty py-4"><i class="bi bi-clock-history"></i><strong>No review activity yet</strong></div>@endforelse
            </div>
        </section>
    </div>
    <aside>
        <section class="dashboard-card account-action-card">
            <div class="card-heading"><div><span>ADMIN ACTIONS</span><h2>Manage status</h2></div></div>
            @if($account->is(auth()->user()))
                <div class="alert alert-info small mb-0"><i class="bi bi-shield-check me-1"></i>Your own administrator account is protected from lifecycle actions.</div>
            @elseif($account->trashed())
                <p class="text-secondary small">Restore this account to Pending review. Login remains disabled until an administrator activates it.</p>
                <form method="POST" action="{{ route('admin.accounts.restore', $account->id) }}">@csrf<button class="btn btn-portal w-100" type="submit"><i class="bi bi-arrow-counterclockwise"></i> Restore account</button></form>
            @else
                <form method="POST" action="{{ route('admin.accounts.status', $account->id) }}">@csrf @method('PATCH')
                    <label class="form-label" for="status">New status</label><select class="form-select @error('status') is-invalid @enderror" id="status" name="status">@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(old('status', $account->account_status->value) === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                    <label class="form-label mt-3" for="reason">Review note / reason</label><textarea class="form-control @error('reason') is-invalid @enderror" id="reason" name="reason" rows="4" placeholder="Required when restricting an account">{{ old('reason') }}</textarea>@error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <button class="btn btn-portal w-100 mt-3" type="submit"><i class="bi bi-check2-circle"></i> Update account</button>
                </form>
                <hr class="my-4">
                <details class="danger-zone"><summary>Delete account</summary><p>This is recoverable. The account moves to the deleted list and cannot sign in.</p><form method="POST" action="{{ route('admin.accounts.destroy', $account->id) }}">@csrf @method('DELETE')<textarea class="form-control mb-2" name="reason" rows="3" required placeholder="Reason for deletion"></textarea><button class="btn btn-outline-danger w-100" type="submit"><i class="bi bi-trash3"></i> Move to deleted</button></form></details>
            @endif
        </section>
        @if(!$account->trashed())
        <section class="dashboard-card account-action-card position-static mt-4">
            <div class="card-heading"><div><span>ROLE & ACCESS</span><h2>{{ $account->role?->name ?? 'No role assigned' }}</h2></div></div>
            @if($account->isSuperAdmin())<div class="super-admin-note"><i class="bi bi-stars"></i><div><strong>Unrestricted access</strong><small>Super Admin permissions cannot be reduced.</small></div></div>
            @else<form method="POST" action="{{ route('admin.accounts.role',$account->id) }}">@csrf @method('PUT')<label class="form-label" for="user_role_id">Assign {{ $account->userType->category->label() }} role</label><select class="form-select" id="user_role_id" name="user_role_id">@foreach($roles as $role)<option value="{{ $role->id }}" @selected($account->user_role_id === $role->id)>{{ $role->name }}</option>@endforeach</select><div class="form-text">The role controls module access for this account.</div><button class="btn btn-portal-light w-100 mt-3" type="submit"><i class="bi bi-arrow-repeat"></i>Assign role</button></form>
            @endif
        </section>
        @if(in_array($account->userType->category, [\App\Enums\UserCategory::Recruiter, \App\Enums\UserCategory::Talent], true))
        <section class="dashboard-card account-action-card position-static mt-4">
            <div class="card-heading"><div><span>SUBSCRIPTION</span><h2>{{ $account->activeSubscription?->plan?->name ?? 'No active plan' }}</h2></div>@if($account->activeSubscription)<span class="plan-state">Active</span>@endif</div>
            @if($account->activeSubscription)<p class="text-secondary small">{{ $account->activeSubscription->currency }} {{ number_format((float)$account->activeSubscription->price, 2) }} · {{ Str::headline($account->activeSubscription->billing_period) }}@if($account->activeSubscription->ends_at) · Ends {{ $account->activeSubscription->ends_at->format('M j, Y') }}@endif</p>@endif
            <form method="POST" action="{{ route('admin.accounts.subscription',$account->id) }}">@csrf @method('PUT')
                <label class="form-label" for="subscription_plan_id">Assign plan</label><select class="form-select" id="subscription_plan_id" name="subscription_plan_id">@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected($account->activeSubscription?->subscription_plan_id === $plan->id)>{{ $plan->name }} — {{ $plan->currency }} {{ number_format((float)$plan->price, 2) }}/{{ Str::headline($plan->billing_period) }}</option>@endforeach</select>
                <label class="form-label mt-3" for="ends_at">End date (optional)</label><input class="form-control" id="ends_at" name="ends_at" type="datetime-local">
                <label class="form-label mt-3" for="subscription-note">Admin note (optional)</label><textarea class="form-control" id="subscription-note" name="note" rows="2"></textarea>
                <button class="btn btn-portal w-100 mt-3" type="submit"><i class="bi bi-credit-card"></i>Assign subscription</button>
            </form>
        </section>
        @endif
        @endif
    </aside>
</div>
@endsection
