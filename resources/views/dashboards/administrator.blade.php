@extends('layouts.administrator')
@section('title', 'Administrator Dashboard')
@section('content')
    <div class="dashboard-heading">
        <div><span class="dashboard-kicker">OVERVIEW</span><h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ Str::before(auth()->user()->name, ' ') }}.</h1><p>Here’s what is happening across your placement ecosystem today.</p></div>
        <div class="dashboard-actions"><button class="btn btn-portal-light"><i class="bi bi-download"></i><span>Export report</span></button><button class="btn btn-portal"><i class="bi bi-plus-lg"></i><span>Add member</span></button></div>
    </div>

    <div class="row g-3 g-xl-4 metric-row">
        @foreach([
            ['Total members', \App\Models\User::count(), '+12.4%', 'bi-people', 'violet'],
            ['User types', \App\Models\UserType::count(), '3 active groups', 'bi-diagram-3', 'cyan'],
            ['Portal modules', \App\Models\PortalModule::count(), 'All operational', 'bi-grid', 'orange'],
            ['Access policies', \App\Models\PortalMenu::count(), 'Fully configured', 'bi-shield-check', 'green'],
        ] as [$label, $value, $note, $icon, $tone])
            <div class="col-sm-6 col-xl-3"><article class="metric-card"><div class="metric-top"><span class="metric-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span><button><i class="bi bi-three-dots"></i></button></div><strong>{{ number_format($value) }}</strong><span>{{ $label }}</span><small><i class="bi bi-arrow-up-right"></i>{{ $note }}</small></article></div>
        @endforeach
    </div>

    <div class="row g-4 mt-1">
        <div class="col-xl-8"><article class="dashboard-card h-100"><div class="card-heading"><div><span>PLACEMENT ACTIVITY</span><h2>Platform growth</h2></div><select class="form-select form-select-sm"><option>Last 7 days</option><option>Last 30 days</option></select></div><div class="chart-legend"><span><i class="primary"></i>New talent</span><span><i class="secondary"></i>Recruiters</span></div><div class="portal-chart"><div class="chart-y"><span>600</span><span>450</span><span>300</span><span>150</span><span>0</span></div><div class="chart-bars">@foreach([44,58,51,72,64,83,76] as $index => $height)<div class="chart-column"><div><i style="height:{{ $height }}%"></i><b style="height:{{ max(20, $height - 22) }}%"></b></div><span>{{ ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'][$index] }}</span></div>@endforeach</div></div></article></div>
        <div class="col-xl-4"><article class="dashboard-card h-100"><div class="card-heading"><div><span>SECURITY</span><h2>System health</h2></div><span class="status-chip success"><i></i>Healthy</span></div><div class="health-score"><div class="score-ring"><strong>96</strong><small>/100</small></div><p>Your access configuration is in excellent shape.</p></div><div class="health-list"><div><span><i class="bi bi-check-circle-fill"></i>Module policies</span><strong>Complete</strong></div><div><span><i class="bi bi-check-circle-fill"></i>Menu permissions</span><strong>Complete</strong></div><div><span><i class="bi bi-exclamation-circle-fill warning"></i>Profiles pending</span><strong>4</strong></div></div></article></div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-xl-8"><article class="dashboard-card"><div class="card-heading"><div><span>DIRECTORY</span><h2>Recently added members</h2></div><button class="text-button">View all <i class="bi bi-arrow-right"></i></button></div><div class="table-responsive"><table class="table portal-table align-middle"><thead><tr><th>Member</th><th>Type</th><th>Status</th><th>Joined</th><th></th></tr></thead><tbody>@foreach(\App\Models\User::with('userType')->latest()->take(5)->get() as $member)<tr><td><div class="member-cell"><span>{{ mb_substr($member->name,0,1) }}</span><div><strong>{{ $member->name }}</strong><small>{{ $member->email }}</small></div></div></td><td>{{ $member->userType?->name ?? 'Unassigned' }}</td><td><span class="status-chip {{ $member->is_active ? 'success' : 'muted' }}"><i></i>{{ $member->is_active ? 'Active' : 'Inactive' }}</span></td><td>{{ $member->created_at->diffForHumans() }}</td><td><button class="table-action"><i class="bi bi-three-dots-vertical"></i></button></td></tr>@endforeach</tbody></table></div></article></div>
        <div class="col-xl-4"><article class="dashboard-card"><div class="card-heading"><div><span>QUICK TASKS</span><h2>Common actions</h2></div></div><div class="task-list">@foreach([['Review access rules','12 policies updated','bi-shield-check'],['Manage user types','Configure inheritance','bi-diagram-3'],['Audit portal modules','All systems ready','bi-grid']] as [$title,$copy,$icon])<a href="#"><span><i class="bi {{ $icon }}"></i></span><div><strong>{{ $title }}</strong><small>{{ $copy }}</small></div><i class="bi bi-chevron-right"></i></a>@endforeach</div></article></div>
    </div>
@endsection
