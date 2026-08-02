<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('short_name', 80)->nullable();
            $table->string('display_name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('candidate_profile_skill', function (Blueprint $table) {
            $table->foreignId('skill_category_id')->nullable()->after('skill_id')->constrained()->nullOnDelete();
            $table->text('remarks')->nullable()->after('is_primary');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profile_skill', function (Blueprint $table) {
            $table->dropConstrainedForeignId('skill_category_id');
            $table->dropColumn('remarks');
        });

        Schema::dropIfExists('skill_categories');
    }
};
