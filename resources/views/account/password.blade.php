@extends('layouts.'.auth()->user()->userType->category->value)

@section('title', 'Change password')
@section('content')
<div class="dashboard-heading mb-4"><div><span class="dashboard-eyebrow">ACCOUNT</span><h1>Change password</h1><p>Use a strong password you do not use elsewhere.</p></div></div>
<section class="portal-card account-panel p-4">
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('account.password.update') }}">@csrf @method('PUT')
        <div class="mb-3"><label class="form-label" for="current_password">Current password</label><input class="form-control @error('current_password') is-invalid @enderror" id="current_password" type="password" name="current_password" required>@error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-3"><label class="form-label" for="password">New password</label><input class="form-control @error('password') is-invalid @enderror" id="password" type="password" name="password" required>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="mb-4"><label class="form-label" for="password_confirmation">Confirm new password</label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" required></div>
        <button class="btn btn-primary" type="submit">Change password</button>
    </form>
</section>
@endsection
