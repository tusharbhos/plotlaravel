<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'plot_id',
        'plot_type',
        'area',
        'fsi',
        'permissible_area',
        'rl',
        'road',
        'status',
        'category',
        'corner',
        'garden',
        'notes',
        'land_image_id' // Add this
    ];

    // Add this relationship
    public function landImage()
    {
        return $this->belongsTo(LandImage::class);
    }
    protected $table = 'plots';
    protected $guarded = [];

    protected $casts = [
        'area' => 'float',
        'fsi' => 'float',
        'permissible_area' => 'float',
        'corner' => 'boolean',
        'garden' => 'boolean',
    ];

    /**
     * Polygon points (coordinates)
     */
    public function points()
    {
        return $this->hasMany(PlotPoint::class, 'plot_id')->orderBy('sort_order');
    }

    /**
     * Plot images
     */
    public function images()
    {
        return $this->hasMany(PlotImage::class, 'plot_id');
    }

    /**
     * Active (non-deleted) images
     */
    public function activeImages()
    {
        return $this->hasMany(PlotImage::class, 'plot_id')->whereNull('deleted_at');
    }

    /**
     * Primary image
     */
    public function primaryImage()
    {
        return $this->hasOne(PlotImage::class, 'plot_id')->where('is_primary', true)->whereNull('deleted_at');
    }

    /**
     * Get polygon points as array [{x, y}, ...]
     */
    public function getPolygonPointsAttribute()
    {
        return $this->points->map(function($point) {
            return ['x' => $point->x, 'y' => $point->y];
        })->toArray();
    }

    /**
     * Status badge color
     */
    public function getStatusColor()
    {
        return match($this->status) {
            'available'    => 'bg-green-100 text-green-800',
            'sold'         => 'bg-red-100 text-red-800',
            'under_review' => 'bg-yellow-100 text-yellow-800',
            'booked'       => 'bg-blue-100 text-blue-800',
            default        => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Category badge color
     */
    public function getCategoryColor()
    {
        return match($this->category) {
            'PREMIUM'  => 'bg-purple-100 text-purple-800',
            'STANDARD' => 'bg-blue-100 text-blue-800',
            'ECO'      => 'bg-green-100 text-green-800',
            default    => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Scope: only trashed (soft-deleted) plots
     */
    public static function trashed()
    {
        return static::onlyTrashed();
    }
}