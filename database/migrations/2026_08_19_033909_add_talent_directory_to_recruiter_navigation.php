<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $moduleId = DB::table('portal_modules')->where('slug', 'recruitment')->value('id');
        $parentId = DB::table('portal_menus')->where('slug', 'hiring-workspace')->value('id');
        if (! $moduleId || DB::table('portal_menus')->where('slug', 'talent-directory')->exists()) {
            return;
        }

        $now = now();
        $menuId = DB::table('portal_menus')->insertGetId([
            'portal_module_id' => $moduleId, 'parent_id' => $parentId, 'name' => 'Available Talent',
            'slug' => 'talent-directory', 'route_name' => 'recruiter.talent.index', 'icon' => 'bi-people',
            'position' => 16, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach (DB::table('subscription_plans')->where('category', 'recruiter')->pluck('id') as $planId) {
            DB::table('portal_menu_subscription_plan')->insert([
                'subscription_plan_id' => $planId, 'portal_menu_id' => $menuId, 'can_view' => true,
                'can_create' => false, 'can_update' => false, 'can_delete' => false,
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('portal_menus')->where('slug', 'talent-directory')->delete();
    }
};
