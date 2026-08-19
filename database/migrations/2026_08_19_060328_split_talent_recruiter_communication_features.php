<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('plan_features')
            ->where('key', 'receive_recruiter_communications')
            ->update([
                'key' => 'receive_portal_messages',
                'name' => 'Receive recruiter messages',
                'description' => 'Receive private opportunity messages from recruiters in the portal.',
                'icon' => 'bi-chat-dots',
                'updated_at' => $now,
            ]);

        $interviewInvitationId = DB::table('plan_features')->insertGetId([
            'key' => 'receive_interview_invitations',
            'category' => 'talent',
            'name' => 'Receive interview invitations',
            'description' => 'Receive dated interview invitations with meeting details from recruiters.',
            'icon' => 'bi-calendar2-check',
            'position' => 20,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $messageFeatureId = DB::table('plan_features')->where('key', 'receive_portal_messages')->value('id');
        $planIds = DB::table('plan_feature_subscription_plan')
            ->where('plan_feature_id', $messageFeatureId)
            ->pluck('subscription_plan_id');

        foreach ($planIds as $planId) {
            DB::table('plan_feature_subscription_plan')->insert([
                'subscription_plan_id' => $planId,
                'plan_feature_id' => $interviewInvitationId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $interviewInvitationId = DB::table('plan_features')->where('key', 'receive_interview_invitations')->value('id');

        if ($interviewInvitationId !== null) {
            DB::table('plan_feature_subscription_plan')->where('plan_feature_id', $interviewInvitationId)->delete();
            DB::table('plan_features')->where('id', $interviewInvitationId)->delete();
        }

        DB::table('plan_features')
            ->where('key', 'receive_portal_messages')
            ->update([
                'key' => 'receive_recruiter_communications',
                'name' => 'Receive recruiter communications',
                'description' => 'Receive opportunity messages and interview invitations from recruiters.',
                'icon' => 'bi-inbox',
                'updated_at' => now(),
            ]);
    }
};
