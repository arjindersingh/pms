<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['talent_categories', 'talents'] as $name) {
            Schema::create($name, function (Blueprint $table) {
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
        }

        Schema::create('candidate_profile_talent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_id')->constrained('talents')->cascadeOnDelete();
            $table->foreignId('talent_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('proficiency_level_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('years_practised', 4, 1)->nullable();
            $table->text('achievements')->nullable();
            $table->string('evidence_url', 500)->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            $table->unique(['candidate_profile_id', 'talent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_profile_talent');
        Schema::dropIfExists('talents');
        Schema::dropIfExists('talent_categories');
    }
};
