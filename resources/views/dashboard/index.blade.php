@extends('layouts.dashboard')
@section('title', 'Dashboard')

@section('content')
@php $breadcrumbs = [['name' => 'Dashboard', 'url' => route('dashboard')]]; @endphp

<!-- ─── WELCOME HEADER ─── -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard Overview</h1>
    <p class="text-gray-500 text-sm">Welcome back! Here's your plots summary at a glance.</p>
</div>

<!-- ─── QUICK STATS ─── -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Plots -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Plots</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['total_plots'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-th-large text-indigo-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-3 text-xs text-green-600 font-semibold">
            <i class="fas fa-chart-line mr-1"></i> Total area: {{ number_format($stats['total_area'], 2) }} sq.m
        </div>
    </div>

    <!-- Available Plots -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Available</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['available_plots'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-check-circle text-green-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-3 text-xs text-gray-500 font-medium">
            {{ round(($stats['available_plots'] / max($stats['total_plots'], 1)) * 100, 1) }}% of total
        </div>
    </div>

    <!-- Sold Plots -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Sold</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['sold_plots'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-tag text-red-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-3 text-xs text-gray-500 font-medium">
            {{ round(($stats['sold_plots'] / max($stats['total_plots'], 1)) * 100, 1) }}% of total
        </div>
    </div>

    <!-- Trashed Plots -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">In Trash</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $stats['trashed_plots'] }}</h3>
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-trash-alt text-gray-600 text-xl"></i>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('plots.trash') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                <i class="fas fa-eye mr-1"></i> View Trash
            </a>
        </div>
    </div>
</div>

<!-- ─── CHARTS & DETAILS ─── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <!-- Status Distribution -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Status Distribution</h3>
            <span class="text-xs text-gray-500 font-medium">{{ $stats['total_plots'] }} plots total</span>
        </div>
        <div class="space-y-3">
            @foreach($statusChart as $item)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium">{{ $item['label'] }}</span>
                    <span class="font-bold">{{ $item['value'] }} ({{ round(($item['value'] / max($stats['total_plots'], 1)) * 100, 1) }}%)</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full" style="width: {{ ($item['value'] / max($stats['total_plots'], 1)) * 100 }}%; background-color: {{ $item['color'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-800">Category Distribution</h3>
            <span class="text-xs text-gray-500 font-medium">By plot type</span>
        </div>
        <div class="space-y-3">
            @foreach($categoryChart as $item)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium">{{ $item['label'] }}</span>
                    <span class="font-bold">{{ $item['value'] }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="h-2 rounded-full" style="width: {{ ($item['value'] / max($stats['total_plots'], 1)) * 100 }}%; background-color: {{ $item['color'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ─── RECENT PLOTS ─── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-800">Recently Added Plots</h3>
        <p class="text-xs text-gray-500 mt-1">Latest {{ $recentPlots->count() }} plots added to the system</p>
    </div>
    
    @if($recentPlots->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase">Plot ID</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase">Area</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase">Category</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase">Status</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase">Added</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-500 text-xs uppercase">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentPlots as $plot)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <td class="px-5 py-3 font-bold text-gray-800">{{ $plot->plot_id }}</td>
                    <td class="px-5 py-3 text-gray-600">{{ number_format($plot->area, 2) }} sq.m</td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $plot->getCategoryColor() }}">{{ $plot->category }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $plot->getStatusColor() }}">{{ ucfirst(str_replace('_', ' ', $plot->status)) }}</span>
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $plot->created_at->diffForHumans() }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('plots.edit', $plot->id) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                            <i class="fas fa-eye mr-1"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-5 py-10 text-center">
        <div class="flex flex-col items-center gap-2">
            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center">
                <i class="fas fa-th-large text-gray-400 text-xl"></i>
            </div>
            <p class="text-gray-500 text-sm font-medium">No plots found</p>
            <p class="text-gray-400 text-xs">Get started by <a href="{{ route('plots.create') }}" class="text-indigo-600 hover:underline">creating a new plot</a></p>
        </div>
    </div>
    @endif
    
    <div class="px-5 py-3 border-t border-gray-100 bg-gray-50">
        <a href="{{ route('plots.index') }}" class="text-xs font-semibold text-indigo-600 hover:underline">
            <i class="fas fa-list mr-1"></i> View All Plots
        </a>
    </div>
</div>

<!-- ─── QUICK ACTIONS ─── -->
<div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="{{ route('plots.create') }}" class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 hover:bg-indigo-100 transition group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center group-hover:bg-indigo-700 transition">
                <i class="fas fa-plus text-white"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800">Add New Plot</h4>
                <p class="text-xs text-gray-500 mt-0.5">Create a new plot with details</p>
            </div>
        </div>
    </a>
    
    <a href="{{ route('plots.export') }}" class="bg-green-50 border border-green-100 rounded-xl p-4 hover:bg-green-100 transition group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center group-hover:bg-green-700 transition">
                <i class="fas fa-file-export text-white"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800">Export Data</h4>
                <p class="text-xs text-gray-500 mt-0.5">Download all plots as Excel/CSV</p>
            </div>
        </div>
    </a>
    
    <a href="{{ route('plots.trash') }}" class="bg-gray-50 border border-gray-200 rounded-xl p-4 hover:bg-gray-100 transition group">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gray-600 rounded-lg flex items-center justify-center group-hover:bg-gray-700 transition">
                <i class="fas fa-trash-restore text-white"></i>
            </div>
            <div>
                <h4 class="font-semibold text-gray-800">Manage Trash</h4>
                <p class="text-xs text-gray-500 mt-0.5">{{ $stats['trashed_plots'] }} items in trash</p>
            </div>
        </div>
    </a>
</div>

@endsection