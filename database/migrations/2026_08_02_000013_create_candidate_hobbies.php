<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['hobby_categories', 'interest_levels', 'hobbies'] as $name) {
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

        Schema::create('candidate_profile_hobby', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hobby_id')->constrained()->cascadeOnDelete();
            $table->foreignId('hobby_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('interest_level_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('years_active', 4, 1)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['candidate_profile_id', 'hobby_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_profile_hobby');
        Schema::dropIfExists('hobbies');
        Schema::dropIfExists('interest_levels');
        Schema::dropIfExists('hobby_categories');
    }
};
