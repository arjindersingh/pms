<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class CompanyProfile extends Model
{
    protected $guarded=[];
    protected function casts(): array { return ['founded_on'=>'date','social_links'=>'array','promotion_enabled'=>'boolean']; }
    public static function current(): self { return Schema::hasTable('company_profiles') ? static::firstOrCreate([],['display_name'=>'PlaceFlow']) : new static(['display_name'=>'PlaceFlow','tagline'=>'Where talent meets opportunity']); }
    public function fullAddress(): string { return collect([$this->address_line_1,$this->address_line_2,$this->city,$this->state,$this->postal_code,$this->country])->filter()->implode(', '); }
    public function logoUrl(bool $dark=false): ?string { $path=$dark ? ($this->logo_dark_path ?: $this->logo_path) : $this->logo_path; return $path ? asset('storage/'.$path) : null; }
}
