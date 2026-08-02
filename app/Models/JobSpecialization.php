<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class JobSpecialization extends SharedMaster {protected $fillable=['job_sector_id','code','short_name','display_name','description','sort_order','is_active'];public function sector():BelongsTo{return $this->belongsTo(JobSector::class,'job_sector_id');}public function titles():HasMany{return $this->hasMany(JobTitle::class);} }
