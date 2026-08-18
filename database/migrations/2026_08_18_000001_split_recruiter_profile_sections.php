<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruiter_profiles', function (Blueprint $table) {
            $table->string('full_name', 150)->nullable()->after('user_id');
        });
        Schema::table('recruiter_organizations', function (Blueprint $table) {
            $table->string('hoi_name', 150)->nullable()->after('description');
            $table->string('hoi_designation', 120)->nullable()->after('hoi_name');
            $table->string('hoi_email')->nullable()->after('hoi_designation');
            $table->string('hoi_phone', 30)->nullable()->after('hoi_email');
        });

        $parent = DB::table('portal_menus')->where('slug', 'recruiter-profile')->first();
        if (! $parent) return;
        DB::table('portal_menus')->where('id', $parent->id)->update(['name' => 'Profile', 'route_name' => null, 'updated_at' => now()]);
        foreach ([
            ['Basic Detail', 'recruiter-profile-basic', 'recruiter.profile.basic', 'bi-person', 10],
            ['Contact Detail', 'recruiter-profile-contact', 'recruiter.profile.contact', 'bi-telephone', 20],
            ['Organisations', 'recruiter-profile-organizations', 'recruiter.profile.organizations', 'bi-buildings', 30],
        ] as [$name, $slug, $route, $icon, $position]) {
            $id = DB::table('portal_menus')->insertGetId(['portal_module_id' => $parent->portal_module_id, 'parent_id' => $parent->id, 'name' => $name, 'slug' => $slug, 'route_name' => $route, 'icon' => $icon, 'position' => $position, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            foreach (DB::table('portal_menu_user_role')->where('portal_menu_id', $parent->id)->get() as $permission) {
                DB::table('portal_menu_user_role')->insert(['user_role_id' => $permission->user_role_id, 'portal_menu_id' => $id, 'can_view' => $permission->can_view, 'can_create' => $permission->can_create, 'can_update' => $permission->can_update, 'can_delete' => $permission->can_delete, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach (DB::table('portal_menu_user_type')->where('portal_menu_id', $parent->id)->get() as $permission) {
                DB::table('portal_menu_user_type')->insert(['user_type_id' => $permission->user_type_id, 'portal_menu_id' => $id, 'can_view' => $permission->can_view, 'can_create' => $permission->can_create, 'can_update' => $permission->can_update, 'can_delete' => $permission->can_delete, 'created_at' => now(), 'updated_at' => now()]);
            }
            foreach (DB::table('portal_menu_subscription_plan')->where('portal_menu_id', $parent->id)->get() as $permission) {
                DB::table('portal_menu_subscription_plan')->insert(['subscription_plan_id' => $permission->subscription_plan_id, 'portal_menu_id' => $id, 'can_view' => $permission->can_view, 'can_create' => $permission->can_create, 'can_update' => $permission->can_update, 'can_delete' => $permission->can_delete, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        DB::table('portal_menus')->whereIn('slug', ['recruiter-profile-basic', 'recruiter-profile-contact', 'recruiter-profile-organizations'])->delete();
        DB::table('portal_menus')->where('slug', 'recruiter-profile')->update(['name' => 'Recruiter Profile', 'route_name' => 'recruiter.profile.edit', 'updated_at' => now()]);
        Schema::table('recruiter_organizations', fn (Blueprint $table) => $table->dropColumn(['hoi_name', 'hoi_designation', 'hoi_email', 'hoi_phone']));
        Schema::table('recruiter_profiles', fn (Blueprint $table) => $table->dropColumn('full_name'));
    }
};
