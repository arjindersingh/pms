<?php

namespace App\Enums;

enum UserAccountStatus: string
{
    case Active = 'active';
    case PendingReview = 'pending_review';
    case Suspended = 'suspended';
    case Locked = 'locked';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active', self::PendingReview => 'Pending review',
            self::Suspended => 'Suspended', self::Locked => 'Locked', self::Archived => 'Archived',
        };
    }

    public function allowsLogin(): bool
    {
        return $this === self::Active;
    }
}
