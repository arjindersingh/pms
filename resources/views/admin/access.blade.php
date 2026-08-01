@extends('layouts.administrator')
@section('title', 'Access Management')
@section('content')
    <div class="mb-4">
        <h1 class="h2">Access Management</h1>
        <p class="text-secondary mb-0">Control module access and menu-level CRUD permissions for every user type and subtype.</p>
    </div>
    <livewire:admin.access-matrix />
@endsection
