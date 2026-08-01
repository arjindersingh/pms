<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32)->index();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('user_role_id')->nullable()->after('user_type_id')->constrained('user_roles')->nullOnDelete();
        });

        Schema::create('portal_module_user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_module_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false);
            $table->timestamps();
            $table->unique(['user_role_id', 'portal_module_id']);
        });

        Schema::create('portal_menu_user_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_menu_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_view')->default(false); $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false); $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['user_role_id', 'portal_menu_id']);
        });

        Schema::create('portal_module_user', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_module_id')->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(false); $table->timestamps();
            $table->unique(['user_id', 'portal_module_id']);
        });

        Schema::create('portal_menu_user', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('portal_menu_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_view')->default(false); $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false); $table->boolean('can_delete')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'portal_menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_menu_user'); Schema::dropIfExists('portal_module_user');
        Schema::dropIfExists('portal_menu_user_role'); Schema::dropIfExists('portal_module_user_role');
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('user_role_id'));
        Schema::dropIfExists('user_roles');
    }
};
