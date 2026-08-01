<div>
    @if(session('status'))
        <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <label class="form-label fw-semibold" for="user-type">User type or subtype</label>
            <select class="form-select" id="user-type" wire:model.live="selectedUserTypeId">
                @foreach($userTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->category->label() }} · {{ $type->name }}{{ $type->parent ? ' (inherits '.$type->parent->name.')' : '' }}</option>
                @endforeach
            </select>
            @if($selectedType->parent)
                <div class="form-text">Current values include permissions inherited from {{ $selectedType->parent->name }}. Saving creates explicit overrides for this subtype.</div>
            @endif
        </div>
    </div>

    <form wire:submit="save">
        @foreach($modules as $module)
            <div class="card border-0 shadow-sm mb-4" wire:key="module-{{ $module->id }}">
                <div class="card-header bg-white d-flex align-items-center justify-content-between py-3">
                    <span class="fw-semibold"><i class="bi {{ $module->icon }} me-2"></i>{{ $module->name }}</span>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="module-{{ $module->id }}" wire:model="moduleAccess.{{ $module->id }}">
                        <label class="form-check-label" for="module-{{ $module->id }}">Module access</label>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light"><tr><th>Menu</th><th class="text-center">View</th><th class="text-center">Create</th><th class="text-center">Update</th><th class="text-center">Delete</th></tr></thead>
                        <tbody>
                            @foreach($module->menus as $menu)
                                <tr wire:key="menu-{{ $menu->id }}">
                                    <td><i class="bi {{ $menu->icon }} me-2 text-secondary"></i>{{ $menu->name }}</td>
                                    @foreach(['view', 'create', 'update', 'delete'] as $ability)
                                        <td class="text-center"><input class="form-check-input" type="checkbox" aria-label="{{ ucfirst($ability) }} {{ $menu->name }}" wire:model="menuPermissions.{{ $menu->id }}.{{ $ability }}"></td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
        <div class="d-flex justify-content-end">
            <button class="btn btn-primary px-4" type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove><i class="bi bi-save me-2"></i>Save permissions</span>
                <span wire:loading><span class="spinner-border spinner-border-sm me-2"></span>Saving…</span>
            </button>
        </div>
    </form>
</div>
