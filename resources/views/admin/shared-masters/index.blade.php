@extends('layouts.administrator')
@section('title', $selectedType['label'])
@section('content')
<div class="dashboard-heading shared-master-heading">
    <div><span class="dashboard-kicker">Shared masters / {{ Str::upper($selectedType['label']) }}</span><h1>{{ $selectedType['label'] }}</h1><p>Maintain consistent values used across forms, filters, and reports.</p></div>
    <div class="master-count"><i class="bi {{ $selectedType['icon'] }}"></i><strong>{{ $records->count() }}</strong><span>records</span></div>
</div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Please correct the highlighted fields.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@if(($selectedType['scoped_parent'] ?? false) && $parentOptions->isNotEmpty())
    <form method="GET" action="{{ route('admin.shared-masters.index') }}" class="dashboard-card master-filter-bar">
        <input type="hidden" name="type" value="{{ $selectedKey }}">
        <label for="master_parent_filter"><i class="bi bi-funnel"></i> Show {{ Str::lower($selectedType['label']) }} for {{ Str::lower($selectedType['parent']['label']) }}</label>
        <select class="form-select form-select-sm" id="master_parent_filter" name="parent">
            @foreach($parentOptions as $parent)
                <option value="{{ $parent->id }}" @selected($selectedParentId === $parent->id)>{{ isset($selectedType['parent']['context']) ? $parent->{$selectedType['parent']['context']}?->display_name.' — ' : '' }}{{ $parent->display_name }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-primary" type="submit">Apply</button>
    </form>
@endif

<section class="dashboard-card master-table-card">
    <div class="master-table-intro"><div><span>MASTER VALUES</span><strong>Add, edit, activate, or archive entries directly in the table.</strong></div><span class="master-save-hint"><i class="bi bi-check2-circle"></i> Save each row separately</span></div>
    <form id="new-master-record" method="POST" action="{{ route('admin.shared-masters.store', $selectedKey) }}">@csrf</form>
    <div class="table-responsive">
        <table class="table align-middle shared-master-table">
            <thead><tr><th class="master-order-column">Order</th>@if(isset($selectedType['parent']) && ! ($selectedType['scoped_parent'] ?? false))<th>{{ $selectedType['parent']['label'] }}</th>@endif<th>Code</th><th>Name</th><th>Short name</th><th>Description</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <tr class="master-add-row">
                    <td><input form="new-master-record" class="form-control form-control-sm" type="number" min="0" name="sort_order" value="{{ old('sort_order', ($records->max('sort_order') ?? 0) + 10) }}" required aria-label="Display order">@if(isset($selectedType['parent']) && ($selectedType['scoped_parent'] ?? false))<input form="new-master-record" type="hidden" name="{{ $selectedType['parent']['field'] }}" value="{{ $selectedParentId }}">@endif</td>
                    @if(isset($selectedType['parent']) && ! ($selectedType['scoped_parent'] ?? false))
                            <td><select form="new-master-record" class="form-select form-select-sm" name="{{ $selectedType['parent']['field'] }}" required><option value="">Select {{ Str::lower($selectedType['parent']['label']) }}</option>@foreach($parentOptions as $parent)<option value="{{ $parent->id }}" @selected(old($selectedType['parent']['field'])==$parent->id)>{{ $parent->display_name }}</option>@endforeach</select></td>
                    @endif
                    <td><input form="new-master-record" class="form-control form-control-sm font-monospace" name="code" value="{{ old('code') }}" placeholder="CODE" required></td>
                    <td><input form="new-master-record" class="form-control form-control-sm" name="display_name" value="{{ old('display_name') }}" placeholder="Display name" required></td>
                    <td><input form="new-master-record" class="form-control form-control-sm" name="short_name" value="{{ old('short_name') }}" placeholder="Optional"></td>
                    <td><input form="new-master-record" class="form-control form-control-sm" name="description" value="{{ old('description') }}" placeholder="Optional description"></td>
                    <td><div class="form-check form-switch master-status"><input form="new-master-record" type="hidden" name="is_active" value="0"><input form="new-master-record" class="form-check-input" type="checkbox" name="is_active" value="1" checked><span>Active</span></div></td>
                    <td class="text-end"><button form="new-master-record" class="btn btn-sm btn-portal master-action" type="submit" title="Add value"><i class="bi bi-plus-lg"></i><span>Add</span></button></td>
                </tr>
                @forelse($records as $record)
                    @php($recordFormId = 'master-record-'.$record->id)
                    <tr>
                        <td><input form="{{ $recordFormId }}" class="form-control form-control-sm" type="number" min="0" name="sort_order" value="{{ $record->sort_order }}" required aria-label="Sort order">@if(isset($selectedType['parent']) && ($selectedType['scoped_parent'] ?? false))<input form="{{ $recordFormId }}" type="hidden" name="{{ $selectedType['parent']['field'] }}" value="{{ $selectedParentId }}">@endif</td>
                        @if(isset($selectedType['parent']) && ! ($selectedType['scoped_parent'] ?? false))
                                <td><select form="{{ $recordFormId }}" class="form-select form-select-sm" name="{{ $selectedType['parent']['field'] }}" required>@foreach($parentOptions as $parent)<option value="{{ $parent->id }}" @selected($record->{$selectedType['parent']['field']}===$parent->id)>{{ $parent->display_name }}</option>@endforeach</select></td>
                        @endif
                        <td><input form="{{ $recordFormId }}" class="form-control form-control-sm font-monospace" name="code" value="{{ $record->code }}" required></td>
                        <td><input form="{{ $recordFormId }}" class="form-control form-control-sm" name="display_name" value="{{ $record->display_name }}" required></td>
                        <td><input form="{{ $recordFormId }}" class="form-control form-control-sm" name="short_name" value="{{ $record->short_name }}" placeholder="—"></td>
                        <td><input form="{{ $recordFormId }}" class="form-control form-control-sm" name="description" value="{{ $record->description }}" placeholder="—"></td>
                        <td><div class="form-check form-switch master-status"><input form="{{ $recordFormId }}" type="hidden" name="is_active" value="0"><input form="{{ $recordFormId }}" class="form-check-input" type="checkbox" name="is_active" value="1" @checked($record->is_active)><span>{{ $record->is_active ? 'Active' : 'Hidden' }}</span></div></td>
                        <td class="text-end master-row-actions"><form id="{{ $recordFormId }}" method="POST" action="{{ route('admin.shared-masters.update', [$selectedKey, $record]) }}">@csrf @method('PUT')</form><button form="{{ $recordFormId }}" class="btn btn-sm btn-outline-primary" type="submit" title="Save row"><i class="bi bi-check-lg"></i></button><form class="d-inline" method="POST" action="{{ route('admin.shared-masters.destroy', [$selectedKey, $record]) }}" onsubmit="return confirm('Archive this value?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit" title="Archive"><i class="bi bi-archive"></i></button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ isset($selectedType['parent']) && ! ($selectedType['scoped_parent'] ?? false) ? 8 : 7 }}" class="master-empty"><i class="bi bi-inbox"></i><span>No values have been added yet.</span></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
