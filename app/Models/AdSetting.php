<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class AdSetting extends Model
{
    protected $attributes = [
        'enabled' => false,
        'auto_ads_enabled' => false,
        'show_placeholders' => true,
        'homepage_top_enabled' => true,
        'homepage_middle_enabled' => true,
        'homepage_bottom_enabled' => true,
    ];

    protected $fillable = [
        'enabled', 'auto_ads_enabled', 'show_placeholders', 'publisher_id',
        'homepage_top_enabled', 'homepage_top_slot',
        'homepage_middle_enabled', 'homepage_middle_slot',
        'homepage_bottom_enabled', 'homepage_bottom_slot', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean', 'auto_ads_enabled' => 'boolean', 'show_placeholders' => 'boolean',
            'homepage_top_enabled' => 'boolean', 'homepage_middle_enabled' => 'boolean',
            'homepage_bottom_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        if (! Schema::hasTable('ad_settings')) {
            return new static;
        }

        return static::query()->firstOrCreate([]);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function hasPublisher(): bool
    {
        return (bool) preg_match('/^ca-pub-\d{16}$/', (string) $this->publisher_id);
    }

    public function shouldLoadScript(): bool
    {
        return $this->enabled && $this->hasPublisher();
    }

    public function slot(string $placement): ?string
    {
        return $this->{"{$placement}_slot"};
    }

    public function placementEnabled(string $placement): bool
    {
        return (bool) $this->{"{$placement}_enabled"};
    }

    public function canServe(string $placement): bool
    {
        return $this->shouldLoadScript()
            && $this->placementEnabled($placement)
            && (bool) preg_match('/^\d{5,20}$/', (string) $this->slot($placement));
    }
}
