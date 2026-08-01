<?php

namespace App\Livewire\Admin;

use App\Enums\UserCategory;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\User;
use App\Models\UserType;
use App\Services\PortalAccess;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AccessMatrix extends Component
{
    public int $selectedUserTypeId;

    /** @var array<int|string, bool> */
    public array $moduleAccess = [];

    /** @var array<int|string, array<string, bool>> */
    public array $menuPermissions = [];

    public function mount(): void
    {
        $this->authorizeManager();
        $this->selectedUserTypeId = UserType::query()->orderBy('category')->orderBy('name')->value('id');
        $this->loadAssignments();
    }

    public function updatedSelectedUserTypeId(): void
    {
        $this->authorizeManager();
        $this->loadAssignments();
    }

    public function save(): void
    {
        $this->authorizeManager();
        $type = UserType::query()->findOrFail($this->selectedUserTypeId);

        DB::transaction(function () use ($type): void {
            foreach (PortalModule::query()->get() as $module) {
                DB::table('portal_module_user_type')->updateOrInsert(
                    ['portal_module_id' => $module->id, 'user_type_id' => $type->id],
                    ['enabled' => (bool) ($this->moduleAccess[$module->id] ?? false), 'updated_at' => now(), 'created_at' => now()],
                );
            }

            foreach (PortalMenu::query()->get() as $menu) {
                $values = $this->menuPermissions[$menu->id] ?? [];
                DB::table('portal_menu_user_type')->updateOrInsert(
                    ['portal_menu_id' => $menu->id, 'user_type_id' => $type->id],
                    [
                        'can_view' => (bool) ($values['view'] ?? false),
                        'can_create' => (bool) ($values['create'] ?? false),
                        'can_update' => (bool) ($values['update'] ?? false),
                        'can_delete' => (bool) ($values['delete'] ?? false),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ],
                );
            }
        });

        session()->flash('status', "Permissions saved for {$type->name}.");
    }

    public function render()
    {
        return view('livewire.admin.access-matrix', [
            'userTypes' => UserType::query()->with('parent')->orderBy('category')->orderBy('name')->get(),
            'modules' => PortalModule::query()->with('menus')->orderBy('position')->get(),
            'selectedType' => UserType::query()->with('parent')->findOrFail($this->selectedUserTypeId),
        ]);
    }

    private function loadAssignments(): void
    {
        $type = UserType::query()->findOrFail($this->selectedUserTypeId);
        $access = app(PortalAccess::class);
        $probe = $type->users()->first() ?? new User(['is_active' => true]);
        $probe->setRelation('userType', $type);

        $this->moduleAccess = PortalModule::query()->get()->mapWithKeys(
            fn (PortalModule $module) => [$module->id => $access->module($probe, $module)],
        )->all();

        $this->menuPermissions = PortalMenu::query()->get()->mapWithKeys(function (PortalMenu $menu) use ($access, $probe) {
            return [$menu->id => [
                'view' => $access->menu($probe, $menu, 'view'),
                'create' => $access->menu($probe, $menu, 'create'),
                'update' => $access->menu($probe, $menu, 'update'),
                'delete' => $access->menu($probe, $menu, 'delete'),
            ]];
        })->all();
    }

    private function authorizeManager(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->userType?->category === UserCategory::Administrator
            && app(PortalAccess::class)->menu($user, 'module-access', 'update'),
            403,
        );
    }
}
