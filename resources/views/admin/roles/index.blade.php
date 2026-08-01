@extends('layouts.administrator')
@section('title', 'Roles & Permissions')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">ACCESS CONTROL</span><h1>Roles & permissions</h1><p>Create category-specific quick-start roles, then customize permissions for individual users.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="role-page-grid">
    <section class="dashboard-card">
        <div class="card-heading"><div><span>ROLE TEMPLATES</span><h2>Available roles</h2></div></div>
        @foreach(\App\Enums\UserCategory::cases() as $category)
            <div class="role-category"><h3><i class="bi {{ $category === \App\Enums\UserCategory::Administrator ? 'bi-shield-lock' : ($category === \App\Enums\UserCategory::Recruiter ? 'bi-building' : 'bi-briefcase') }}"></i>{{ $category->label() }} roles</h3>
                <div class="role-list">@foreach($roles->where('category', $category) as $role)<a class="role-card" href="{{ $role->is_super_admin ? '#' : route('admin.roles.edit', $role) }}"><span class="role-card-icon"><i class="bi {{ $role->is_super_admin ? 'bi-stars' : 'bi-person-badge' }}"></i></span><div><strong>{{ $role->name }}</strong><small>{{ $role->description }}</small><span>{{ $role->users_count }} {{ Str::plural('user', $role->users_count) }} · {{ $role->is_active ? 'Active' : 'Inactive' }}</span></div>@if($role->is_super_admin)<b>FULL ACCESS</b>@else<i class="bi bi-chevron-right"></i>@endif</a>@endforeach</div>
            </div>
        @endforeach
    </section>
    <aside class="dashboard-card role-create-card">
        <div class="card-heading"><div><span>NEW TEMPLATE</span><h2>Create a role</h2></div></div>
        <form method="POST" action="{{ route('admin.roles.store') }}">@csrf
            <label class="form-label" for="role-name">Role name</label><input class="form-control" id="role-name" name="name" required placeholder="e.g. Interview Coordinator">
            <label class="form-label mt-3" for="role-category">User category</label><select class="form-select" id="role-category" name="category">@foreach(\App\Enums\UserCategory::cases() as $category)<option value="{{ $category->value }}">{{ $category->label() }}</option>@endforeach</select>
            <label class="form-label mt-3" for="role-description">Description</label><textarea class="form-control" id="role-description" name="description" rows="3"></textarea>
            <button class="btn btn-portal w-100 mt-3" type="submit"><i class="bi bi-plus-lg"></i>Create role</button>
        </form>
        <div class="role-explainer"><i class="bi bi-lightbulb"></i><p><strong>Templates are starting points.</strong> Assigning a role copies its current permissions to the user. Later template edits never erase individual customization.</p></div>
    </aside>
</div>
@endsection
