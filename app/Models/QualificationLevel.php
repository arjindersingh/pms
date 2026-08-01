<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class QualificationLevel extends SharedMaster
{
    public function degrees(): HasMany
    {
        return $this->hasMany(Degree::class);
    }
}
