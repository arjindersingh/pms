<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_types', function (Blueprint $table) {
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

        Schema::create('candidate_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_type_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->string('candidate_role', 150)->nullable();
            $table->string('organization_client', 200)->nullable();
            $table->unsignedSmallInteger('team_size')->nullable();
            $table->date('started_on')->nullable();
            $table->date('ended_on')->nullable();
            $table->boolean('currently_active')->default(false);
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('candidate_contribution')->nullable();
            $table->text('outcome')->nullable();
            $table->string('project_url', 500)->nullable();
            $table->string('repository_url', 500)->nullable();
            $table->string('demo_url', 500)->nullable();
            $table->json('screenshots')->nullable();
            $table->json('supporting_documents')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });

        Schema::create('candidate_project_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['candidate_project_id', 'skill_id']);
        });

        Schema::create('candidate_project_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('role', 150)->nullable();
            $table->string('organization', 200)->nullable();
            $table->string('profile_url', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_project_team_members');
        Schema::dropIfExists('candidate_project_skill');
        Schema::dropIfExists('candidate_projects');
        Schema::dropIfExists('project_types');
    }
};
