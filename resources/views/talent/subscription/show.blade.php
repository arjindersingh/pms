@extends('layouts.'.$portalArea)
@section('title', 'Subscription & Billing')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">ACCOUNT · BILLING</span><h1>Subscription & billing</h1><p>Review your plan, available features, renewal options, and payment history.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="row g-4">
    <div class="col-xl-8">
        <section class="dashboard-card mb-4">
            <div class="card-heading"><div><span>CURRENT PLAN</span><h2>{{ $activeSubscription?->plan?->name ?? 'No active subscription' }}</h2></div>@if($activeSubscription)<span class="badge text-bg-success">{{ ucfirst($activeSubscription->status) }}</span>@endif</div>
            @if($activeSubscription)
                <div class="row g-3">
                    <div class="col-sm-3"><small class="text-muted d-block">Price</small><strong>{{ $activeSubscription->currency }} {{ number_format((float)$activeSubscription->price, 2) }}</strong></div>
                    <div class="col-sm-3"><small class="text-muted d-block">Billing period</small><strong>{{ (float)$activeSubscription->price === 0.0 ? 'N/A' : (\App\Models\SubscriptionPlan::BILLING_PERIODS[$activeSubscription->billing_period] ?? Str::headline($activeSubscription->billing_period)) }}</strong></div>
                    <div class="col-sm-3"><small class="text-muted d-block">Started</small><strong>{{ $activeSubscription->starts_at->format('M j, Y') }}</strong></div>
                    <div class="col-sm-3"><small class="text-muted d-block">Renews / Expires</small><strong>{{ $activeSubscription->ends_at?->format('M j, Y') ?? 'No expiry' }}</strong></div>
                </div>
                @if($activeSubscription->plan?->description)<p class="text-muted mt-3 mb-0">{{ $activeSubscription->plan->description }}</p>@endif
            @else
                <div class="alert alert-warning mb-0">Your account has no active subscription. Select a plan below to restore plan access.</div>
            @endif
        </section>

        <div class="card-heading"><div><span>AVAILABLE PLANS</span><h2>Renew or change plan</h2></div></div>
        <div class="subscription-plan-grid">
            @foreach($plans as $plan)
                <section class="subscription-plan-card {{ $activeSubscription?->subscription_plan_id === $plan->id ? 'border-primary' : '' }}">
                    <span class="plan-state {{ $activeSubscription?->subscription_plan_id === $plan->id ? '' : 'inactive' }}">{{ $activeSubscription?->subscription_plan_id === $plan->id ? 'Current plan' : 'Available' }}</span>
                    <h3>{{ $plan->name }}</h3><p>{{ $plan->description }}</p>
                    <strong>{{ $plan->currency }} {{ number_format((float)$plan->price, 2) }}<small>{{ (float)$plan->price > 0 ? '/ '.$plan->billingPeriodLabel() : ' · '.$plan->billingPeriodLabel() }}</small></strong>
                    <ul class="small text-muted mt-3 ps-3">@foreach(($plan->features->isNotEmpty() ? $plan->features : $plan->menus->take(6)) as $benefit)<li>{{ $benefit->name }}</li>@endforeach</ul>
                    <form method="POST" action="{{ route($renewRoute) }}" class="mt-auto pt-3">@csrf<input type="hidden" name="subscription_plan_id" value="{{ $plan->id }}">
                        @if((float)$plan->price > 0)
                            <label class="form-label" for="payment_method_{{ $plan->id }}">Payment method</label>
                            <select class="form-select" id="payment_method_{{ $plan->id }}" name="payment_method_id" required><option value="">Select payment method</option>@foreach($gateways as $gateway)@foreach($gateway->methods as $method)<option value="{{ $method->id }}">{{ $gateway->name }} · {{ $method->name }}</option>@endforeach @endforeach</select>
                            @if($gateways->isEmpty())<small class="text-danger d-block mt-2">No online payment method is currently enabled. Contact support.</small>@endif
                        @endif
                        <button class="btn btn-portal w-100 mt-3" @disabled((float)$plan->price > 0 && $gateways->isEmpty())>{{ $activeSubscription?->subscription_plan_id === $plan->id ? 'Renew plan' : 'Choose plan' }}</button>
                    </form>
                </section>
            @endforeach
        </div>
    </div>

    <div class="col-xl-4">
        <section class="dashboard-card mb-4"><div class="card-heading"><div><span>PAYMENTS</span><h2>Recent transactions</h2></div></div>
            @forelse($transactions as $transaction)<div class="profile-record"><div><strong>{{ $transaction->currency }} {{ number_format((float)$transaction->total, 2) }}</strong><small class="d-block text-muted">{{ $transaction->plan?->name }} · {{ $transaction->created_at->format('M j, Y') }}</small><code class="small">{{ $transaction->reference }}</code></div><span class="badge {{ $transaction->status === 'completed' ? 'text-bg-success' : ($transaction->status === 'failed' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ Str::headline($transaction->status) }}</span></div>@empty<p class="text-muted mb-0">No payment transactions yet.</p>@endforelse
        </section>
        <section class="dashboard-card"><div class="card-heading"><div><span>HISTORY</span><h2>Subscription history</h2></div></div>
            @foreach($history as $subscription)<div class="profile-record"><div><strong>{{ $subscription->plan?->name ?? 'Deleted plan' }}</strong><small class="d-block text-muted">{{ $subscription->starts_at->format('M j, Y') }} · {{ $subscription->ends_at?->format('M j, Y') ?? 'No expiry' }}</small></div><span class="badge text-bg-light border">{{ Str::headline($subscription->status) }}</span></div>@endforeach
        </section>
    </div>
</div>
@endsection
