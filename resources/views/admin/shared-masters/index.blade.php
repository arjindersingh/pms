@extends('layouts.administrator')
@section('title', 'Shared Masters')
@section('content')
<div class="dashboard-heading"><div><span class="dashboard-kicker">SHARED DATA</span><h1>Shared masters</h1><p>Maintain consistent choices for forms, filters, and reports from one place.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Please correct the highlighted fields.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="shared-master-tabs" role="navigation" aria-label="Master type">
    @foreach($types as $key => $type)
        <a href="{{ route('admin.shared-masters.index', ['type' => $key]) }}" class="{{ $selectedKey === $key ? 'active' : '' }}"><i class="bi {{ $type['icon'] }}"></i>{{ $type['label'] }}</a>
    @endforeach
</div>

<div class="shared-master-grid">
    <section class="dashboard-card">
        <div class="card-heading"><div><span>CURRENT VALUES</span><h2>{{ $selectedType['label'] }}</h2></div><span class="badge text-bg-light">{{ $records->count() }} records</span></div>
        <div class="table-responsive"><table class="table align-middle shared-master-table"><thead><tr><th>Order</th><th>Code & name</th><th>Description</th><th>Status</th><th></th></tr></thead><tbody>
        @forelse($records as $record)
            <tr><form method="POST" action="{{ route('admin.shared-masters.update', [$selectedKey, $record]) }}">@csrf @method('PUT')
                <td><input class="form-control form-control-sm" type="number" min="0" name="sort_order" value="{{ $record->sort_order }}" aria-label="Sort order"></td>
                <td><input class="form-control form-control-sm font-monospace mb-1" name="code" value="{{ $record->code }}" required><input class="form-control form-control-sm mb-1" name="display_name" value="{{ $record->display_name }}" required><input class="form-control form-control-sm" name="short_name" value="{{ $record->short_name }}" placeholder="Short name"></td>
                <td><textarea class="form-control form-control-sm" name="description" rows="3" placeholder="Optional description">{{ $record->description }}</textarea></td>
                <td><div class="form-check form-switch"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($record->is_active)><label class="form-check-label">Active</label></div></td>
                <td class="text-end"><button class="btn btn-sm btn-outline-primary" title="Save"><i class="bi bi-check-lg"></i></button></form><form class="d-inline" method="POST" action="{{ route('admin.shared-masters.destroy', [$selectedKey, $record]) }}" onsubmit="return confirm('Archive this value?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger mt-1" title="Archive"><i class="bi bi-archive"></i></button></form></td>
            </tr>
        @empty<tr><td colspan="5" class="text-center text-secondary py-5">No values have been added yet.</td></tr>@endforelse
        </tbody></table></div>
    </section>
    <aside class="dashboard-card">
        <div class="card-heading"><div><span>NEW VALUE</span><h2>Add to {{ Str::lower($selectedType['label']) }}</h2></div></div>
        <form method="POST" action="{{ route('admin.shared-masters.store', $selectedKey) }}">@csrf
            <label class="form-label" for="code">Code</label><input class="form-control font-monospace" id="code" name="code" value="{{ old('code') }}" placeholder="e.g. UG" required><div class="form-text">Uppercase letters, numbers, underscores, or hyphens.</div>
            <label class="form-label mt-3" for="display_name">Display name</label><input class="form-control" id="display_name" name="display_name" value="{{ old('display_name') }}" required>
            <label class="form-label mt-3" for="short_name">Short name</label><input class="form-control" id="short_name" name="short_name" value="{{ old('short_name') }}">
            <label class="form-label mt-3" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="3">{{ old('description') }}</textarea>
            <label class="form-label mt-3" for="sort_order">Display order</label><input class="form-control" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', ($records->max('sort_order') ?? 0) + 10) }}" required>
            <div class="form-check form-switch mt-3"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked><label class="form-check-label" for="is_active">Available in forms and reports</label></div>
            <button class="btn btn-portal w-100 mt-4" type="submit"><i class="bi bi-plus-lg"></i>Add value</button>
        </form>
    </aside>
</div>
@endsection
