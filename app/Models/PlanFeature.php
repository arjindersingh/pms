<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PlanFeature extends Model
{
    protected $fillable = ['key', 'category', 'name', 'description', 'icon', 'position'];

    public function plans(): BelongsToMany
    {
        return $this->belongsToMany(SubscriptionPlan::class, 'plan_feature_subscription_plan')->withTimestamps();
    }
}
