<?php

namespace App\Models;

use App\Enums\UserCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class UserType extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'category', 'name', 'slug', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['category' => UserCategory::class, 'is_active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(PortalModule::class)->withPivot('enabled')->withTimestamps();
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(PortalMenu::class)
            ->withPivot(['can_view', 'can_create', 'can_update', 'can_delete'])
            ->withTimestamps();
    }

    /** @return Collection<int, self> */
    public function lineage(): Collection
    {
        $lineage = collect();
        $current = $this;

        while ($current) {
            $lineage->push($current);
            $current = $current->parent;
        }

        return $lineage;
    }
}
