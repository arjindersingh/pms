<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class CandidatePublication extends Model {
 protected $fillable=['publication_type_id','publication_mode_id','area_of_publication','publication_count','title','publisher_name','published_on','edition_or_volume','identifier','publication_url','co_authors','is_peer_reviewed','is_verified','description'];
 protected function casts():array{return ['published_on'=>'date','is_peer_reviewed'=>'boolean','is_verified'=>'boolean'];}
 public function type():BelongsTo{return $this->belongsTo(PublicationType::class,'publication_type_id');}public function mode():BelongsTo{return $this->belongsTo(PublicationMode::class,'publication_mode_id');}
}
