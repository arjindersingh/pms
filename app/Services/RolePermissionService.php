<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserRole;
use App\Models\PermissionAudit;
use Illuminate\Support\Facades\DB;
use App\Enums\UserCategory;
use App\Models\SubscriptionPlan;

class RolePermissionService
{
    public function assign(User $user, UserRole $role, ?User $actor = null, bool $recordAudit = true): void
    {
        abort_unless($user->userType?->category === $role->category, 422, 'The role must belong to the same user category.');

        DB::transaction(function () use ($user, $role, $actor, $recordAudit): void {
            $user->update(['user_role_id' => $role->id, 'permissions_customized_at' => null, 'permissions_customized_by' => null]);
            DB::table('portal_module_user')->where('user_id', $user->id)->delete();
            DB::table('portal_menu_user')->where('user_id', $user->id)->delete();

            if (in_array($role->category, [UserCategory::Recruiter, UserCategory::Talent], true) && ! $user->subscriptions()->where('status', 'active')->exists()) {
                $plan = SubscriptionPlan::where('category', $role->category)->where('slug', 'free')->where('is_active', true)->first();
                if ($plan) $user->subscriptions()->create(['subscription_plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now(), 'price' => $plan->price, 'currency' => $plan->currency, 'billing_period' => $plan->billing_period]);
            }

            if ($role->is_super_admin) {
                if ($recordAudit) PermissionAudit::create(['actor_id'=>$actor?->id,'target_user_id'=>$user->id,'user_role_id'=>$role->id,'event'=>'role_assigned','summary'=>"{$role->name} assigned to {$user->name}",'changes'=>['snapshot'=>'unrestricted'],'ip_address'=>request()?->ip()]);
                return;
            }

            foreach (DB::table('portal_module_user_role')->where('user_role_id', $role->id)->get() as $permission) {
                DB::table('portal_module_user')->insert(['user_id' => $user->id, 'portal_module_id' => $permission->portal_module_id, 'enabled' => $permission->enabled, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach (DB::table('portal_menu_user_role')->where('user_role_id', $role->id)->get() as $permission) {
                DB::table('portal_menu_user')->insert(['user_id' => $user->id, 'portal_menu_id' => $permission->portal_menu_id, 'can_view' => $permission->can_view, 'can_create' => $permission->can_create, 'can_update' => $permission->can_update, 'can_delete' => $permission->can_delete, 'created_at' => now(), 'updated_at' => now()]);
            }
            if ($recordAudit) PermissionAudit::create(['actor_id'=>$actor?->id,'target_user_id'=>$user->id,'user_role_id'=>$role->id,'event'=>'role_assigned','summary'=>"{$role->name} assigned to {$user->name}",'changes'=>['snapshot'=>'reset_from_role'],'ip_address'=>request()?->ip()]);
        });
    }
}
