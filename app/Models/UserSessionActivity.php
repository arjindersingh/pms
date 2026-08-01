<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSessionActivity extends Model
{
    public $timestamps = false;

    protected $fillable = ['method', 'path', 'route_name', 'response_status', 'occurred_at'];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime', 'response_status' => 'integer'];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(UserSessionHistory::class, 'user_session_history_id');
    }
}
