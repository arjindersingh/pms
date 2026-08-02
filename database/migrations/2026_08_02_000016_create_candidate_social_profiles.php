<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_platforms', function (Blueprint $table) {
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

        Schema::create('candidate_social_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('social_platform_id')->constrained()->restrictOnDelete();
            $table->string('custom_platform_name', 100)->nullable();
            $table->string('username', 150)->nullable();
            $table->string('profile_url', 500);
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();
            $table->unique(['candidate_profile_id', 'social_platform_id', 'profile_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_social_profiles');
        Schema::dropIfExists('social_platforms');
    }
};
