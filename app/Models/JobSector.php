<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\HasMany;
class JobSector extends SharedMaster { public function specializations():HasMany{return $this->hasMany(JobSpecialization::class);} }
