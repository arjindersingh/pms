<?php

namespace App\Models;

use App\Enums\UserCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    protected $fillable = ['category', 'name', 'slug', 'description', 'price', 'currency', 'billing_period', 'position', 'is_active'];

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
