<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CandidateExperience extends Model { protected $guarded=['id']; protected function casts(): array { return ['started_on'=>'date','ended_on'=>'date','currently_working'=>'boolean']; } }
