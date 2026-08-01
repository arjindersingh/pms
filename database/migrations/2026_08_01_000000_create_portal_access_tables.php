<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('user_types')->nullOnDelete();
            $table->string('category', 32)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_type_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true)->after('password');
        });

        Schema::create('portal_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('portal_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('portal_menus')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('route_name')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('portal_module_user_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
            $table->unique(['portal_module_id', 'user_type_id']);
        });

        Schema::create('portal_menu_user_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_type_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['portal_menu_id', 'user_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_menu_user_type');
        Schema::dropIfExists('portal_module_user_type');
        Schema::dropIfExists('portal_menus');
        Schema::dropIfExists('portal_modules');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_type_id');
            $table->dropColumn('is_active');
        });

        Schema::dropIfExists('user_types');
    }
};
