<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateDeclaration extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_accepted' => 'boolean', 'accepted_at' => 'datetime'];
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(DeclarationType::class, 'declaration_type_id');
    }
}
