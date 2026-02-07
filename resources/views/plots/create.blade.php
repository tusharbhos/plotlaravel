@extends('layouts.dashboard')
@section('title', 'Create Plot')

@section('content')
@php $breadcrumbs = [['name' => 'Plots', 'url' => route('plots.index')], ['name' => 'Create Plot', 'url' => null]]; @endphp

<!-- ─── TOP BAR ─── -->
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-800">Create New Plot</h2>
    <a href="{{ route('plots.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left text-xs"></i> Back to All Plots
    </a>
</div>

<form method="POST" action="{{ route('plots.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ─── LEFT + CENTER: Main Form ─── -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Plot Details Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-600"></i>
                    <h3 class="font-bold text-gray-700 text-sm">Plot Details</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Plot ID -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Plot ID <span class="text-red-500">*</span></label>
                        <input type="text" name="plot_id" value="{{ old('plot_id') }}"
                            placeholder="e.g. Plot 101"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 {{ $errors->has('plot_id') ? 'border-red-400' : '' }}" required />
                        @error('plot_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <!-- Plot Type -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Plot Type <span class="text-red-500">*</span></label>
                        <select name="plot_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="Land parcel" {{ old('plot_type') === 'Land parcel' ? 'selected' : '' }}>Land Parcel</option>
                            <option value="Residential" {{ old('plot_type') === 'Residential' ? 'selected' : '' }}>Residential</option>
                            <option value="Commercial" {{ old('plot_type') === 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        </select>
                    </div>
                    <!-- Area -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Area (Sq.Ft) <span class="text-red-500">*</span></label>
                        <input type="number" name="area" value="{{ old('area') }}" step="0.01" min="0"
                            placeholder="e.g. 5035.46"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" required />
                    </div>
                    <!-- FSI -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">FSI <span class="text-red-500">*</span></label>
                        <input type="number" name="fsi" value="{{ old('fsi', '1.1') }}" step="0.1" min="0"
                            placeholder="1.1"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" required />
                    </div>
                    <!-- Permissible Area -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Permissible Area</label>
                        <input type="number" name="permissible_area" value="{{ old('permissible_area') }}" step="0.01" min="0"
                            placeholder="e.g. 505.46"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400"  />
                    </div>
                    <!-- RL -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">RL (Reduced Level)</label>
                        <input type="text" name="rl" value="{{ old('rl') }}"
                            placeholder="e.g. RL 150.5"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" />
                    </div>
                    <!-- Road -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Road Width <span class="text-red-500">*</span></label>
                        <select name="road" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="">Select Road</option>
                            <option value="12MTR" {{ old('road') === '12MTR' ? 'selected' : '' }}>12 MTR</option>
                            <option value="15 MTR" {{ old('road') === '15 MTR' ? 'selected' : '' }}>15 MTR</option>
                            <option value="18MTR" {{ old('road') === '18MTR' ? 'selected' : '' }}>18 MTR</option>
                            <option value="24MTR" {{ old('road') === '24MTR' ? 'selected' : '' }}>24 MTR</option>
                        </select>
                    </div>
                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="available" {{ old('status') === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="sold" {{ old('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="booked" {{ old('status') === 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="under_review" {{ old('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        </select>
                    </div>
                    <!-- Category -->
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Category <span class="text-red-500">*</span></label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="PREMIUM" {{ old('category') === 'PREMIUM' ? 'selected' : '' }}>PREMIUM</option>
                            <option value="STANDARD" {{ old('category') === 'STANDARD' ? 'selected' : '' }}>STANDARD</option>
                            <option value="ECO" {{ old('category') === 'ECO' ? 'selected' : '' }}>ECO</option>
                        </select>
                    </div>
                </div>

                <!-- Corner + Garden Toggles -->
                <div class="px-5 pb-5 flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="corner" class="rounded text-indigo-600" {{ old('corner') ? 'checked' : '' }} />
                        <span class="text-sm text-gray-600 font-medium">Corner Plot</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="garden" class="rounded text-indigo-600" {{ old('garden') ? 'checked' : '' }} />
                        <span class="text-sm text-gray-600 font-medium">Garden Plot</span>
                    </label>
                </div>

                <!-- Notes -->
                <div class="px-5 pb-5">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" placeholder="Additional notes about this plot..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Polygon Points Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-indigo-50 border-b border-indigo-100 px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-map-pin text-indigo-600"></i>
                        <h3 class="font-bold text-indigo-700 text-sm">Polygon Points</h3>
                        <span id="pointCount" class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-0.5 rounded-full">0 points</span>
                    </div>
                    <button type="button" onclick="addPoint()" class="flex items-center gap-1 text-xs font-semibold text-indigo-600 bg-white border border-indigo-200 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                        <i class="fas fa-plus"></i> Add Point
                    </button>
                </div>
                <div class="p-5">
                    <!-- Points Container -->
                    <div id="pointsContainer" class="space-y-2">
                        <!-- Initial point row -->
                        <div class="point-row flex items-center gap-3 bg-gray-50 rounded-lg p-3" data-index="0">
                            <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs flex-shrink-0">1</div>
                            <div class="flex-1 flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-400 mb-0.5">X Coordinate</label>
                                    <input type="number" name="points[0][x]" step="0.1" placeholder="X"
                                        class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" />
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-400 mb-0.5">Y Coordinate</label>
                                    <input type="number" name="points[0][y]" step="0.1" placeholder="Y"
                                        class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" />
                                </div>
                            </div>
                            <button type="button" class="text-gray-400 hover:text-red-500 transition remove-point-btn" onclick="removePoint(this)" title="Remove">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-3">Add at least 3 points to form a polygon. Points are saved in order.</p>
                </div>
            </div>

            <!-- Images Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-green-50 border-b border-green-100 px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-image text-green-600"></i>
                    <h3 class="font-bold text-green-700 text-sm">Plot Images (Optional)</h3>
                </div>
                <div class="p-5">
                    <p class="text-xs text-gray-400 mb-3">You can add images after creating the plot from the Edit page.</p>
                    <!-- File Upload -->
                    <div class="border-2 border-dashed border-gray-200 rounded-lg p-4 text-center hover:border-green-400 transition cursor-pointer" onclick="document.getElementById('imageFilesInput').click()">
                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-1"></i>
                        <p class="text-sm text-gray-500">Click to upload images</p>
                        <p class="text-xs text-gray-400">JPG, PNG, GIF (max 5MB each)</p>
                        <input type="file" name="image_files[]" id="imageFilesInput" multiple accept="image/*" class="hidden" onchange="previewImages(this)" />
                    </div>
                    <div id="imagePreviewContainer" class="mt-3 flex flex-wrap gap-2"></div>
                </div>
            </div>
        </div>

        <!-- ─── RIGHT: Sidebar Summary ─── -->
        <div class="lg:col-span-1 space-y-5">
            <!-- Summary Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-900 text-white px-5 py-3">
                    <h3 class="font-bold text-sm">Plot Summary</h3>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Plot ID</span>
                        <span id="summaryPlotId" class="font-semibold text-gray-800">—</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Area</span>
                        <span id="summaryArea" class="font-semibold text-gray-800">—</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Perm. Area</span>
                        <span id="summaryPermArea" class="font-semibold text-gray-800">—</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Polygon Pts</span>
                        <span id="summaryPoints" class="font-semibold text-gray-800">0</span>
                    </div>
                    <hr class="border-gray-100" />
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Status</span>
                        <span id="summaryStatus" class="font-semibold text-green-600">Available</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Category</span>
                        <span id="summaryCategory" class="font-semibold text-purple-600">PREMIUM</span>
                    </div>
                </div>
            </div>

            <!-- SVG Preview -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-vector-square text-indigo-600"></i>
                    <h3 class="font-bold text-gray-700 text-sm">Polygon Preview</h3>
                </div>
                <div class="p-4 flex justify-center">
                    <svg id="polygonPreview" viewBox="0 0 100 100" class="w-full max-w-xs border border-gray-100 rounded-lg bg-gray-50" style="max-height:200px;">
                        <defs>
                            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                                <path d="M 10 0 L 0 0 0 10" fill="none" stroke="#e5e7eb" stroke-width="0.3"/>
                            </pattern>
                        </defs>
                        <rect width="100" height="100" fill="url(#grid)" />
                        <polygon id="previewPolygon" points="" fill="rgba(99,102,241,0.15)" stroke="#6366f1" stroke-width="1" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-md hover:shadow-lg">
                <i class="fas fa-save"></i> Create Plot
            </button>
            <a href="{{ route('plots.index') }}" class="w-full flex items-center justify-center gap-2 text-gray-600 hover:text-gray-800 text-sm py-2 transition">
                <i class="fas fa-times text-xs"></i> Cancel
            </a>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
let pointIndex = 1; // starts at 1 since we have 1 point already

// ─── Add Point ───
function addPoint() {
    const container = document.getElementById("pointsContainer");
    const row = document.createElement("div");
    row.className = "point-row flex items-center gap-3 bg-gray-50 rounded-lg p-3";
    row.dataset.index = pointIndex;
    row.innerHTML = `
        <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs flex-shrink-0">${pointIndex + 1}</div>
        <div class="flex-1 flex items-center gap-2">
            <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-0.5">X Coordinate</label>
                <input type="number" name="points[${pointIndex}][x]" step="0.1" placeholder="X"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400"
                    oninput="updatePreview()" />
            </div>
            <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-0.5">Y Coordinate</label>
                <input type="number" name="points[${pointIndex}][y]" step="0.1" placeholder="Y"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400"
                    oninput="updatePreview()" />
            </div>
        </div>
        <button type="button" class="text-gray-400 hover:text-red-500 transition remove-point-btn" onclick="removePoint(this)" title="Remove">
            <i class="fas fa-times text-sm"></i>
        </button>
    `;
    container.appendChild(row);
    pointIndex++;
    updatePointCount();
}

// ─── Remove Point ───
function removePoint(btn) {
    const row = btn.closest(".point-row");
    row.remove();
    rebuildPointNames();
    updatePointCount();
    updatePreview();
}

// ─── Rebuild point name indices ───
function rebuildPointNames() {
    const rows = document.querySelectorAll(".point-row");
    rows.forEach((row, i) => {
        row.dataset.index = i;
        const badge = row.querySelector(".rounded-full");
        if (badge) badge.textContent = (i + 1);
        const xInput = row.querySelector("input[name*=\"[x]\"]");
        const yInput = row.querySelector("input[name*=\"[y]\"]");
        if (xInput) xInput.setAttribute("name", "points[" + i + "][x]");
        if (yInput) yInput.setAttribute("name", "points[" + i + "][y]");
    });
}

// ─── Update Point Count Badge ───
function updatePointCount() {
    const count = document.querySelectorAll(".point-row").length;
    document.getElementById("pointCount").textContent = count + " points";
    document.getElementById("summaryPoints").textContent = count;
}

// ─── Live Summary Update ───
document.addEventListener("input", function(e) {
    const target = e.target;
    if (target.name === "plot_id")    document.getElementById("summaryPlotId").textContent = target.value || "—";
    if (target.name === "area")       { document.getElementById("summaryArea").textContent = target.value ? Number(target.value).toLocaleString() : "—"; calcPermArea(); }
    if (target.name === "fsi")        calcPermArea();
    if (target.name === "status")     document.getElementById("summaryStatus").textContent = target.options[target.selectedIndex].text;
    if (target.name === "category")   document.getElementById("summaryCategory").textContent = target.value;
    if (target.name.includes("points["))  updatePreview();
});



// ─── SVG Polygon Preview ───
function updatePreview() {
    const inputs = document.querySelectorAll(".point-row");
    let points = [];
    inputs.forEach(row => {
        const x = parseFloat(row.querySelector("input[name*=\"[x]\"]")?.value);
        const y = parseFloat(row.querySelector("input[name*=\"[y]\"]")?.value);
        if (!isNaN(x) && !isNaN(y)) points.push({x, y});
    });

    if (points.length < 2) {
        document.getElementById("previewPolygon").setAttribute("points", "");
        return;
    }

    // Normalize to 0-100 viewBox
    const minX = Math.min(...points.map(p => p.x));
    const maxX = Math.max(...points.map(p => p.x));
    const minY = Math.min(...points.map(p => p.y));
    const maxY = Math.max(...points.map(p => p.y));
    const rangeX = maxX - minX || 1;
    const rangeY = maxY - minY || 1;
    const scale = Math.min(80 / rangeX, 80 / rangeY);

    const normalized = points.map(p => ({
        x: 10 + (p.x - minX) * scale,
        y: 10 + (p.y - minY) * scale,
    }));

    const svgPoints = normalized.map(p => p.x + "," + p.y).join(" ");
    document.getElementById("previewPolygon").setAttribute("points", svgPoints);
}

// ─── Image Preview ───
function previewImages(input) {
    const container = document.getElementById("imagePreviewContainer");
    Array.from(input.files).forEach((file, i) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const div = document.createElement("div");
            div.className = "relative";
            div.innerHTML = `
                <img src="${e.target.result}" class="w-16 h-16 object-cover rounded-lg border border-gray-200" />
                <input type="text" name="new_image_names[${i}]" value="${file.name.split('.')[0]}" class="block w-16 text-xs text-center border border-gray-200 rounded mt-1 px-1 py-0.5" />
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

// Init
updatePointCount();
</script>
@endpush