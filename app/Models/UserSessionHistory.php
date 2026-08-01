<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserSessionHistory extends Model
{
    protected $fillable = [
        'user_id', 'session_hash', 'ip_address', 'user_agent', 'browser', 'browser_version',
        'operating_system', 'device_type', 'device_name', 'locale', 'login_method', 'remembered',
        'logged_in_at', 'last_seen_at', 'logged_out_at', 'duration_seconds', 'request_count',
        'last_route', 'last_path', 'referrer_host', 'ended_reason',
    ];

    protected function casts(): array
    {
        return [
            'remembered' => 'boolean', 'logged_in_at' => 'datetime', 'last_seen_at' => 'datetime',
            'logged_out_at' => 'datetime', 'duration_seconds' => 'integer', 'request_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserSessionActivity::class)->latest('occurred_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('logged_out_at')->where('last_seen_at', '>=', now()->subMinutes(config('session.lifetime')));
    }

    public function isActive(): bool
    {
        return $this->logged_out_at === null && $this->last_seen_at->gte(now()->subMinutes(config('session.lifetime')));
    }

    public function statusLabel(): string
    {
        if ($this->logged_out_at) {
            return 'Logged out';
        }

        return $this->isActive() ? 'Active' : 'Expired';
    }

    public function displayDuration(): string
    {
        $seconds = $this->duration_seconds ?: $this->logged_in_at->diffInSeconds($this->logged_out_at ?? $this->last_seen_at);

        if ($seconds < 60) {
            return $seconds.' sec';
        }
        if ($seconds < 3600) {
            return intdiv($seconds, 60).' min';
        }

        return intdiv($seconds, 3600).' hr '.intdiv($seconds % 3600, 60).' min';
    }
}
