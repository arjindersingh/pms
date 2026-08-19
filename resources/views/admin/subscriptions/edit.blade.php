@extends('layouts.administrator')
@section('title', $creating ? 'New Subscription Plan' : 'Edit '.$plan->name)
@section('content')
<a class="account-back" href="{{ route('admin.subscription-plans.index') }}"><i class="bi bi-arrow-left"></i> Back to subscription plans</a>
<div class="dashboard-heading mt-3"><div><span class="dashboard-kicker">PLAN MANAGEMENT</span><h1>{{ $creating ? 'Create subscription plan' : $plan->name }}</h1><p>{{ $creating ? 'Set the commercial terms first; menu access is configured after creation.' : 'Manage pricing, availability, and plan-level menu permissions.' }}</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="POST" action="{{ $creating ? route('admin.subscription-plans.store') : route('admin.subscription-plans.update', $plan) }}">@csrf @unless($creating)@method('PUT')@endunless
<section class="dashboard-card mb-4"><div class="card-heading"><div><span>COMMERCIAL SETTINGS</span><h2>Plan details</h2></div></div>
<div class="profile-form-grid">
@if($creating)<div><label class="form-label">Account category</label><select class="form-select" name="category"><option value="recruiter">Recruiter</option><option value="talent">Talent</option></select></div>@endif
<div><label class="form-label">Plan name</label><input class="form-control" name="name" required value="{{ old('name', $plan->name) }}"></div>
<div class="profile-span"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2">{{ old('description', $plan->description) }}</textarea></div>
<div><label class="form-label">Price</label><input class="form-control" name="price" type="number" min="0" step="0.01" required value="{{ old('price', $plan->price ?? 0) }}"></div>
<div><label class="form-label">Currency</label><x-currency-select :selected="$plan->currency ?: \App\Support\Currency::DEFAULT" /></div>
<div><label class="form-label">Billing period</label><select class="form-select" name="billing_period">@foreach(\App\Models\SubscriptionPlan::BILLING_PERIODS as $period => $label)<option value="{{ $period }}" @selected(old('billing_period',$plan->billing_period)===$period)>{{ $label }}</option>@endforeach</select><small class="text-muted">Free plans are automatically saved as N/A.</small></div>
<div><label class="form-label">Display order</label><input class="form-control" name="position" type="number" min="0" required value="{{ old('position', $plan->position ?? 0) }}"></div>
<div class="profile-span form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="is-active" @checked(old('is_active',$plan->is_active))><label class="form-check-label" for="is-active">Plan is available for assignment</label></div>
</div></section>
@unless($creating)
<section class="dashboard-card permission-matrix mb-4"><div class="permission-module-head"><div><i class="bi bi-boxes"></i><strong>{{ $plan->category->label() }} services</strong></div><span>Move any service between plans by checking or unchecking it.</span></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Service</th><th>Description</th><th>Included</th></tr></thead><tbody>@foreach($features as $feature)<tr><td><i class="bi {{ $feature->icon }} me-2"></i><strong>{{ $feature->name }}</strong></td><td>{{ $feature->description }}</td><td><input class="form-check-input" type="checkbox" name="features[]" value="{{ $feature->id }}" @checked(collect(old('features', $plan->features->pluck('id')))->contains($feature->id))></td></tr>@endforeach</tbody></table></div></section>
@foreach($modules as $module)<section class="dashboard-card permission-matrix mb-4"><div class="permission-module-head"><div><i class="bi {{ $module->icon }}"></i><strong>{{ $module->name }} menu permissions</strong></div></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th>Menu</th><th>View</th><th>Create</th><th>Update</th><th>Delete</th></tr></thead><tbody>@foreach($module->menus as $menu)@php($permission=$plan->menus->firstWhere('id',$menu->id)?->pivot)<tr><td>{{ $menu->parent_id ? '— ' : '' }}{{ $menu->name }}</td>@foreach(['view','create','update','delete'] as $ability)<td><input class="form-check-input" type="checkbox" name="menus[{{ $menu->id }}][{{ $ability }}]" value="1" @checked(old("menus.{$menu->id}.{$ability}", (bool)($permission?->{'can_'.$ability})))></td>@endforeach</tr>@endforeach</tbody></table></div></section>@endforeach
@endunless
<div class="permission-savebar"><span><i class="bi bi-shield-check"></i> Permissions apply to every active subscriber on this plan.</span><button class="btn btn-portal" type="submit"><i class="bi bi-check2"></i>{{ $creating ? 'Create plan' : 'Save plan' }}</button></div>
</form>
@endsection
