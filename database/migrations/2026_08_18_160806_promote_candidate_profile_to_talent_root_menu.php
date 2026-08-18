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
        DB::table('portal_menus')->where('slug', 'career-workspace')->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        foreach (['candidate-profile' => 20, 'job-preferences' => 30, 'opportunities' => 40, 'applications' => 50] as $slug => $position) {
            DB::table('portal_menus')->where('slug', $slug)->update([
                'parent_id' => null,
                'position' => $position,
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $workspaceId = DB::table('portal_menus')->where('slug', 'career-workspace')->value('id');

        DB::table('portal_menus')->where('slug', 'career-workspace')->update([
            'is_active' => true,
            'updated_at' => now(),
        ]);

        foreach (['candidate-profile' => 10, 'job-preferences' => 15, 'opportunities' => 10, 'applications' => 20] as $slug => $position) {
            DB::table('portal_menus')->where('slug', $slug)->update([
                'parent_id' => $workspaceId,
                'position' => $position,
                'updated_at' => now(),
            ]);
        }
    }
};
