<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('permissions_customized_at')->nullable()->after('last_reviewed_by');
            $table->foreignId('permissions_customized_by')->nullable()->after('permissions_customized_at')->constrained('users')->nullOnDelete();
        });
        Schema::create('permission_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_role_id')->nullable()->constrained('user_roles')->nullOnDelete();
            $table->string('event', 48)->index();
            $table->string('summary');
            $table->json('changes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_audits');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('permissions_customized_by');
            $table->dropColumn('permissions_customized_at');
        });
    }
};
