<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionAudit extends Model
{
    protected $fillable = ['actor_id', 'target_user_id', 'user_role_id', 'event', 'summary', 'changes', 'ip_address'];

    protected function casts(): array
    {
        return ['changes' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id')->withTrashed();
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id')->withTrashed();
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }
}
