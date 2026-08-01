<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('profile_code');
            $table->timestamp('photo_updated_at')->nullable()->after('photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', fn (Blueprint $table) => $table->dropColumn(['photo_path', 'photo_updated_at']));
    }
};
