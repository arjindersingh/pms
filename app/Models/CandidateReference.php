<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateReference extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['years_known' => 'decimal:1', 'permission_to_contact' => 'boolean', 'consent_received' => 'boolean', 'is_primary' => 'boolean'];
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReferenceType::class, 'reference_type_id');
    }
}
