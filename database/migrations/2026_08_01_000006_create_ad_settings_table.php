<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->boolean('auto_ads_enabled')->default(false);
            $table->boolean('show_placeholders')->default(true);
            $table->string('publisher_id', 32)->nullable();
            $table->boolean('homepage_top_enabled')->default(true);
            $table->string('homepage_top_slot', 32)->nullable();
            $table->boolean('homepage_middle_enabled')->default(true);
            $table->string('homepage_middle_slot', 32)->nullable();
            $table->boolean('homepage_bottom_enabled')->default(true);
            $table->string('homepage_bottom_slot', 32)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        $moduleId = DB::table('portal_modules')->where('slug', 'administration')->value('id');

        if ($moduleId) {
            DB::table('portal_menus')->updateOrInsert(
                ['slug' => 'monetization'],
                ['portal_module_id' => $moduleId, 'parent_id' => null, 'name' => 'Monetization', 'route_name' => null, 'icon' => 'bi-cash-coin', 'position' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            );

            $parentId = DB::table('portal_menus')->where('slug', 'monetization')->value('id');
            DB::table('portal_menus')->updateOrInsert(
                ['slug' => 'google-ads'],
                ['portal_module_id' => $moduleId, 'parent_id' => $parentId, 'name' => 'Google Ads', 'route_name' => 'admin.ads.edit', 'icon' => 'bi-badge-ad', 'position' => 10, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('portal_menus')->whereIn('slug', ['google-ads', 'monetization'])->delete();
        Schema::dropIfExists('ad_settings');
    }
};
