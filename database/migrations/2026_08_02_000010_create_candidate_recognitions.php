<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recognition_levels', function (Blueprint $table) {
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

        Schema::create('candidate_recognitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 30)->index();
            $table->string('title', 200);
            $table->string('award_type', 120)->nullable();
            $table->string('issuing_organization', 200)->nullable();
            $table->foreignId('recognition_level_id')->nullable()->constrained()->nullOnDelete();
            $table->date('awarded_on')->nullable();
            $table->string('rank_position', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('certificate_path', 500)->nullable();
            $table->string('verification_status', 30)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_recognitions');
        Schema::dropIfExists('recognition_levels');
    }
};
