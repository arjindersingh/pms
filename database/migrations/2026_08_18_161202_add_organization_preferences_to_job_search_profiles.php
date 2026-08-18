<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_search_profile_organization_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_search_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_category_id')->constrained()->restrictOnDelete();
            $table->unique(['job_search_profile_id', 'organization_category_id'], 'job_search_profile_org_category_unique');
        });

        Schema::create('job_search_profile_organization_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_search_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_post_id')->constrained()->restrictOnDelete();
            $table->unique(['job_search_profile_id', 'organization_post_id'], 'job_search_profile_org_post_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_search_profile_organization_post');
        Schema::dropIfExists('job_search_profile_organization_category');
    }
};
