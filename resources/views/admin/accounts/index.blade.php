@extends('layouts.administrator')
@section('title', 'Account Review')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">ADMINISTRATION</span><h1>Account review</h1><p>Review access, investigate account state, and manage the complete user lifecycle.</p></div></div>

@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="row g-3 mb-4">
    @foreach([['All accounts',$counts['all'],'bi-people'],['Active',$counts['active'],'bi-person-check'],['Needs attention',$counts['attention'],'bi-exclamation-diamond'],['Deleted',$counts['deleted'],'bi-trash3']] as [$label,$count,$icon])
        <div class="col-6 col-xl-3"><div class="metric-card account-stat"><span class="metric-icon"><i class="bi {{ $icon }}"></i></span><div><strong>{{ $count }}</strong><small>{{ $label }}</small></div></div></div>
    @endforeach
</div>

<section class="dashboard-card">
    <form class="account-filters" method="GET">
        <div class="account-search"><i class="bi bi-search"></i><input name="search" value="{{ request('search') }}" placeholder="Search name or email…" aria-label="Search accounts"></div>
        <select class="form-select" name="status" aria-label="Filter status"><option value="">All statuses</option>@foreach($statuses as $status)<option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>@endforeach<option value="deleted" @selected(request('status') === 'deleted')>Deleted</option></select>
        <select class="form-select" name="category" aria-label="Filter category"><option value="">All categories</option>@foreach(\App\Enums\UserCategory::cases() as $category)<option value="{{ $category->value }}" @selected(request('category') === $category->value)>{{ $category->label() }}</option>@endforeach</select>
        <button class="btn btn-portal" type="submit">Apply filters</button>
        @if(request()->hasAny(['search','status','category']))<a class="btn btn-portal-light" href="{{ route('admin.accounts.index') }}">Clear</a>@endif
    </form>
    <div class="table-responsive mt-3">
        <table class="table portal-table account-table align-middle">
            <thead><tr><th>User</th><th>Type</th><th>Status</th><th>Last reviewed</th><th>Activity</th><th></th></tr></thead>
            <tbody>
            @forelse($users as $account)
                @php($displayStatus = $account->trashed() ? 'deleted' : $account->account_status->value)
                <tr>
                    <td><div class="member-cell"><span>{{ collect(explode(' ', $account->name))->map(fn($word) => mb_substr($word,0,1))->take(2)->implode('') }}</span><div><strong>{{ $account->name }}</strong><small>{{ $account->email }}</small></div></div></td>
                    <td><span class="account-type">{{ $account->userType->name }}</span><small class="d-block text-secondary mt-1">{{ $account->userType->category->label() }}</small></td>
                    <td><span class="account-status account-status--{{ $displayStatus }}"><i></i>{{ $account->trashed() ? 'Deleted' : $account->account_status->label() }}</span>@if($account->status_reason)<small class="status-reason" title="{{ $account->status_reason }}">{{ Str::limit($account->status_reason, 44) }}</small>@endif</td>
                    <td>{{ $account->last_reviewed_at?->diffForHumans() ?? 'Never' }}</td>
                    <td>{{ $account->account_reviews_count }} review {{ Str::plural('event', $account->account_reviews_count) }}</td>
                    <td class="text-end"><a class="btn btn-sm btn-portal-light" href="{{ route('admin.accounts.show', $account->id) }}">Review <i class="bi bi-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="account-empty"><i class="bi bi-person-x"></i><strong>No accounts found</strong><span>Try changing your search or filters.</span></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="account-pagination">{{ $users->links() }}</div>
</section>
@endsection
