<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profile_talent', function (Blueprint $table) {
            $table->dropForeign(['talent_id']);
            $table->foreign('talent_id')->references('id')->on('talents')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profile_talent', function (Blueprint $table) {
            $table->dropForeign(['talent_id']);
            $table->foreign('talent_id')->references('id')->on('talent')->cascadeOnDelete();
        });
    }
};
