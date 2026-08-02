<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateRecognition extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['awarded_on' => 'date'];
    }

    public function candidateProfile(): BelongsTo
    {
        return $this->belongsTo(CandidateProfile::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(RecognitionLevel::class, 'recognition_level_id');
    }
}
