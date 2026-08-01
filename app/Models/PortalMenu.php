<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortalMenu extends Model
{
    protected $fillable = ['portal_module_id', 'parent_id', 'name', 'slug', 'route_name', 'icon', 'position', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(PortalModule::class, 'portal_module_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position');
    }

    public function userTypes(): BelongsToMany
    {
        return $this->belongsToMany(UserType::class)
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete'])
            ->withTimestamps();
    }
}
