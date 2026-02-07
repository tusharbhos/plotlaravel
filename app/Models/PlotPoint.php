<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlotPoint extends Model
{
    protected $table = 'plot_points';
    protected $guarded = [];

    protected $casts = [
        'x' => 'float',
        'y' => 'float',
        'sort_order' => 'integer',
    ];

    /**
     * Belongs to Plot
     */
    public function plot()
    {
        return $this->belongsTo(Plot::class, 'plot_id');
    }
}