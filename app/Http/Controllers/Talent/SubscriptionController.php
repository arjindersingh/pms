<?php

namespace App\Http\Controllers\Talent;

use App\Enums\UserCategory;
use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Models\PaymentGatewayMethod;
use App\Models\PaymentTransaction;
use App\Models\SubscriptionPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $activeSubscription = $user->activeSubscription()->with('plan')->first();
        $plans = SubscriptionPlan::where('category', UserCategory::Talent)->where('is_active', true)->with(['menus' => fn ($query) => $query->wherePivot('can_view', true)])->orderBy('position')->get();
        $gateways = PaymentGateway::with(['methods' => fn ($query) => $query->where('is_enabled', true)])->where('is_enabled', true)->orderBy('position')->get();

        return view('talent.subscription.show', [
            'activeSubscription' => $activeSubscription,
            'plans' => $plans,
            'gateways' => $gateways,
            'history' => $user->subscriptions()->with('plan')->latest()->get(),
            'transactions' => PaymentTransaction::with(['gateway', 'plan'])->where('user_id', $user->id)->latest()->limit(20)->get(),
        ]);
    }

    public function renew(Request $request): RedirectResponse
    {
        $plan = SubscriptionPlan::where('category', UserCategory::Talent)->where('is_active', true)->findOrFail($request->integer('subscription_plan_id'));
        $paid = (float) $plan->price > 0;
        $data = $request->validate([
            'subscription_plan_id' => ['required', Rule::exists('subscription_plans', 'id')->where('category', UserCategory::Talent->value)->where('is_active', true)],
            'payment_method_id' => [$paid ? 'required' : 'nullable', 'integer'],
        ]);

        $gateway = null;
        $method = null;
        if ($paid) {
            $method = PaymentGatewayMethod::with('gateway')->where('is_enabled', true)->findOrFail($data['payment_method_id']);
            $gateway = $method->gateway;
            abort_unless($gateway?->is_enabled, 422, 'The selected payment gateway is unavailable.');
            abort_unless(in_array($plan->currency, $gateway->currencies ?? [], true), 422, 'This gateway does not support the plan currency.');
            abort_if($gateway->minimum_amount !== null && (float) $plan->price < (float) $gateway->minimum_amount, 422, 'The plan amount is below the gateway minimum.');
            abort_if($gateway->maximum_amount !== null && (float) $plan->price > (float) $gateway->maximum_amount, 422, 'The plan amount exceeds the gateway maximum.');
        }

        $subtotal = (float) $plan->price;
        $fee = $gateway ? round($subtotal * ((float) $gateway->percentage_fee / 100) + (float) $gateway->fixed_fee, 2) : 0;
        $transaction = DB::transaction(function () use ($request, $plan, $gateway, $method, $subtotal, $fee, $paid) {
            $transaction = PaymentTransaction::create([
                'reference' => (string) Str::uuid(),
                'user_id' => $request->user()->id,
                'subscription_plan_id' => $plan->id,
                'payment_gateway_id' => $gateway?->id,
                'payment_method' => $method?->code,
                'status' => $paid ? 'pending' : 'completed',
                'subtotal' => $subtotal,
                'fee' => $fee,
                'tax' => 0,
                'total' => $subtotal + $fee,
                'currency' => $plan->currency,
                'paid_at' => $paid ? null : now(),
                'metadata' => ['purpose' => 'subscription_renewal'],
            ]);

            if (! $paid) {
                $request->user()->subscriptions()->where('status', 'active')->update(['status' => 'replaced', 'ends_at' => now()]);
                $subscription = $request->user()->subscriptions()->create([
                    'subscription_plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => $this->endsAt($plan->billing_period),
                    'price' => $plan->price,
                    'currency' => $plan->currency,
                    'billing_period' => $plan->billing_period,
                    'note' => 'Self-service renewal',
                ]);
                $transaction->update(['user_subscription_id' => $subscription->id]);
            }

            return $transaction;
        });

        return back()->with('status', $paid
            ? "Renewal payment {$transaction->reference} is pending. Follow the gateway instructions to complete payment."
            : 'Your free subscription is active.');
    }

    private function endsAt(string $billingPeriod): ?Carbon
    {
        return match ($billingPeriod) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly', 'annual' => now()->addYear(),
            'lifetime' => null,
            default => now()->addMonth(),
        };
    }
}
