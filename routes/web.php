<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlotController;
use App\Http\Controllers\LandImageController;

// ─────────────────────────────────────────────
// PUBLIC ROUTES (no auth needed)
// ─────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::get('/register', [AuthController::class, 'registerPage'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// ─────────────────────────────────────────────
// PROTECTED ROUTES (auth middleware)
// ─────────────────────────────────────────────
Route::middleware([App\Http\Middleware\AuthMiddleware::class])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Plots CRUD
    Route::get('/plots',              [PlotController::class, 'index'])->name('plots.index');
    Route::get('/plots/create',       [PlotController::class, 'create'])->name('plots.create');
    Route::post('/plots',             [PlotController::class, 'store'])->name('plots.store');
    Route::get('/plots/{id}/edit',    [PlotController::class, 'edit'])->name('plots.edit');
    Route::put('/plots/{id}',         [PlotController::class, 'update'])->name('plots.update');
    Route::delete('/plots/{id}',      [PlotController::class, 'destroy'])->name('plots.destroy');

    // Multiple delete
    Route::post('/plots/multiple-delete', [PlotController::class, 'multipleDelete'])->name('plots.multipleDelete');

    // Trash routes
    Route::get('/plots/trashed',             [PlotController::class, 'trashed'])->name('plots.trashed');
    Route::get('/plots/trash',               [PlotController::class, 'trashed'])->name('plots.trash'); // Alias
    Route::post('/plots/{id}/restore',       [PlotController::class, 'restore'])->name('plots.restore');
    Route::delete('/plots/{id}/force-delete', [PlotController::class, 'forceDelete'])->name('plots.forceDelete');
    Route::post('/plots/multiple-restore',   [PlotController::class, 'multipleRestore'])->name('plots.multipleRestore');
    Route::post('/plots/multiple-force-delete', [PlotController::class, 'multipleForceDelete'])->name('plots.multipleForceDelete');

    // Image CRUD
    Route::post('/plots/{plotId}/image/add',    [PlotController::class, 'addImage'])->name('plots.image.add');
    Route::post('/plots/{plotId}/image/upload', [PlotController::class, 'uploadImage'])->name('plots.image.upload');
    Route::delete('/plots/image/{imageId}',     [PlotController::class, 'deleteImage'])->name('plots.image.delete');
    Route::post('/plots/image/{imageId}/primary', [PlotController::class, 'setPrimaryImage'])->name('plots.image.primary');

    // Excel Import / Export
    Route::get('/plots/export-excel',    [PlotController::class, 'exportExcel'])->name('plots.export');
    Route::get('/plots/download-template', [PlotController::class, 'downloadTemplate'])->name('plots.template');
    Route::get('/plots/import',          [PlotController::class, 'showImportForm'])->name('plots.import.form'); // ADD THIS LINE
    Route::post('/plots/import-excel',   [PlotController::class, 'importExcel'])->name('plots.import');

    // Land Images Routes (CORRECTED ORDER)
    // First define custom routes BEFORE resource routes
    Route::get('/land-images/trashed', [LandImageController::class, 'trashed'])->name('land-images.trashed');
    Route::post('/land-images/{id}/restore', [LandImageController::class, 'restore'])->name('land-images.restore');
    Route::delete('/land-images/{id}/force-delete', [LandImageController::class, 'forceDelete'])->name('land-images.forceDelete');
    
    // Then define resource routes
    Route::resource('land-images', LandImageController::class)->except(['show']);
    Route::get('/land-images/{landImage}', [LandImageController::class, 'show'])->name('land-images.show');
});