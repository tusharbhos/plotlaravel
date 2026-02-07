@extends('layouts.dashboard')
@section('title', 'Edit ' . $plot->plot_id)

@section('content')
@php $breadcrumbs = [['name' => 'Plots', 'url' => route('plots.index')], ['name' => 'Edit', 'url' => null], ['name' => $plot->plot_id, 'url' => null]]; @endphp

<!-- ─── TOP BAR ─── -->
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-bold text-gray-800">Edit Plot <span class="text-indigo-600">{{ $plot->plot_id }}</span></h2>
    <a href="{{ route('plots.index') }}" class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left text-xs"></i> Back
    </a>
</div>

<form method="POST" action="{{ route('plots.update', $plot->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ─── LEFT + CENTER ─── -->
        <div class="lg:col-span-2 space-y-5">

            <!-- Plot Details -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-100 px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-600"></i>
                    <h3 class="font-bold text-gray-700 text-sm">Plot Details</h3>
                </div>
                <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Plot ID *</label>
                        <input type="text" name="plot_id" value="{{ old('plot_id', $plot->plot_id) }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Plot Type *</label>
                        <select name="plot_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="Land parcel" {{ old('plot_type', $plot->plot_type) === 'Land parcel' ? 'selected' : '' }}>Land Parcel</option>
                            <option value="Residential" {{ old('plot_type', $plot->plot_type) === 'Residential' ? 'selected' : '' }}>Residential</option>
                            <option value="Commercial" {{ old('plot_type', $plot->plot_type) === 'Commercial' ? 'selected' : '' }}>Commercial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Area (Sq.Ft) *</label>
                        <input type="number" name="area" value="{{ old('area', $plot->area) }}" step="0.01" min="0"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">FSI *</label>
                        <input type="number" name="fsi" value="{{ old('fsi', $plot->fsi) }}" step="0.1" min="0"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Permissible Area</label>
                        <input type="number" name="permissible_area" id="permissibleArea" value="{{ old('permissible_area', $plot->permissible_area) }}" step="0.01" min="0"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-gray-50" readonly />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">RL</label>
                        <input type="text" name="rl" value="{{ old('rl', $plot->rl) }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Road Width *</label>
                        <select name="road" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="">Select Road</option>
                            <option value="12MTR" {{ old('road', $plot->road) === '12MTR' ? 'selected' : '' }}>12 MTR</option>
                            <option value="15 MTR" {{ old('road', $plot->road) === '15 MTR' ? 'selected' : '' }}>15 MTR</option>
                            <option value="18MTR" {{ old('road', $plot->road) === '18MTR' ? 'selected' : '' }}>18 MTR</option>
                            <option value="24MTR" {{ old('road', $plot->road) === '24MTR' ? 'selected' : '' }}>24 MTR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Status *</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="available" {{ old('status', $plot->status) === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="sold" {{ old('status', $plot->status) === 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="booked" {{ old('status', $plot->status) === 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="under_review" {{ old('status', $plot->status) === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Category *</label>
                        <select name="category" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white" required>
                            <option value="PREMIUM" {{ old('category', $plot->category) === 'PREMIUM' ? 'selected' : '' }}>PREMIUM</option>
                            <option value="STANDARD" {{ old('category', $plot->category) === 'STANDARD' ? 'selected' : '' }}>STANDARD</option>
                            <option value="ECO" {{ old('category', $plot->category) === 'ECO' ? 'selected' : '' }}>ECO</option>
                        </select>
                    </div>
                </div>
                <div class="px-5 pb-4 flex gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="corner" class="rounded text-indigo-600" {{ old('corner', $plot->corner) ? 'checked' : '' }} />
                        <span class="text-sm text-gray-600 font-medium">Corner Plot</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="garden" class="rounded text-indigo-600" {{ old('garden', $plot->garden) ? 'checked' : '' }} />
                        <span class="text-sm text-gray-600 font-medium">Garden Plot</span>
                    </label>
                </div>
                <div class="px-5 pb-5">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 resize-none">{{ old('notes', $plot->notes) }}</textarea>
                </div>
            </div>

            <!-- Polygon Points -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-indigo-50 border-b border-indigo-100 px-5 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-map-pin text-indigo-600"></i>
                        <h3 class="font-bold text-indigo-700 text-sm">Polygon Points</h3>
                        <span id="pointCount" class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-0.5 rounded-full">{{ $plot->points->count() }} points</span>
                    </div>
                    <button type="button" onclick="addPoint()" class="flex items-center gap-1 text-xs font-semibold text-indigo-600 bg-white border border-indigo-200 hover:bg-indigo-50 px-3 py-1.5 rounded-lg transition">
                        <i class="fas fa-plus"></i> Add Point
                    </button>
                </div>
                <div class="p-5">
                    <div id="pointsContainer" class="space-y-2">
                        @foreach($plot->points as $i => $pt)
                        <div class="point-row flex items-center gap-3 bg-gray-50 rounded-lg p-3">
                            <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs flex-shrink-0">{{ $i + 1 }}</div>
                            <div class="flex-1 flex items-center gap-2">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-400 mb-0.5">X</label>
                                    <input type="number" name="points[{{ $i }}][x]" value="{{ $pt->x }}" step="0.1"
                                        class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" oninput="updatePreview()" />
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-400 mb-0.5">Y</label>
                                    <input type="number" name="points[{{ $i }}][y]" value="{{ $pt->y }}" step="0.1"
                                        class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" oninput="updatePreview()" />
                                </div>
                            </div>
                            <button type="button" class="text-gray-400 hover:text-red-500 transition" onclick="removePoint(this)">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Images Management Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-green-50 border-b border-green-100 px-5 py-3 flex items-center gap-2">
                    <i class="fas fa-image text-green-600"></i>
                    <h3 class="font-bold text-green-700 text-sm">Plot Images</h3>
                    <span class="text-xs bg-green-100 text-green-600 font-bold px-2 py-0.5 rounded-full">{{ $plot->activeImages->count() }} images</span>
                </div>
                <div class="p-5">
                    <!-- Existing Images Grid -->
                    @if($plot->activeImages->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4">
                        @foreach($plot->activeImages as $img)
                        <div class="relative group rounded-lg overflow-hidden border border-gray-200 hover:border-indigo-300 transition">
                            <img src="{{ $img->getImageUrl() }}" alt="{{ $img->image_name }}"
                                class="w-full h-28 object-cover" onerror="this.style.display='none'" />
                            <!-- Overlay Actions -->
                            <div class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center gap-1 opacity-0 group-hover:opacity-100 transition">
                                @if(!$img->is_primary)
                                <form action="{{ route('plots.image.primary', $img->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-white bg-indigo-500 hover:bg-indigo-600 px-2 py-1 rounded">
                                        <i class="fas fa-star mr-1"></i> Set Primary
                                    </button>
                                </form>
                                @endif
                                <form action="{{ route('plots.image.delete', $img->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-white bg-red-500 hover:bg-red-600 px-2 py-1 rounded">
                                        <i class="fas fa-trash-alt mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                            <!-- Primary Badge -->
                            @if($img->is_primary)
                            <span class="absolute top-1 left-1 bg-indigo-600 text-white text-xs font-bold px-1.5 py-0.5 rounded">
                                <i class="fas fa-star text-yellow-300 mr-0.5"></i> Primary
                            </span>
                            @endif
                            <!-- Name -->
                            <div class="px-2 py-1 bg-white border-t border-gray-100">
                                <p class="text-xs text-gray-600 truncate">{{ $img->image_name }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- Add Image by URL -->
                    <div class="border border-gray-200 rounded-lg p-4 mb-3">
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Add Image by URL</h4>
                        <form action="{{ route('plots.image.add', $plot->id) }}" method="POST" class="flex gap-2">
                            @csrf
                            <input type="text" name="image_name" placeholder="Image name" class="flex-1 px-3 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" />
                            <input type="text" name="image_path" placeholder="Image URL or path" class="flex-1 px-3 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" />
                            <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-sm font-semibold rounded hover:bg-green-700 transition">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        </form>
                    </div>

                    <!-- Upload Image File -->
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="text-xs font-bold text-gray-500 uppercase mb-2">Upload Image File</h4>
                        <form action="{{ route('plots.image.upload', $plot->id) }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
                            @csrf
                            <input type="text" name="image_name" placeholder="Image name" class="flex-0 w-36 px-3 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" />
                            <input type="file" name="image_file" accept="image/*" class="flex-1 text-sm text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-600 hover:file:bg-indigo-200" required />
                            <button type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-semibold rounded hover:bg-indigo-700 transition">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── RIGHT: Summary + Submit ─── -->
        <div class="lg:col-span-1 space-y-5">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-900 text-white px-5 py-3 flex items-center justify-between">
                    <h3 class="font-bold text-sm">Plot Summary</h3>
                    <span class="text-xs bg-{{ $plot->status === 'available' ? 'green' : 'gray' }}-200 text-{{ $plot->status === 'available' ? 'green' : 'gray' }}-700 px-2 py-0.5 rounded-full font-semibold">{{ ucfirst(str_replace('_',' ',$plot->status)) }}</span>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Plot ID</span>
                        <span class="font-semibold text-gray-800">{{ $plot->plot_id }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Area</span>
                        <span class="font-semibold text-gray-800">{{ number_format($plot->area, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Perm. Area</span>
                        <span class="font-semibold text-gray-800">{{ number_format($plot->permissible_area, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Points</span>
                        <span class="font-semibold text-gray-800">{{ $plot->points->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Images</span>
                        <span class="font-semibold text-gray-800">{{ $plot->activeImages->count() }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Created</span>
                        <span class="font-semibold text-gray-600 text-xs">{{ $plot->created_at->diffForHumans() }}</span>
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

            <!-- Submit -->
            <button type="submit" class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-md hover:shadow-lg">
                <i class="fas fa-save"></i> Update Plot
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
let pointIndex = {{ $plot->points->count() }};

function addPoint() {
    const container = document.getElementById("pointsContainer");
    const row = document.createElement("div");
    row.className = "point-row flex items-center gap-3 bg-gray-50 rounded-lg p-3";
    row.innerHTML = `
        <div class="w-6 h-6 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xs flex-shrink-0">${pointIndex + 1}</div>
        <div class="flex-1 flex items-center gap-2">
            <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-0.5">X</label>
                <input type="number" name="points[${pointIndex}][x]" step="0.1" placeholder="X"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" oninput="updatePreview()" />
            </div>
            <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-0.5">Y</label>
                <input type="number" name="points[${pointIndex}][y]" step="0.1" placeholder="Y"
                    class="w-full px-2 py-1.5 border border-gray-200 rounded text-sm focus:outline-none focus:border-indigo-400" oninput="updatePreview()" />
            </div>
        </div>
        <button type="button" class="text-gray-400 hover:text-red-500 transition" onclick="removePoint(this)">
            <i class="fas fa-times text-sm"></i>
        </button>`;
    container.appendChild(row);
    pointIndex++;
    updatePointCount();
}

function removePoint(btn) {
    btn.closest(".point-row").remove();
    rebuildPointNames();
    updatePointCount();
    updatePreview();
}

function rebuildPointNames() {
    const rows = document.querySelectorAll(".point-row");
    rows.forEach((row, i) => {
        const badge = row.querySelector(".rounded-full");
        if (badge) badge.textContent = (i + 1);
        const xInput = row.querySelector("input[name*=\"[x]\"]");
        const yInput = row.querySelector("input[name*=\"[y]\"]");
        if (xInput) xInput.setAttribute("name", "points[" + i + "][x]");
        if (yInput) yInput.setAttribute("name", "points[" + i + "][y]");
    });
}

function updatePointCount() {
    const count = document.querySelectorAll(".point-row").length;
    document.getElementById("pointCount").textContent = count + " points";
}

document.addEventListener("input", function(e) {
    if (e.target.name === "area" || e.target.name === "fsi") calcPermArea();
    if (e.target.name.includes("points[")) updatePreview();
});

function calcPermArea() {
    const area = parseFloat(document.querySelector("[name=area]").value) || 0;
    const fsi  = parseFloat(document.querySelector("[name=fsi]").value) || 1.1;
    document.getElementById("permissibleArea").value = (area * fsi).toFixed(2);
}

function updatePreview() {
    const rows = document.querySelectorAll(".point-row");
    let points = [];
    rows.forEach(row => {
        const x = parseFloat(row.querySelector("input[name*=\"[x]\"]")?.value);
        const y = parseFloat(row.querySelector("input[name*=\"[y]\"]")?.value);
        if (!isNaN(x) && !isNaN(y)) points.push({x, y});
    });
    if (points.length < 2) { document.getElementById("previewPolygon").setAttribute("points", ""); return; }
    const minX = Math.min(...points.map(p=>p.x)), maxX = Math.max(...points.map(p=>p.x));
    const minY = Math.min(...points.map(p=>p.y)), maxY = Math.max(...points.map(p=>p.y));
    const scale = Math.min(80/(maxX-minX||1), 80/(maxY-minY||1));
    const norm = points.map(p => ({ x: 10+(p.x-minX)*scale, y: 10+(p.y-minY)*scale }));
    document.getElementById("previewPolygon").setAttribute("points", norm.map(p=>p.x+","+p.y).join(" "));
}

// Init preview on load
updatePreview();
</script>
@endpush