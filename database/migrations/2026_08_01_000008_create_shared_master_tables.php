<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['genders', 'marital_statuses', 'academic_classes', 'qualification_levels'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('code', 40)->unique();
                $table->string('short_name', 80)->nullable();
                $table->string('display_name', 150);
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['is_active', 'sort_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('qualification_levels');
        Schema::dropIfExists('academic_classes');
        Schema::dropIfExists('marital_statuses');
        Schema::dropIfExists('genders');
    }
};
