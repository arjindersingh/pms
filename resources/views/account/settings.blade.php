@extends('layouts.'.auth()->user()->userType->category->value)

@section('title', 'Account settings')
@section('content')
<div class="dashboard-heading mb-4"><div><span class="dashboard-eyebrow">ACCOUNT</span><h1>Account settings</h1><p>Manage your PlaceFlow experience.</p></div></div>
<section class="portal-card account-panel p-4">
    <h2 class="h5 fw-bold">Appearance</h2>
    <div class="account-theme-note mt-3"><i class="bi bi-palette"></i><div><strong>Choose your interface theme</strong><div class="text-secondary small">Use the palette menu in the top bar. Your choice is applied instantly and saved on this device.</div></div></div>
</section>
@endsection
