<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProfessionalMembership extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['started_on' => 'date', 'expires_on' => 'date', 'is_lifetime' => 'boolean'];
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }
}
