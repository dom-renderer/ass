<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'secondary_images' => 'array',
        'documents' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('os', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->where('type', 0);
        });
    }

    public function designations() {
        return $this->hasMany(Designation::class, 'type_id')->where('type', 1);
    }

    public function thecity() {
        return $this->belongsTo(City::class, 'city', 'city_id');
    }

    public function dom() {
        return $this->belongsTo(User::class, 'dom_id');
    }

    public function storetype() {
        return $this->belongsTo(StoreType::class, 'store_type');
    }

    public function modeltype() {
        return $this->belongsTo(ModelType::class, 'model_type');
    }

    public function storecategory() {
        return $this->belongsTo(StoreCategory::class, 'store_category');
    }

    public function assetStatus() {
        return $this->belongsTo(AssetStatus::class, 'asset_status_id');
    }

    public function store() {
        return $this->belongsTo(Store::class, 'location');
    }

    public function subassets() {
        return $this->belongsToMany( Store::class, 'location_assets', 'asset_id', 'location_id' );
    }

    public function getPiAttribute() {
        if ( file_exists( public_path( "storage/primary_image/{$this->primary_image}" ) ) ) {
            return asset( "storage/primary_image/{$this->primary_image}" );
        }

        return '';
    }

    public function getSiAttribute() {
        $attachment_url_arr = array();
        $attachment_arr = !empty($this->secondary_images) ? $this->secondary_images : array();
        if ( !empty($attachment_arr) ) {
            foreach ( $attachment_arr as $attachment ) {
                if ( file_exists( public_path( "storage/secondary_images/{$attachment}" ) ) ) {
                    $attachment_url_arr[] = asset( "storage/secondary_images/{$attachment}" );
                }
            }
        }

        return $attachment_url_arr;
    }

    public function scopeLoc($query)
    {
        return $query->where('type', 0);
    }

    public function scopeAss($query)
    {
        return $query->where('type', '!=', 0);
    }

}