<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status', 32)->default('active')->after('is_active')->index();
            $table->text('status_reason')->nullable()->after('account_status');
            $table->timestamp('status_changed_at')->nullable()->after('status_reason');
            $table->foreignId('status_changed_by')->nullable()->after('status_changed_at')->constrained('users')->nullOnDelete();
            $table->timestamp('last_reviewed_at')->nullable()->after('status_changed_by');
            $table->foreignId('last_reviewed_by')->nullable()->after('last_reviewed_at')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });

        DB::table('users')->where('is_active', false)->update(['account_status' => 'suspended']);

        Schema::create('user_account_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 32);
            $table->string('from_status', 32)->nullable();
            $table->string('to_status', 32)->nullable();
            $table->text('reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_account_reviews');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('status_changed_by');
            $table->dropConstrainedForeignId('last_reviewed_by');
            $table->dropColumn(['account_status', 'status_reason', 'status_changed_at', 'last_reviewed_at', 'deleted_at']);
        });
    }
};
