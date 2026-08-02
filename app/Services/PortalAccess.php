<?php

namespace App\Services;

use App\Enums\UserCategory;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PortalAccess
{
    public function module(User $user, PortalModule|string $module): bool
    {
        $module = is_string($module)
            ? PortalModule::query()->where('slug', $module)->first()
            : $module;

        if (! $module?->is_active || ! $user->is_active || ! $user->userType?->is_active || ($user->role && ! $user->role->is_active)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->user_role_id) {
            $assignment = DB::table('portal_module_user_role')->where('portal_module_id', $module->id)->where('user_role_id', $user->user_role_id)->first();

            return (bool) ($assignment?->enabled ?? false);
        }

        foreach ($user->userType->lineage() as $type) {
            $assignment = DB::table('portal_module_user_type')
                ->where('portal_module_id', $module->id)
                ->where('user_type_id', $type->id)
                ->first();

            if ($assignment) {
                return (bool) $assignment->enabled;
            }
        }

        return false;
    }

    public function menu(User $user, PortalMenu|string $menu, string $ability = 'view'): bool
    {
        $menu = is_string($menu)
            ? PortalMenu::query()->with('module')->where('slug', $menu)->first()
            : $menu->loadMissing('module');

        $column = match ($ability) {
            'view' => 'can_view',
            'create' => 'can_create',
            'update' => 'can_update',
            'delete' => 'can_delete',
            default => null,
        };

        if (! $column || ! $menu?->is_active || ! $this->module($user, $menu->module)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (in_array($user->userType?->category, [UserCategory::Recruiter, UserCategory::Talent], true)) {
            $subscription = $user->activeSubscription()->with('plan')->first();
            if (! $subscription?->plan?->is_active || $subscription->plan->category !== $user->userType->category) {
                return false;
            }
            $assignment = DB::table('portal_menu_subscription_plan')->where('portal_menu_id', $menu->id)->where('subscription_plan_id', $subscription->subscription_plan_id)->first();

            return (bool) ($assignment?->{$column} ?? false);
        }

        if ($user->user_role_id) {
            $assignment = DB::table('portal_menu_user_role')->where('portal_menu_id', $menu->id)->where('user_role_id', $user->user_role_id)->first();

            return (bool) ($assignment?->{$column} ?? false);
        }

        foreach ($user->userType->lineage() as $type) {
            $assignment = DB::table('portal_menu_user_type')
                ->where('portal_menu_id', $menu->id)
                ->where('user_type_id', $type->id)
                ->first();

            if ($assignment) {
                return (bool) $assignment->{$column};
            }
        }

        return false;
    }

    /** @return Collection<int, PortalModule> */
    public function navigation(User $user): Collection
    {
        return PortalModule::query()
            ->where('is_active', true)
            ->with([
                'menus' => fn ($query) => $query->where('is_active', true)->orderBy('position'),
                'menus.parent',
            ])
            ->orderBy('position')
            ->get()
            ->filter(fn (PortalModule $module) => $this->module($user, $module))
            ->map(function (PortalModule $module) use ($user) {
                $module->setRelation(
                    'menus',
                    $module->menus->filter(fn (PortalMenu $menu) => $this->menu($user, $menu))->values(),
                );

                return $module;
            })
            ->filter(fn (PortalModule $module) => $module->menus->isNotEmpty())
            ->values();
    }
}
