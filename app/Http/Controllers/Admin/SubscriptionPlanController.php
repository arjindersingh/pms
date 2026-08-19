<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserCategory;
use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use App\Models\PortalMenu;
use App\Models\PortalModule;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Support\Currency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.subscriptions.index', ['plans' => SubscriptionPlan::withCount(['subscriptions' => fn ($q) => $q->where('status', 'active')])->orderBy('category')->orderBy('position')->get()]);
    }

    public function create(): View
    {
        return view('admin.subscriptions.edit', ['plan' => new SubscriptionPlan(['currency' => Currency::DEFAULT, 'billing_period' => 'monthly', 'is_active' => true]), 'modules' => collect(), 'features' => collect(), 'creating' => true]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request, true);
        $base = Str::slug($data['name']);
        $slug = $base;
        $suffix = 2;
        while (SubscriptionPlan::where('category', $data['category'])->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }
        $plan = SubscriptionPlan::create($data + ['slug' => $slug, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.subscription-plans.edit', $plan)->with('status', 'Plan created. You can now select its menu permissions.');
    }

    public function edit(SubscriptionPlan $subscriptionPlan): View
    {
        $moduleSlug = $subscriptionPlan->category === UserCategory::Recruiter ? 'recruitment' : 'career';
        $modules = PortalModule::where('slug', $moduleSlug)->with('menus')->get();

        return view('admin.subscriptions.edit', ['plan' => $subscriptionPlan->load(['menus', 'features']), 'modules' => $modules, 'features' => PlanFeature::where('category', $subscriptionPlan->category)->orderBy('position')->get(), 'creating' => false]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan): RedirectResponse
    {
        $data = $this->validated($request, false);
        DB::transaction(function () use ($request, $subscriptionPlan, $data) {
            $subscriptionPlan->update($data + ['is_active' => $request->boolean('is_active')]);
            $allowedMenuIds = PortalMenu::whereHas('module', fn ($q) => $q->where('slug', $subscriptionPlan->category === UserCategory::Recruiter ? 'recruitment' : 'career'))->pluck('id');
            $subscriptionPlan->menus()->sync($allowedMenuIds->mapWithKeys(fn ($id) => [$id => [
                'can_view' => $request->boolean("menus.$id.view"), 'can_create' => $request->boolean("menus.$id.create"),
                'can_update' => $request->boolean("menus.$id.update"), 'can_delete' => $request->boolean("menus.$id.delete"),
            ]])->all());
            $featureIds = PlanFeature::query()->where('category', $subscriptionPlan->category)->whereIn('id', $request->input('features', []))->pluck('id');
            $subscriptionPlan->features()->sync($featureIds);
        });

        return back()->with('status', 'Subscription plan and menu permissions saved.');
    }

    public function assign(Request $request, int $user): RedirectResponse
    {
        $account = User::with('userType')->findOrFail($user);
        abort_unless(in_array($account->userType->category, [UserCategory::Recruiter, UserCategory::Talent], true), 422, 'Only recruiter and talent accounts can have subscriptions.');
        $data = $request->validate([
            'subscription_plan_id' => ['required', Rule::exists('subscription_plans', 'id')->where('category', $account->userType->category->value)->where('is_active', true)],
            'ends_at' => ['nullable', 'date', 'after:now'], 'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $plan = SubscriptionPlan::findOrFail($data['subscription_plan_id']);
        DB::transaction(function () use ($request, $account, $plan, $data) {
            $account->subscriptions()->where('status', 'active')->update(['status' => 'replaced', 'ends_at' => now()]);
            $account->subscriptions()->create([
                'subscription_plan_id' => $plan->id, 'status' => 'active', 'starts_at' => now(),
                'ends_at' => $data['ends_at'] ?? null, 'price' => $plan->price, 'currency' => $plan->currency,
                'billing_period' => $plan->billing_period, 'assigned_by' => $request->user()->id, 'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('status', "{$plan->name} subscription assigned to {$account->name}.");
    }

    private function validated(Request $request, bool $withCategory): array
    {
        $rules = ['name' => ['required', 'string', 'max:100'], 'description' => ['nullable', 'string', 'max:1000'], 'price' => ['required', 'numeric', 'min:0'], 'currency' => ['required', Rule::in(Currency::CODES)], 'billing_period' => ['required', Rule::in(array_keys(SubscriptionPlan::BILLING_PERIODS))], 'position' => ['required', 'integer', 'min:0'], 'is_active' => ['boolean']];
        if ($withCategory) {
            $rules['category'] = ['required', Rule::in([UserCategory::Recruiter->value, UserCategory::Talent->value])];
        }
        $data = $request->validate($rules);
        $data['billing_period'] = (float) $data['price'] === 0.0 ? 'na' : $data['billing_period'];

        if ((float) $data['price'] > 0 && $data['billing_period'] === 'na') {
            throw IlluminateValidationValidationException::withMessages(['billing_period' => 'Select a billing period for a paid plan.']);
        }

        return $data;
    }
}
