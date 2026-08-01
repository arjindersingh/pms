<?php

namespace App\Http\Controllers;

use App\Models\PermissionAudit;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function administrator(): View
    {
        return view('dashboards.administrator', [
            'permissionStats' => [
                'roles' => UserRole::where('is_active', true)->count(),
                'assigned' => User::whereNotNull('user_role_id')->count(),
                'customized' => User::whereNotNull('permissions_customized_at')->count(),
                'attention' => User::where(fn($q) => $q->whereNull('user_role_id')->orWhereHas('role', fn($role) => $role->where('is_active', false)))->count(),
            ],
            'rolesByCategory' => UserRole::withCount('users')->orderBy('category')->orderBy('name')->get()->groupBy(fn($role) => $role->category->value),
            'recentAudits' => PermissionAudit::with(['actor','targetUser','role'])->latest()->take(7)->get(),
            'recentUsers' => User::with(['userType','role'])->latest()->take(5)->get(),
            'coverage' => ['modules' => PortalModule::count(), 'menus' => PortalMenu::count()],
        ]);
    }

    public function recruiter(): View
    {
        return view('dashboards.recruiter');
    }

    public function talent(): View
    {
        return view('dashboards.talent');
    }
}
