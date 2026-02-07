@extends('layouts.dashboard')
@section('title', 'Edit ' . $landImage->land_name)

@section('content')
@php $breadcrumbs = [['name' => 'Land Images', 'url' => route('land-images.index')], ['name' => 'Edit', 'url' => null], ['name' => $landImage->land_name, 'url' => null]]; @endphp

<!-- ─── TOP BAR ─── -->
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-800">Edit Land Image <span class="text-indigo-600">{{ $landImage->land_name }}</span></h2>
    <a href="{{ route('land-images.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

<form method="POST" action="{{ route('land-images.update', $landImage->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- ─── LEFT + CENTER: Main Form ─── -->
        <div class="lg:col-span-2 space-y-5">
            <!-- Land Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-600"></i>
                    <h3 class="font-bold text-gray-700 text-sm">Land Details</h3>
                </div>
                <div class="p-5 space-y-4">
                    <!-- Land Name -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Land Name <span class="text-red-500">*</span></label>
                        <input type="text" name="land_name" value="{{ old('land_name', $landImage->land_name) }}" placeholder="e.g. Mountain View Land"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" required />
                        @error('land_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Notes</label>
                        <textarea name="notes" rows="3" placeholder="Any notes about this land..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 resize-none">{{ old('notes', $landImage->notes) }}</textarea>
                        @error('notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <!-- Image Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-green-50 border-b border-green-100 px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-image text-green-600"></i>
                    <h3 class="font-bold text-green-700 text-sm">Land Image</h3>
                </div>
                <div class="p-5">
                    <!-- Current Image -->
                    @if($landImage->land_image)
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-2">Current Image:</p>
                        <div class="flex items-center gap-4">
                            <img src="{{ $landImage->image_url }}" alt="{{ $landImage->land_name }}" class="w-32 h-32 object-cover rounded-lg border border-gray-200">
                            <div>
                                <button type="button" onclick="removeImage()" class="text-sm text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash mr-1"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- File Upload -->
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-6 text-center hover:border-green-400 transition cursor-pointer" onclick="document.getElementById('land_image').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">{{ $landImage->land_image ? 'Change Image' : 'Upload Image' }}</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WEBP (max 5MB)</p>
                        <input type="file" name="land_image" id="land_image" accept="image/*" class="hidden" onchange="previewImage(this)" />
                    </div>
                    
                    <!-- New Image Preview -->
                    <div id="imagePreviewContainer" class="mt-4 hidden">
                        <p class="text-xs text-gray-500 mb-2">New Image Preview:</p>
                        <img id="previewImage" class="max-w-xs rounded-lg border border-gray-200">
                    </div>
                    @error('land_image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- ─── RIGHT: Summary ─── -->
        <div class="lg:col-span-1 space-y-5">
            <!-- Summary Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-900 text-white px-5 py-3">
                    <h3 class="font-bold text-sm">Land Summary</h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Land Name</span>
                        <span class="font-semibold text-gray-800">{{ $landImage->land_name }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Image</span>
                        <span class="font-semibold text-gray-800">
                            @if($landImage->land_image)
                                Uploaded
                            @else
                                No image
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Created</span>
                        <span class="font-semibold text-gray-600 text-xs">{{ $landImage->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            <!-- Hidden field for image removal -->
            <input type="hidden" name="remove_image" id="removeImage" value="0">

            <!-- Submit Button -->
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-md hover:shadow-lg">
                <i class="fas fa-save"></i> Update Land Image
            </button>
            <a href="{{ route('land-images.index') }}" class="w-full flex items-center justify-center gap-2 text-gray-600 hover:text-gray-800 text-sm py-2 transition">
                <i class="fas fa-times text-xs"></i> Cancel
            </a>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
// ─── Image Preview ───
function previewImage(input) {
    const container = document.getElementById("imagePreviewContainer");
    const preview = document.getElementById("previewImage");
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.classList.remove("hidden");
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ─── Remove Image ───
function removeImage() {
    if (confirm('Are you sure you want to remove the current image?')) {
        document.getElementById('removeImage').value = '1';
        const currentImage = document.querySelector('img[alt="{{ $landImage->land_name }}"]');
        if (currentImage) {
            currentImage.parentElement.parentElement.style.display = 'none';
        }
    }
}
</script>
@endpush