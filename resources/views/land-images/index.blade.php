@extends('layouts.dashboard')
@section('title', 'Land Images')

@section('content')
@php $breadcrumbs = [['name' => 'Land Images', 'url' => route('land-images.index')]]; @endphp

<!-- ─── TOP BAR: Actions + Filters ─── -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <!-- Left: Title + Bulk Actions -->
    <div class="flex items-center gap-3 flex-wrap">
        <h2 class="text-lg font-bold text-gray-800">Land Images</h2>
        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $landImages->count() }} images</span>
    </div>

    <!-- Right: Action Buttons -->
    <div class="flex items-center gap-2 flex-wrap">
        <!-- Add Land Image -->
        <a href="{{ route('land-images.create') }}" class="flex items-center gap-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg transition shadow-sm">
            <i class="fas fa-plus"></i> Add Land Image
        </a>
        <!-- View Trash -->
        <a href="{{ route('land-images.trashed') }}" class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 bg-gray-100 border border-gray-200 hover:bg-gray-200 px-3 py-2 rounded-lg transition">
            <i class="fas fa-trash-alt"></i> View Trash
        </a>
    </div>
</div>

<!-- ─── STATS CARDS ─── -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">Total Images</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $landImages->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-image text-indigo-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">With Images</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $landImages->whereNotNull('land_image')->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-camera text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 font-medium">In Trash</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ \App\Models\LandImage::onlyTrashed()->count() }}</h3>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-trash-alt text-red-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- ─── LAND IMAGES TABLE ─── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Image</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Land Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Notes</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Created</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($landImages as $land)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition">
                    <!-- Image -->
                    <td class="px-4 py-3">
                        @if($land->land_image)
                            <img src="{{ $land->image_url }}" alt="{{ $land->land_name }}" 
                                class="w-12 h-12 rounded-lg object-cover border border-gray-200">
                        @else
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-sm"></i>
                            </div>
                        @endif
                    </td>
                    <!-- Land Name -->
                    <td class="px-4 py-3 font-bold text-gray-800">{{ $land->land_name }}</td>
                    <!-- Notes -->
                    <td class="px-4 py-3 text-gray-600">
                        @if($land->notes)
                            <span class="text-sm truncate max-w-xs block">{{ $land->notes }}</span>
                        @else
                            <span class="text-gray-400 text-sm">—</span>
                        @endif
                    </td>
                    <!-- Created -->
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $land->created_at->diffForHumans() }}</td>
                    <!-- Actions -->
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <!-- View -->
                            <a href="{{ route('land-images.show', $land->id) }}" class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg transition" title="View">
                                <i class="fas fa-eye text-sm"></i>
                            </a>
                            <!-- Edit -->
                            <a href="{{ route('land-images.edit', $land->id) }}" class="p-1.5 text-indigo-600 hover:bg-indigo-100 rounded-lg transition" title="Edit">
                                <i class="fas fa-pencil-alt text-sm"></i>
                            </a>
                            <!-- Delete -->
                            <form action="{{ route('land-images.destroy', $land->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure?')" class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition" title="Delete">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">No land images found</p>
                            <p class="text-gray-400 text-xs">Get started by <a href="{{ route('land-images.create') }}" class="text-indigo-600 hover:underline">creating a land image</a></p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection