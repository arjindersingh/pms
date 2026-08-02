@extends('layouts.administrator')
@section('title', 'Subscription Plans')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">MONETIZATION</span><h1>Subscription plans</h1><p>Pricing and menu access for recruiter and talent accounts.</p></div><a class="btn btn-portal" href="{{ route('admin.subscription-plans.create') }}"><i class="bi bi-plus-lg"></i> New plan</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@foreach([\App\Enums\UserCategory::Recruiter, \App\Enums\UserCategory::Talent] as $category)
<section class="dashboard-card mb-4"><div class="card-heading"><div><span>{{ strtoupper($category->value) }}</span><h2>{{ $category->label() }} plans</h2></div></div>
<div class="subscription-plan-grid">
@forelse($plans->where('category', $category) as $plan)
<a class="subscription-plan-card" href="{{ route('admin.subscription-plans.edit', $plan) }}"><div><span class="plan-state {{ $plan->is_active ? '' : 'inactive' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span><h3>{{ $plan->name }}</h3><p>{{ $plan->description }}</p></div><strong>{{ $plan->currency }} {{ number_format((float)$plan->price, 2) }}<small>{{ (float)$plan->price > 0 ? '/ '.$plan->billingPeriodLabel() : ' · '.$plan->billingPeriodLabel() }}</small></strong><footer><span>{{ $plan->subscriptions_count }} active subscriptions</span><i class="bi bi-arrow-right"></i></footer></a>
@empty<div class="account-empty"><i class="bi bi-credit-card"></i><strong>No plans yet</strong></div>@endforelse
</div></section>
@endforeach
@endsection
