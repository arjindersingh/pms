<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class SharedMaster extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'short_name', 'display_name', 'description', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->orderByRaw("CASE WHEN UPPER(code) LIKE 'OTHER%' OR LOWER(display_name) LIKE 'other%' THEN 1 ELSE 0 END")
            ->orderBy('display_name');
    }
}
