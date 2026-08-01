<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserErrorSetting extends Model
{
    protected $fillable = [
        'placement', 'font_family', 'font_size', 'text_color', 'background_color',
        'accent_color', 'density', 'motion', 'show_icon', 'allow_dismiss',
        'group_messages', 'auto_dismiss_seconds',
    ];

    protected function casts(): array
    {
        return [
            'font_size' => 'integer',
            'show_icon' => 'boolean',
            'allow_dismiss' => 'boolean',
            'group_messages' => 'boolean',
            'auto_dismiss_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
