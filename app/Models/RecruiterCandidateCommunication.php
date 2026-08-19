<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterCandidateCommunication extends Model
{
    protected $fillable = ['recruiter_id', 'candidate_id', 'type', 'subject', 'message', 'scheduled_at', 'meeting_location', 'status'];

    protected function casts(): array
    {
        return ['scheduled_at' => 'datetime'];
    }

    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recruiter_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }
}
