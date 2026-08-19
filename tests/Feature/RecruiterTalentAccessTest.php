<?php

namespace Tests\Feature;

use App\Models\PlanFeature;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecruiterTalentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_free_recruiter_can_browse_summaries_but_cannot_open_full_profiles(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $talent->candidateProfile()->create(['profile_code' => 'PUBLIC-001', 'is_public' => true, 'headline' => 'Laravel developer']);

        $this->actingAs($recruiter)->get(route('recruiter.talent.index'))->assertOk()->assertSee('Laravel developer')->assertSee('Summary only');
        $this->get(route('recruiter.talent.show', $profile))->assertForbidden();
    }

    public function test_full_plan_can_view_contact_and_send_interview_invitation(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $talent->candidateProfile()->create(['profile_code' => 'PUBLIC-002', 'is_public' => true, 'mobile' => '555-0100']);
        $plan = SubscriptionPlan::where('category', 'recruiter')->where('slug', 'full')->firstOrFail();
        $recruiter->subscriptions()->where('status', 'active')->update(['status' => 'replaced', 'ends_at' => now()]);
        $recruiter->subscriptions()->create(['subscription_plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now(), 'price' => $plan->price, 'currency' => $plan->currency, 'billing_period' => $plan->billing_period]);
        $talentPlan = SubscriptionPlan::where('category', 'talent')->where('slug', 'intermediate')->firstOrFail();
        $talent->subscriptions()->where('status', 'active')->update(['status' => 'replaced', 'ends_at' => now()]);
        $talent->subscriptions()->create(['subscription_plan_id' => $talentPlan->id, 'status' => 'active', 'starts_at' => now(), 'price' => $talentPlan->price, 'currency' => $talentPlan->currency, 'billing_period' => $talentPlan->billing_period]);

        $this->actingAs($recruiter)->get(route('recruiter.talent.show', $profile))->assertOk()->assertSee('555-0100');
        $this->post(route('recruiter.talent.contact', $profile), ['type' => 'interview', 'subject' => 'Technical interview', 'message' => 'We would like to meet you.', 'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'), 'meeting_location' => 'https://meet.example.test/room'])->assertRedirect();

        $this->assertDatabaseHas('recruiter_candidate_communications', ['recruiter_id' => $recruiter->id, 'candidate_id' => $profile->user_id, 'type' => 'interview']);
    }

    public function test_free_talent_plan_blocks_recruiter_communications(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $profile = $talent->candidateProfile()->create(['profile_code' => 'PUBLIC-003', 'is_public' => true]);
        $fullPlan = SubscriptionPlan::where('category', 'recruiter')->where('slug', 'full')->firstOrFail();
        $recruiter->subscriptions()->where('status', 'active')->update(['status' => 'replaced', 'ends_at' => now()]);
        $recruiter->subscriptions()->create(['subscription_plan_id' => $fullPlan->id, 'status' => 'active', 'starts_at' => now(), 'price' => $fullPlan->price, 'currency' => $fullPlan->currency, 'billing_period' => $fullPlan->billing_period]);

        $this->actingAs($recruiter)->post(route('recruiter.talent.contact', $profile), [
            'type' => 'message', 'subject' => 'Opportunity', 'message' => 'We would like to connect.',
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('recruiter_candidate_communications', ['candidate_id' => $talent->id]);
    }

    public function test_admin_can_move_a_service_to_another_plan(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $free = SubscriptionPlan::where('category', 'recruiter')->where('slug', 'free')->firstOrFail();
        $contact = PlanFeature::where('key', 'contact_details')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.subscription-plans.update', $free), ['name' => $free->name, 'description' => $free->description, 'price' => $free->price, 'currency' => $free->currency, 'billing_period' => $free->billing_period, 'position' => $free->position, 'is_active' => 1, 'features' => [$contact->id]])->assertRedirect();

        $this->assertTrue($free->fresh()->hasFeature('contact_details'));
        $this->assertFalse($free->fresh()->hasFeature('talent_directory'));
    }

    public function test_admin_can_manage_talent_services_independently(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $free = SubscriptionPlan::where('category', 'talent')->where('slug', 'free')->firstOrFail();
        $messages = PlanFeature::where('key', 'receive_portal_messages')->firstOrFail();
        $invitations = PlanFeature::where('key', 'receive_interview_invitations')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.subscription-plans.edit', $free))
            ->assertOk()
            ->assertSee('Receive recruiter messages')
            ->assertSee('Receive interview invitations');

        $this->actingAs($admin)->put(route('admin.subscription-plans.update', $free), ['name' => $free->name, 'description' => $free->description, 'price' => $free->price, 'currency' => $free->currency, 'billing_period' => $free->billing_period, 'position' => $free->position, 'is_active' => 1, 'features' => [$messages->id]])->assertRedirect();

        $this->assertTrue($free->fresh()->hasFeature('receive_portal_messages'));
        $this->assertFalse($free->fresh()->hasFeature('receive_interview_invitations'));
        $this->assertNotSame($messages->id, $invitations->id);
    }
}
