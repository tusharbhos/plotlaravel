<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LandImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'land_name',
        'land_image',
        'notes'
    ];

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->land_image && str_starts_with($this->land_image, 'http')) {
            return $this->land_image;
        }
        
        return $this->land_image ? asset('storage/' . $this->land_image) : asset('images/default-land.jpg');
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
}