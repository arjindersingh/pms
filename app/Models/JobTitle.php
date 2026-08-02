<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class JobTitle extends SharedMaster {protected $fillable=['job_specialization_id','code','short_name','display_name','description','sort_order','is_active'];public function specialization():BelongsTo{return $this->belongsTo(JobSpecialization::class,'job_specialization_id');} }
