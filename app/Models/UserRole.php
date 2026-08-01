<?php

namespace App\Models;

use App\Enums\UserCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserRole extends Model
{
    protected $fillable = ['category', 'name', 'slug', 'description', 'is_super_admin', 'is_active'];

    protected function casts(): array
    {
        return ['category' => UserCategory::class, 'is_super_admin' => 'boolean', 'is_active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(PortalModule::class, 'portal_module_user_role')->withPivot('enabled')->withTimestamps();
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(PortalMenu::class, 'portal_menu_user_role')->withPivot(['can_view', 'can_create', 'can_update', 'can_delete'])->withTimestamps();
    }
}
