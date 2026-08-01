<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('degrees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qualification_level_id')->constrained()->restrictOnDelete();
            $table->string('code', 40)->unique();
            $table->string('short_name', 80)->nullable();
            $table->string('display_name', 150);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['qualification_level_id', 'is_active', 'sort_order']);
        });
        Schema::table('candidate_educations', fn (Blueprint $table) => $table->foreignId('degree_id')->nullable()->after('qualification_level_id')->constrained()->nullOnDelete());
    }

    public function down(): void
    {
        Schema::table('candidate_educations', fn (Blueprint $table) => $table->dropConstrainedForeignId('degree_id'));
        Schema::dropIfExists('degrees');
    }
};
