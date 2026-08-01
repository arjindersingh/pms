<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortalModule extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'position', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function menus(): HasMany
    {
        return $this->hasMany(PortalMenu::class)->orderBy('position');
    }

    public function userTypes(): BelongsToMany
    {
        return $this->belongsToMany(UserType::class)->withPivot('enabled')->withTimestamps();
    }
}
