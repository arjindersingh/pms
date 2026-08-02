<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('skill_categories', 'skill_groups');

        Schema::table('candidate_profile_skill', function (Blueprint $table) {
            $table->renameColumn('skill_category_id', 'skill_group_id');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profile_skill', function (Blueprint $table) {
            $table->renameColumn('skill_group_id', 'skill_category_id');
        });

        Schema::rename('skill_groups', 'skill_categories');
    }
};
