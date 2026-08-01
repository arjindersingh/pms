@extends('layouts.'.auth()->user()->userType->category->value)

@section('title', 'Profile')
@section('content')
<div class="dashboard-heading mb-4"><div><span class="dashboard-eyebrow">ACCOUNT</span><h1>Profile</h1><p>Keep your personal details up to date.</p></div></div>
<section class="portal-card account-panel p-4">
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('account.profile.update') }}">@csrf @method('PATCH')
        <div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-4"><label class="form-label" for="email">Email address</label><input class="form-control @error('email') is-invalid @enderror" id="email" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <button class="btn btn-primary" type="submit">Save profile</button>
    </form>
</section>
@endsection
