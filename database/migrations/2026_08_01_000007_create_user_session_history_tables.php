<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_session_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('session_hash', 64)->unique();
            $table->string('ip_address', 45)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('browser', 80)->nullable()->index();
            $table->string('browser_version', 40)->nullable();
            $table->string('operating_system', 80)->nullable()->index();
            $table->string('device_type', 30)->default('desktop')->index();
            $table->string('device_name', 100)->nullable();
            $table->string('locale', 20)->nullable();
            $table->string('login_method', 30)->default('password');
            $table->boolean('remembered')->default(false);
            $table->timestamp('logged_in_at')->index();
            $table->timestamp('last_seen_at')->index();
            $table->timestamp('logged_out_at')->nullable()->index();
            $table->unsignedBigInteger('duration_seconds')->default(0);
            $table->unsignedBigInteger('request_count')->default(0);
            $table->string('last_route')->nullable();
            $table->text('last_path')->nullable();
            $table->string('referrer_host')->nullable();
            $table->string('ended_reason', 30)->nullable();
            $table->timestamps();
        });

        Schema::create('user_session_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_session_history_id')->constrained()->cascadeOnDelete();
            $table->string('method', 10);
            $table->text('path');
            $table->string('route_name')->nullable()->index();
            $table->unsignedSmallInteger('response_status')->nullable()->index();
            $table->timestamp('occurred_at')->index();
            $table->index(['user_session_history_id', 'occurred_at']);
        });

        $moduleId = DB::table('portal_modules')->where('slug', 'administration')->value('id');
        $parentId = DB::table('portal_menus')->where('slug', 'user-management')->value('id');

        if ($moduleId && $parentId) {
            DB::table('portal_menus')->updateOrInsert(
                ['slug' => 'session-reports'],
                ['portal_module_id' => $moduleId, 'parent_id' => $parentId, 'name' => 'Session Reports', 'route_name' => 'admin.sessions.index', 'icon' => 'bi-pc-display-horizontal', 'position' => 30, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    public function down(): void
    {
        DB::table('portal_menus')->where('slug', 'session-reports')->delete();
        Schema::dropIfExists('user_session_activities');
        Schema::dropIfExists('user_session_histories');
    }
};
