<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menuIds = DB::table('portal_menus')->whereIn('slug', ['hiring-workspace', 'jobs'])->pluck('id');
        $planIds = DB::table('subscription_plans')->where('category', 'recruiter')->where('is_active', true)->pluck('id');
        foreach ($planIds as $planId) {
            foreach ($menuIds as $menuId) {
                DB::table('portal_menu_subscription_plan')->updateOrInsert(
                    ['subscription_plan_id' => $planId, 'portal_menu_id' => $menuId],
                    ['can_view' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'updated_at' => now(), 'created_at' => now()],
                );
            }
        }
    }

    public function down(): void {}
};
