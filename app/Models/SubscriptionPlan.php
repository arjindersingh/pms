<?php

namespace App\Models;

use App\Enums\UserCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    public const BILLING_PERIODS = [
        'na' => 'N/A',
        'daily' => 'Daily',
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'yearly' => 'Yearly',
        'lifetime' => 'Lifetime',
        'one_time' => 'One-time payment',
    ];

    protected $fillable = ['category', 'name', 'slug', 'description', 'price', 'currency', 'billing_period', 'position', 'is_active'];

    public function billingPeriodLabel(): string
    {
        return (float) $this->price === 0.0
            ? self::BILLING_PERIODS['na']
            : (self::BILLING_PERIODS[$this->billing_period] ?? str($this->billing_period)->headline()->toString());
    }

    protected function casts(): array
    {
        return ['category' => UserCategory::class, 'price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(PortalMenu::class, 'portal_menu_subscription_plan')
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete'])->withTimestamps();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }
}
