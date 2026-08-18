@extends('layouts.recruiter')
@section('title', 'Job Postings')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">TALENT ACQUISITION HUB</span><h1>Job postings</h1><p>Create and manage opportunities across all your organisations.</p></div><a class="btn btn-portal" href="{{ route('recruiter.job-postings.create') }}"><i class="bi bi-plus-lg"></i> Create job</a></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<section class="dashboard-card">
    <div class="card-heading"><div><span>OPPORTUNITIES</span><h2>{{ $postings->count() }} job {{ Str::plural('posting', $postings->count()) }}</h2></div></div>
    <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Position</th><th>Organisation</th><th>Location</th><th>Vacancies</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($postings as $posting)
        <tr><td><strong>{{ $posting->title }}</strong><small class="d-block text-secondary">{{ Str::headline($posting->employment_type ?: 'Flexible') }}</small></td><td>{{ $posting->organization->name }}</td><td>{{ $posting->location ?: 'Not specified' }}</td><td>{{ $posting->vacancies }}</td><td><span class="badge text-bg-{{ $posting->status === 'published' ? 'success' : ($posting->status === 'closed' ? 'secondary' : 'warning') }}">{{ Str::headline($posting->status) }}</span></td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('recruiter.job-postings.edit', $posting) }}">Edit</a> <form class="d-inline" method="POST" action="{{ route('recruiter.job-postings.destroy', $posting) }}" onsubmit="return confirm('Remove this job posting?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Remove</button></form></td></tr>
    @empty<tr><td colspan="6" class="text-center text-secondary py-5">No job postings yet. Create your first opportunity.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
