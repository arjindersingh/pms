<?php

namespace App\Models;

class EducationAuthority extends SharedMaster
{
    protected $fillable = ['authority_type', 'code', 'short_name', 'display_name', 'description', 'sort_order', 'is_active'];
}
