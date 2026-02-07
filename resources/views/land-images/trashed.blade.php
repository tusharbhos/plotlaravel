@extends('layouts.dashboard')
@section('title', 'Trashed Land Images')

@section('content')
@php $breadcrumbs = [['name' => 'Land Images', 'url' => route('land-images.index')], ['name' => 'Trash', 'url' => null]]; @endphp

<!-- ─── TOP BAR ─── -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-trash-alt text-red-600"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Trashed Land Images</h2>
            <p class="text-xs text-gray-500">Items here will be permanently deleted after 30 days</p>
        </div>
        <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $landImages->count() }} items</span>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('land-images.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
            <i class="fas fa-arrow-left text-xs"></i> Back to Land Images
        </a>
    </div>
</div>

<!-- ─── INFO BANNER ─── -->
<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
    <div>
        <p class="text-sm text-amber-800 font-semibold">Trash Items</p>
        <p class="text-xs text-amber-600">These land images have been moved to trash. You can restore them or permanently delete them.</p>
    </div>
</div>

<!-- ─── TRASHED LAND IMAGES TABLE ─── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Land Name</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Image</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Deleted</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($landImages as $land)
                <tr class="border-t border-gray-100 hover:bg-red-50/30 transition bg-red-50/10">
                    <td class="px-4 py-3">
                        <span class="font-bold text-gray-700 line-through">{{ $land->land_name }}</span>
                        @if($land->notes)
                            <p class="text-xs text-gray-500 line-through truncate max-w-xs">{{ $land->notes }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($land->land_image)
                            <img src="{{ asset('storage/' . $land->land_image) }}" alt="{{ $land->land_name }}" class="w-10 h-10 rounded object-cover opacity-60">
                        @else
                            <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center opacity-60">
                                <i class="fas fa-image text-gray-400 text-sm"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $land->deleted_at->diffForHumans() }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <!-- Restore -->
                            <form action="{{ route('land-images.restore', $land->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg transition" title="Restore">
                                    <i class="fas fa-redo text-sm"></i>
                                </button>
                            </form>
                            <!-- Force Delete -->
                            <form action="{{ route('land-images.forceDelete', $land->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete Forever" onclick="return confirm('Permanently delete? This cannot be undone!')">
                                    <i class="fas fa-times-circle text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">Trash is empty!</p>
                            <p class="text-gray-400 text-xs">No deleted land images to recover.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection