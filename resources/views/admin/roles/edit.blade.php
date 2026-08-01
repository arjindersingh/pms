@extends('layouts.administrator')
@section('title', 'Edit '.$role->name)
@section('content')
<a class="account-back" href="{{ route('admin.roles.index') }}"><i class="bi bi-arrow-left"></i> Back to roles</a>
<div class="dashboard-heading mt-3"><div><span class="dashboard-kicker">{{ strtoupper($role->category->label()) }} ROLE</span><h1>{{ $role->name }}</h1><p>Configure the permission template copied to users when this role is assigned.</p></div></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<form method="POST" action="{{ route('admin.roles.update', $role) }}">@csrf @method('PUT')
    <section class="dashboard-card mb-4"><div class="row g-3"><div class="col-md-4"><label class="form-label">Role name</label><input class="form-control" name="name" value="{{ old('name',$role->name) }}" required></div><div class="col-md-6"><label class="form-label">Description</label><input class="form-control" name="description" value="{{ old('description',$role->description) }}"></div><div class="col-md-2 d-flex align-items-end"><label class="form-check form-switch mb-2"><input type="hidden" name="is_active" value="0"><input class="form-check-input" name="is_active" value="1" type="checkbox" @checked($role->is_active)><span>Active</span></label></div></div></section>
    @foreach($modules as $module)
        @php($roleModule = $role->modules->firstWhere('id',$module->id))
        <section class="dashboard-card permission-matrix mb-4"><div class="permission-module-head"><div><i class="bi {{ $module->icon }}"></i><strong>{{ $module->name }}</strong></div><label class="form-check form-switch"><input type="hidden" name="modules[{{ $module->id }}]" value="0"><input class="form-check-input" type="checkbox" name="modules[{{ $module->id }}]" value="1" @checked($roleModule?->pivot->enabled)><span>Module access</span></label></div><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Menu</th>@foreach(['View','Create','Update','Delete'] as $ability)<th class="text-center">{{ $ability }}</th>@endforeach</tr></thead><tbody>@foreach($module->menus as $menu)@php($permission = $role->menus->firstWhere('id',$menu->id)?->pivot)<tr><td><i class="bi {{ $menu->icon }} me-2"></i>{{ $menu->name }}</td>@foreach(['view','create','update','delete'] as $ability)<td class="text-center"><input type="hidden" name="menus[{{ $menu->id }}][{{ $ability }}]" value="0"><input class="form-check-input" type="checkbox" name="menus[{{ $menu->id }}][{{ $ability }}]" value="1" @checked($permission?->{'can_'.$ability})></td>@endforeach</tr>@endforeach</tbody></table></div></section>
    @endforeach
    <div class="permission-savebar"><span><i class="bi bi-info-circle"></i>Changes apply to future assignments; existing user permissions remain unchanged.</span><button class="btn btn-portal" type="submit"><i class="bi bi-save"></i>Save role template</button></div>
</form>
@endsection
