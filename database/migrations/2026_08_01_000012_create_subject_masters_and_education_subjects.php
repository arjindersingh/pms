<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
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
        Schema::create('candidate_education_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_education_id')->constrained('candidate_educations')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['candidate_education_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_education_subject');
        Schema::dropIfExists('subjects');
    }
};
