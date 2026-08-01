<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CandidateEducation extends Model { protected $guarded=['id']; protected function casts(): array { return ['currently_studying'=>'boolean']; } }
