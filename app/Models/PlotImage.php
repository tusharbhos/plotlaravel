<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlotImage extends Model
{
    use SoftDeletes;

    protected $table = 'plot_images';
    protected $guarded = [];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Belongs to Plot
     */
    public function plot()
    {
        return $this->belongsTo(Plot::class, 'plot_id');
    }

    /**
     * Get full URL for image
     */
    public function getImageUrl()
    {
        // If it's an external URL, return directly
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }
        // Otherwise, return relative to public folder
        return '/images/plots/' . $this->image_path;
    }
}