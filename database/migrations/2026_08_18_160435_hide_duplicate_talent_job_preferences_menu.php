<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('portal_menus')
            ->where('slug', 'job-preferences')
            ->update(['is_active' => true, 'updated_at' => now()]);
        DB::table('portal_menus')
            ->where('slug', 'candidate-profile-preferences')
            ->update(['is_active' => false, 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('portal_menus')
            ->where('slug', 'job-preferences')
            ->update(['is_active' => false, 'updated_at' => now()]);
        DB::table('portal_menus')
            ->where('slug', 'candidate-profile-preferences')
            ->update(['is_active' => true, 'updated_at' => now()]);
    }
};
