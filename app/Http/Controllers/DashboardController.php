<?php

namespace App\Http\Controllers;

use App\Models\Plot;

class DashboardController extends Controller
{
    /**
     * Show Dashboard with summary stats
     */
    public function index()
    {
        $stats = [
            'total_plots'      => Plot::count(),
            'available_plots'  => Plot::where('status', 'available')->count(),
            'sold_plots'       => Plot::where('status', 'sold')->count(),
            'booked_plots'     => Plot::where('status', 'booked')->count(),
            'under_review'     => Plot::where('status', 'under_review')->count(),
            'premium_plots'    => Plot::where('category', 'PREMIUM')->count(),
            'standard_plots'   => Plot::where('category', 'STANDARD')->count(),
            'eco_plots'        => Plot::where('category', 'ECO')->count(),
            'trashed_plots'    => Plot::onlyTrashed()->count(),
            'total_area'       => Plot::sum('area'),
            'corner_plots'     => Plot::where('corner', true)->count(),
            'garden_plots'     => Plot::where('garden', true)->count(),
        ];

        // Recent plots (last 5)
        $recentPlots = Plot::with('points', 'activeImages')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Status distribution for chart
        $statusChart = [
            ['label' => 'Available', 'value' => $stats['available_plots'], 'color' => '#10B981'],
            ['label' => 'Sold',      'value' => $stats['sold_plots'],      'color' => '#EF4444'],
            ['label' => 'Booked',    'value' => $stats['booked_plots'],    'color' => '#3B82F6'],
            ['label' => 'Under Review','value' => $stats['under_review'], 'color' => '#F59E0B'],
        ];

        // Category distribution
        $categoryChart = [
            ['label' => 'Premium',  'value' => $stats['premium_plots'],  'color' => '#8B5CF6'],
            ['label' => 'Standard', 'value' => $stats['standard_plots'], 'color' => '#3B82F6'],
            ['label' => 'Eco',      'value' => $stats['eco_plots'],      'color' => '#10B981'],
        ];

        return view('dashboard.index', compact(
            'stats', 'recentPlots', 'statusChart', 'categoryChart'
        ));
    }
}