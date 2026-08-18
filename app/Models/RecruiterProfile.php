<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecruiterProfile extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function organizations(): HasMany { return $this->hasMany(RecruiterOrganization::class)->orderByDesc('is_primary')->orderBy('name'); }
}
