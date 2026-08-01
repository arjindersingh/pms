<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserCategory;
use App\Http\Controllers\Controller;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\User;
use App\Models\UserRole;
use App\Models\PermissionAudit;
use App\Services\RolePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', ['roles' => UserRole::withCount('users')->orderBy('category')->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(['name' => ['required','string','max:100'], 'category' => ['required', Rule::enum(UserCategory::class)], 'description' => ['nullable','string','max:500']]);
        $base = Str::slug($validated['category'].'-'.$validated['name']); $slug = $base; $suffix = 2;
        while (UserRole::where('slug', $slug)->exists()) $slug = $base.'-'.$suffix++;
        $role = UserRole::create($validated + ['slug' => $slug, 'is_active' => true, 'is_super_admin' => false]);
        PermissionAudit::create(['actor_id'=>$request->user()->id,'user_role_id'=>$role->id,'event'=>'role_created','summary'=>"{$role->name} role created",'changes'=>['category'=>$role->category->value],'ip_address'=>$request->ip()]);
        return redirect()->route('admin.roles.edit', $role)->with('status', 'Role created. Configure its quick-start permissions.');
    }

    public function edit(UserRole $role): View
    {
        return view('admin.roles.edit', ['role' => $role->load(['modules','menus']), 'modules' => PortalModule::with('menus')->orderBy('position')->get()]);
    }

    public function update(Request $request, UserRole $role): RedirectResponse
    {
        abort_if($role->is_super_admin, 422, 'Super Admin permissions cannot be restricted.');
        $validated = $request->validate(['name' => ['required','string','max:100'], 'description' => ['nullable','string','max:500'], 'is_active' => ['boolean'], 'modules' => ['array'], 'menus' => ['array']]);

        DB::transaction(function () use ($role, $request, $validated): void {
            $before = ['name'=>$role->name,'active'=>$role->is_active,'modules'=>$role->modules()->wherePivot('enabled',true)->count(),'menus'=>$role->menus()->wherePivot('can_view',true)->count()];
            $role->update(['name' => $validated['name'], 'description' => $validated['description'] ?? null, 'is_active' => $request->boolean('is_active')]);
            $role->modules()->sync(PortalModule::all()->mapWithKeys(fn ($module) => [$module->id => ['enabled' => $request->boolean("modules.{$module->id}")]])->all());
            $role->menus()->sync(PortalMenu::all()->mapWithKeys(fn ($menu) => [$menu->id => ['can_view' => $request->boolean("menus.{$menu->id}.view"), 'can_create' => $request->boolean("menus.{$menu->id}.create"), 'can_update' => $request->boolean("menus.{$menu->id}.update"), 'can_delete' => $request->boolean("menus.{$menu->id}.delete")]])->all());
            PermissionAudit::create(['actor_id'=>$request->user()->id,'user_role_id'=>$role->id,'event'=>'role_template_updated','summary'=>"{$role->name} permission template updated",'changes'=>['before'=>$before,'after'=>['name'=>$role->name,'active'=>$role->is_active,'modules'=>$request->collect('modules')->filter(fn($v)=>(bool)$v)->count(),'menus'=>$request->collect('menus')->filter(fn($v)=>collect($v)->contains(fn($ability)=>(bool)$ability))->count()]],'ip_address'=>$request->ip()]);
        });
        return back()->with('status', 'Role template saved. Existing user snapshots were left unchanged.');
    }

    public function assign(Request $request, int $user, RolePermissionService $permissions): RedirectResponse
    {
        $account = User::withTrashed()->with('userType')->findOrFail($user);
        abort_if($account->trashed(), 422, 'Restore the account before assigning a role.');
        $validated = $request->validate(['user_role_id' => ['required', Rule::exists('user_roles','id')->where('category', $account->userType->category->value)->where('is_active', true)]]);
        $role = UserRole::findOrFail($validated['user_role_id']);
        abort_if($role->is_super_admin && ! $request->user()->isSuperAdmin(), 403, 'Only a Super Admin can assign the Super Admin role.');
        $permissions->assign($account, $role, $request->user());
        $account->accountReviews()->create(['reviewed_by' => $request->user()->id, 'action' => 'role_assigned', 'reason' => "Assigned {$role->name}; individual permissions reset from its template.", 'metadata' => ['role_id' => $role->id]]);
        return back()->with('status', "{$role->name} assigned and its permission template copied to the account.");
    }

    public function editUserPermissions(int $user): View
    {
        $account = User::with('role')->findOrFail($user);
        abort_if($account->isSuperAdmin(), 422, 'Super Admin always has unrestricted permissions.');
        return view('admin.roles.user-permissions', ['account' => $account, 'modules' => PortalModule::with('menus')->orderBy('position')->get(), 'modulePermissions' => DB::table('portal_module_user')->where('user_id',$account->id)->pluck('enabled','portal_module_id'), 'menuPermissions' => DB::table('portal_menu_user')->where('user_id',$account->id)->get()->keyBy('portal_menu_id')]);
    }

    public function updateUserPermissions(Request $request, int $user): RedirectResponse
    {
        $account = User::with('role')->findOrFail($user);
        abort_if($account->isSuperAdmin(), 422, 'Super Admin always has unrestricted permissions.');
        abort_unless($account->user_role_id, 422, 'Assign a role before editing individual permissions.');

        DB::transaction(function () use ($request, $account): void {
            foreach (PortalModule::all() as $module) DB::table('portal_module_user')->updateOrInsert(['user_id'=>$account->id,'portal_module_id'=>$module->id], ['enabled'=>$request->boolean("modules.{$module->id}"),'created_at'=>now(),'updated_at'=>now()]);
            foreach (PortalMenu::all() as $menu) DB::table('portal_menu_user')->updateOrInsert(['user_id'=>$account->id,'portal_menu_id'=>$menu->id], ['can_view'=>$request->boolean("menus.{$menu->id}.view"),'can_create'=>$request->boolean("menus.{$menu->id}.create"),'can_update'=>$request->boolean("menus.{$menu->id}.update"),'can_delete'=>$request->boolean("menus.{$menu->id}.delete"),'created_at'=>now(),'updated_at'=>now()]);
            $account->update(['permissions_customized_at'=>now(),'permissions_customized_by'=>$request->user()->id]);
            $account->accountReviews()->create(['reviewed_by'=>$request->user()->id,'action'=>'permissions_updated','reason'=>'Individual permissions customized by administrator.']);
            PermissionAudit::create(['actor_id'=>$request->user()->id,'target_user_id'=>$account->id,'user_role_id'=>$account->user_role_id,'event'=>'individual_permissions_updated','summary'=>"Individual permissions updated for {$account->name}",'changes'=>['modules_enabled'=>$request->collect('modules')->filter(fn($v)=>(bool)$v)->count(),'menus_visible'=>$request->collect('menus')->filter(fn($v)=>collect($v)->contains(fn($ability)=>(bool)$ability))->count()],'ip_address'=>$request->ip()]);
        });
        return back()->with('status', 'Individual permissions saved.');
    }

    public function audit(Request $request): View
    {
        $query = PermissionAudit::with(['actor','targetUser','role'])->latest();
        $query->when($request->filled('event'), fn($q) => $q->where('event',$request->string('event')));
        $query->when($request->filled('search'), fn($q) => $q->where('summary','like','%'.$request->string('search').'%'));
        return view('admin.roles.audit', ['audits'=>$query->paginate(25)->withQueryString(), 'events'=>PermissionAudit::distinct()->orderBy('event')->pluck('event')]);
    }
}
