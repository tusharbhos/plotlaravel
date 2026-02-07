@extends('layouts.dashboard')
@section('title', 'Create Land Image')

@section('content')
@php $breadcrumbs = [['name' => 'Land Images', 'url' => route('land-images.index')], ['name' => 'Create', 'url' => null]]; @endphp

<!-- ─── TOP BAR ─── -->
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-800">Create New Land Image</h2>
    <a href="{{ route('land-images.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left text-xs"></i> Back to Land Images
    </a>
</div>

<form method="POST" action="{{ route('land-images.store') }}" enctype="multipart/form-data">
    @csrf

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
                        <input type="text" name="land_name" value="{{ old('land_name') }}" placeholder="e.g. Mountain View Land"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" required />
                        @error('land_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Notes</label>
                        <textarea name="notes" rows="3" placeholder="Any notes about this land..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 resize-none">{{ old('notes') }}</textarea>
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
                    <!-- File Upload -->
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-6 text-center hover:border-green-400 transition cursor-pointer" onclick="document.getElementById('land_image').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">Click to upload image</p>
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WEBP (max 5MB)</p>
                        <input type="file" name="land_image" id="land_image" accept="image/*" class="hidden" onchange="previewImage(this)" />
                    </div>
                    <!-- Image Preview -->
                    <div id="imagePreviewContainer" class="mt-4 hidden">
                        <p class="text-xs text-gray-500 mb-2">Preview:</p>
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
                        <span id="summaryLandName" class="font-semibold text-gray-800">—</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-500">Image</span>
                        <div class="flex flex-col items-end">
                            <span id="summaryImageStatus" class="font-semibold text-gray-800">Not uploaded</span>
                            <span id="summaryFileName" class="text-xs text-gray-500 truncate max-w-[120px]">—</span>
                        </div>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Notes</span>
                        <span id="summaryNotes" class="font-semibold text-gray-800">—</span>
                    </div>
                    
                    <!-- Image Preview in Summary -->
                    <div id="summaryImagePreview" class="mt-4 hidden">
                        <p class="text-xs text-gray-500 mb-2">Image Preview:</p>
                        <div class="relative">
                            <img id="summaryPreviewImage" class="w-full h-48 object-cover rounded-lg border border-gray-200">
                            <div id="imageDimensions" class="absolute bottom-2 right-2 bg-black/70 text-white text-xs px-2 py-1 rounded">
                                0 x 0
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500 flex justify-between">
                            <span id="fileSize">0 KB</span>
                            <span id="imageType">—</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-md hover:shadow-lg">
                <i class="fas fa-save"></i> Create Land Image
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
// ─── Live Summary Update ───
document.addEventListener("input", function(e) {
    const target = e.target;
    if (target.name === "land_name") {
        document.getElementById("summaryLandName").textContent = target.value || "—";
    }
    if (target.name === "notes") {
        const note = target.value || "—";
        document.getElementById("summaryNotes").textContent = note.length > 30 ? note.substring(0, 30) + "..." : note;
    }
});

// ─── Image Preview Function ───
function previewImage(input) {
    const container = document.getElementById("imagePreviewContainer");
    const preview = document.getElementById("previewImage");
    const summaryContainer = document.getElementById("summaryImagePreview");
    const summaryPreview = document.getElementById("summaryPreviewImage");
    const fileSizeSpan = document.getElementById("fileSize");
    const imageTypeSpan = document.getElementById("imageType");
    const dimensionsSpan = document.getElementById("imageDimensions");
    const summaryStatus = document.getElementById("summaryImageStatus");
    const summaryFileName = document.getElementById("summaryFileName");
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            // Main preview
            preview.src = e.target.result;
            container.classList.remove("hidden");
            
            // Summary preview
            summaryPreview.src = e.target.result;
            summaryContainer.classList.remove("hidden");
            
            // Update status
            summaryStatus.textContent = "Uploaded";
            summaryStatus.className = "font-semibold text-green-600";
            summaryFileName.textContent = file.name;
            
            // Get image dimensions
            const img = new Image();
            img.onload = function() {
                dimensionsSpan.textContent = `${this.width} x ${this.height}`;
            };
            img.src = e.target.result;
            
            // File size
            const fileSizeKB = (file.size / 1024).toFixed(2);
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            fileSizeSpan.textContent = fileSizeMB > 1 ? 
                `${fileSizeMB} MB` : `${fileSizeKB} KB`;
            
            // File type
            imageTypeSpan.textContent = file.type.split('/')[1].toUpperCase();
        };
        
        reader.readAsDataURL(file);
    } else {
        // If no file selected
        container.classList.add("hidden");
        summaryContainer.classList.add("hidden");
        summaryStatus.textContent = "Not uploaded";
        summaryStatus.className = "font-semibold text-gray-800";
        summaryFileName.textContent = "—";
    }
}

// ─── Drag and Drop Functionality ───
document.addEventListener('DOMContentLoaded', function() {
    const uploadArea = document.querySelector('.border-dashed');
    const fileInput = document.getElementById('land_image');
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        uploadArea.classList.add('border-green-500', 'bg-green-50');
    }
    
    function unhighlight() {
        uploadArea.classList.remove('border-green-500', 'bg-green-50');
    }
    
    // Handle dropped files
    uploadArea.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            previewImage(fileInput);
        }
    }
});

// ─── Form Validation ───
document.querySelector('form').addEventListener('submit', function(e) {
    const landName = document.querySelector('input[name="land_name"]').value.trim();
    const fileInput = document.getElementById('land_image');
    
    if (!landName) {
        e.preventDefault();
        alert('Please enter Land Name');
        return;
    }
    
    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const maxSize = 5 * 1024 * 1024; // 5MB in bytes
        
        if (file.size > maxSize) {
            e.preventDefault();
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            return;
        }
        
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            e.preventDefault();
            alert('Invalid file type. Please upload JPG, PNG, GIF or WEBP image.');
            return;
        }
    }
});
</script>
@endpush