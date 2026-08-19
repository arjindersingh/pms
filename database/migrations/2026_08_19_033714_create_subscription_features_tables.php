<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('category', 30)->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 60)->default('bi-check-circle');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('plan_feature_subscription_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_feature_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['subscription_plan_id', 'plan_feature_id']);
        });

        $now = now();
        $features = [
            ['talent_directory', 'recruiter', 'Browse available talent', 'See public talent cards and basic career information.', 'bi-people', 10],
            ['full_profile', 'recruiter', 'Full talent profiles', 'Open education, experience, skills, and complete professional details.', 'bi-person-vcard', 20],
            ['contact_details', 'recruiter', 'Talent contact details', 'View permitted email, phone, WhatsApp, and professional links.', 'bi-person-lines-fill', 30],
            ['portal_messages', 'recruiter', 'Portal messaging', 'Send a private opportunity message inside the recruiter portal.', 'bi-chat-dots', 40],
            ['interview_invitations', 'recruiter', 'Interview invitations', 'Send a dated interview invitation with meeting details.', 'bi-calendar2-check', 50],
            ['receive_recruiter_communications', 'talent', 'Receive recruiter communications', 'Receive opportunity messages and interview invitations from recruiters.', 'bi-inbox', 10],
        ];

        foreach ($features as [$key, $category, $name, $description, $icon, $position]) {
            DB::table('plan_features')->insert(compact('key', 'category', 'name', 'description', 'icon', 'position') + ['created_at' => $now, 'updated_at' => $now]);
        }

        $featureIds = DB::table('plan_features')->pluck('id', 'key');
        foreach (DB::table('subscription_plans')->where('category', 'recruiter')->get() as $plan) {
            $keys = match ($plan->slug) {
                'free' => ['talent_directory'],
                'intermediate' => ['talent_directory', 'full_profile', 'portal_messages'],
                default => DB::table('plan_features')->where('category', 'recruiter')->pluck('key')->all(),
            };
            foreach ($keys as $key) {
                DB::table('plan_feature_subscription_plan')->insert(['subscription_plan_id' => $plan->id, 'plan_feature_id' => $featureIds[$key], 'created_at' => $now, 'updated_at' => $now]);
            }
        }

        $receiveCommunicationsId = $featureIds['receive_recruiter_communications'];
        foreach (DB::table('subscription_plans')->where('category', 'talent')->where('price', '>', 0)->pluck('id') as $planId) {
            DB::table('plan_feature_subscription_plan')->insert(['subscription_plan_id' => $planId, 'plan_feature_id' => $receiveCommunicationsId, 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('subscription_plans')->where('category', 'recruiter')->where('slug', 'free')->update(['description' => 'Browse available talent with privacy-safe summary profiles.']);
        DB::table('subscription_plans')->where('category', 'recruiter')->where('slug', 'intermediate')->update(['name' => 'Professional', 'description' => 'Full profiles and secure opportunity messaging in the portal.']);
        DB::table('subscription_plans')->where('category', 'recruiter')->where('slug', 'full')->update(['description' => 'Complete profiles, contact access, messaging, and interview invitations.']);
        DB::table('subscription_plans')->where('category', 'talent')->where('slug', 'free')->update(['description' => 'Create and publish your profile; recruiter communications are not delivered on the free plan.']);
        DB::table('subscription_plans')->where('category', 'talent')->where('price', '>', 0)->update(['description' => 'Publish your profile and receive recruiter messages and interview invitations.']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_feature_subscription_plan');
        Schema::dropIfExists('plan_features');
    }
};
