<?php

namespace Tests\Feature;

use App\Models\PaymentGateway;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TalentSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_talent_can_view_subscription_details_in_the_top_account_menu(): void
    {
        $talent = User::where('email', 'talent@example.com')->firstOrFail();

        $this->actingAs($talent)->get(route('talent.dashboard'))
            ->assertOk()->assertSee('Subscription & billing', false)->assertSee(route('talent.subscription.show'), false)
            ->assertDontSee('sidebar-label">Subscription</span>', false);
        $this->get(route('talent.subscription.show'))
            ->assertOk()->assertSeeText('Subscription & billing')->assertSee('Current plan')->assertSee('Free')->assertSee('Renew plan');
    }

    public function test_recruiter_can_view_subscription_details_in_the_top_account_menu(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();

        $this->actingAs($recruiter)->get(route('recruiter.dashboard'))
            ->assertOk()
            ->assertSee('Subscription & billing', false)
            ->assertSee(route('recruiter.subscription.show'), false);

        $this->get(route('recruiter.subscription.show'))
            ->assertOk()
            ->assertSeeText('Subscription & billing')
            ->assertSee('Current plan')
            ->assertSee(route('recruiter.subscription.renew'), false);
    }

    public function test_recruiter_cannot_renew_a_talent_plan(): void
    {
        $recruiter = User::where('email', 'recruiter@example.com')->firstOrFail();
        $talentPlan = SubscriptionPlan::where('category', 'talent')->firstOrFail();

        $this->actingAs($recruiter)->post(route('recruiter.subscription.renew'), [
            'subscription_plan_id' => $talentPlan->id,
        ])->assertNotFound();
    }

    public function test_free_plan_renewal_activates_immediately_and_is_audited(): void
    {
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $plan = SubscriptionPlan::where('category', 'talent')->where('slug', 'free')->firstOrFail();

        $this->actingAs($talent)->post(route('talent.subscription.renew'), ['subscription_plan_id' => $plan->id])
            ->assertRedirect()->assertSessionHas('status', 'Your free subscription is active.');

        $this->assertSame(1, $talent->subscriptions()->where('status', 'active')->count());
        $subscription = $talent->activeSubscription()->firstOrFail();
        $this->assertSame($plan->id, $subscription->subscription_plan_id);
        $this->assertNull($subscription->ends_at);
        $this->assertSame('na', $subscription->billing_period);
        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $talent->id,
            'user_subscription_id' => $subscription->id,
            'subscription_plan_id' => $plan->id,
            'status' => 'completed',
            'total' => 0,
        ]);
    }

    public function test_paid_renewal_creates_pending_transaction_without_granting_access(): void
    {
        $talent = User::where('email', 'talent@example.com')->firstOrFail();
        $plan = SubscriptionPlan::where('category', 'talent')->where('slug', 'intermediate')->firstOrFail();
        $gateway = PaymentGateway::where('provider', 'bank_transfer')->firstOrFail();
        $gateway->update(['is_enabled' => true, 'currencies' => [$plan->currency], 'percentage_fee' => 2, 'fixed_fee' => 1]);
        $method = $gateway->methods()->where('code', 'bank_transfer')->firstOrFail();

        $this->actingAs($talent)->post(route('talent.subscription.renew'), [
            'subscription_plan_id' => $plan->id,
            'payment_method_id' => $method->id,
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('free', $talent->activeSubscription()->firstOrFail()->plan->slug);
        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $talent->id,
            'subscription_plan_id' => $plan->id,
            'payment_gateway_id' => $gateway->id,
            'payment_method' => 'bank_transfer',
            'status' => 'pending',
            'subtotal' => $plan->price,
            'fee' => 1.38,
            'total' => 20.38,
        ]);
    }
}
