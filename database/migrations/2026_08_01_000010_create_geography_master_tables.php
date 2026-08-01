<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->foreignId('country_id')->constrained()->cascadeOnDelete();
                $table->string('code', 40);
                $table->string('short_name', 80)->nullable();
                $table->string('display_name', 150);
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['country_id', 'code']);
            });
        }
        if (! Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained()->cascadeOnDelete();
                $table->string('code', 40);
                $table->string('short_name', 80)->nullable();
                $table->string('display_name', 150);
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['state_id', 'code']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('states');
    }
};
