@extends('layouts.dashboard')
@section('title', $landImage->land_name)

@section('content')
@php $breadcrumbs = [['name' => 'Land Images', 'url' => route('land-images.index')], ['name' => 'View', 'url' => null]]; @endphp

<!-- ─── TOP BAR ─── -->
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-800">{{ $landImage->land_name }}</h2>
    <a href="{{ route('land-images.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left text-xs"></i> Back to List
    </a>
</div>

<!-- Details Card -->
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Image Section -->
        @if($landImage->land_image)
        <div class="relative bg-gray-100">
            <img src="{{ $landImage->image_url }}" alt="{{ $landImage->land_name }}" 
                class="w-full h-64 object-cover">
        </div>
        @endif

        <!-- Details Section -->
        <div class="p-6">
            <div class="grid grid-cols-1 gap-6">
                <!-- Land Details -->
                <div class="space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase">Land Name</h3>
                        <p class="text-lg font-bold text-gray-800">{{ $landImage->land_name }}</p>
                    </div>
                    
                    @if($landImage->notes)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-500 uppercase">Notes</h3>
                        <p class="text-gray-700 whitespace-pre-line">{{ $landImage->notes }}</p>
                    </div>
                    @endif
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase">Created</h3>
                            <p class="text-gray-700">{{ $landImage->created_at->format('M d, Y') }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase">Updated</h3>
                            <p class="text-gray-700">{{ $landImage->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-between">
                <div class="flex gap-2">
                    <a href="{{ route('land-images.edit', $landImage->id) }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-pencil-alt mr-1"></i> Edit
                    </a>
                    <a href="{{ route('land-images.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-300 transition">
                        <i class="fas fa-list mr-1"></i> All Land Images
                    </a>
                </div>
                <form action="{{ route('land-images.destroy', $landImage->id) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition" onclick="return confirm('Are you sure?')">
                        <i class="fas fa-trash-alt mr-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection