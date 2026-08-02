<?php

namespace App\Models;

use App\Enums\UserAccountStatus;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = ['user_type_id', 'user_role_id', 'name', 'email', 'password', 'is_active', 'account_status', 'status_reason', 'status_changed_at', 'status_changed_by', 'last_reviewed_at', 'last_reviewed_by', 'permissions_customized_at', 'permissions_customized_by'];

    public function userType(): BelongsTo
    {
        return $this->belongsTo(UserType::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')->where('starts_at', '<=', now())
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->latestOfMany('starts_at');
    }

    public function permittedModules()
    {
        return $this->belongsToMany(PortalModule::class, 'portal_module_user')->withPivot('enabled')->withTimestamps();
    }

    public function permittedMenus()
    {
        return $this->belongsToMany(PortalMenu::class, 'portal_menu_user')->withPivot(['can_view', 'can_create', 'can_update', 'can_delete'])->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->role?->is_super_admin;
    }

    public function errorSetting(): HasOne
    {
        return $this->hasOne(UserErrorSetting::class);
    }

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function accountReviews(): HasMany
    {
        return $this->hasMany(UserAccountReview::class)->latest();
    }

    public function sessionHistories(): HasMany
    {
        return $this->hasMany(UserSessionHistory::class);
    }

    public function dashboardRoute(): string
    {
        return $this->userType?->category->dashboardRoute() ?? 'home';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'account_status' => UserAccountStatus::class,
            'status_changed_at' => 'datetime',
            'last_reviewed_at' => 'datetime',
            'permissions_customized_at' => 'datetime',
        ];
    }
}
