<?php

// routes/api.php
use App\Http\Controllers\PlotController;

// use it like this : http://127.0.0.1:8000/api/v1/plots

Route::get('/v1/plots', [PlotController::class, 'getPlotsJson']);
Route::get('/v1/plot-points', [PlotController::class, 'getPlotPointsJson']);