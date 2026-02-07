@extends('layouts.dashboard')
@section('title', 'All Plots')

@section('content')
@php $breadcrumbs = [['name' => 'Plots', 'url' => route('plots.index')], ['name' => 'All Plots', 'url' => null]]; @endphp

<!-- ─── TOP BAR: Actions + Filters ─── -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <!-- Left: Title + Bulk Actions -->
    <div class="flex items-center gap-3 flex-wrap">
        <h2 class="text-lg font-bold text-gray-800">All Plots</h2>
        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $plots->count() }} plots</span>

        <!-- Bulk Delete Button (hidden until selection) -->
        <div id="bulkActions" class="hidden items-center gap-2">
            <span id="selectedCount" class="text-xs text-gray-500 font-semibold">0 selected</span>
            <button onclick="bulkDelete()" class="flex items-center gap-1 text-xs font-semibold text-white bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg transition">
                <i class="fas fa-trash-alt"></i> Delete Selected
            </button>
            <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
        </div>
    </div>

    <!-- Right: Action Buttons -->
    <div class="flex items-center gap-2 flex-wrap">
        <!-- Import -->
        <a href="{{ route('plots.import.form') }}" class="flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 px-3 py-2 rounded-lg transition">
            <i class="fas fa-file-import"></i> Import
        </a>
        <!-- Export -->
        <a href="{{ route('plots.export') }}" class="flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-50 border border-green-200 hover:bg-green-100 px-3 py-2 rounded-lg transition">
            <i class="fas fa-file-export"></i> Export
        </a>
        <!-- Add Plot -->
        <a href="{{ route('plots.create') }}" class="flex items-center gap-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg transition shadow-sm">
            <i class="fas fa-plus"></i> Add Plot
        </a>
    </div>
</div>

<!-- ─── SEARCH + FILTERS ─── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5">
    <form method="GET" action="{{ route('plots.index') }}" class="flex flex-col sm:flex-row gap-3 items-end">
        <!-- Search -->
        <div class="flex-1">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Search Plot ID</label>
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ $searchQuery }}" placeholder="e.g. Plot 9"
                    class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400" />
            </div>
        </div>
        <!-- Status Filter -->
        <div class="w-40">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white">
                <option value="" {{ $statusFilter === '' ? 'selected' : '' }}>All Status</option>
                <option value="available" {{ $statusFilter === 'available' ? 'selected' : '' }}>Available</option>
                <option value="sold" {{ $statusFilter === 'sold' ? 'selected' : '' }}>Sold</option>
                <option value="booked" {{ $statusFilter === 'booked' ? 'selected' : '' }}>Booked</option>
                <option value="under_review" {{ $statusFilter === 'under_review' ? 'selected' : '' }}>Under Review</option>
            </select>
        </div>
        <!-- Category Filter -->
        <div class="w-40">
            <label class="block text-xs font-semibold text-gray-500 mb-1 uppercase">Category</label>
            <select name="category" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-indigo-400 bg-white">
                <option value="" {{ $categoryFilter === '' ? 'selected' : '' }}>All Category</option>
                <option value="PREMIUM" {{ $categoryFilter === 'PREMIUM' ? 'selected' : '' }}>PREMIUM</option>
                <option value="STANDARD" {{ $categoryFilter === 'STANDARD' ? 'selected' : '' }}>STANDARD</option>
                <option value="ECO" {{ $categoryFilter === 'ECO' ? 'selected' : '' }}>ECO</option>
            </select>
        </div>
        <!-- Search Button -->
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <!-- Clear -->
        @if($statusFilter || $categoryFilter || $searchQuery)
        <a href="{{ route('plots.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-red-600 transition">
            <i class="fas fa-times"></i> Clear
        </a>
        @endif
    </form>
</div>

<!-- ─── PLOTS TABLE ─── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" id="selectAll" class="rounded" onchange="selectAllPlots(this)" />
                    </th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Image</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Plot ID</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Area</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">FSI</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Perm. Area</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Road</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Corner</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Points</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plots as $plot)
                <tr class="border-t border-gray-100 hover:bg-gray-50 transition" data-id="{{ $plot->id }}">
                    <!-- Checkbox -->
                    <td class="px-4 py-3">
                        <input type="checkbox" class="plot-checkbox rounded" value="{{ $plot->id }}" onchange="updateBulkActions()" />
                    </td>
                    <!-- Image -->
                    <td class="px-4 py-3">
                        @if($plot->primaryImage)
                            <img src="{{ $plot->primaryImage->getImageUrl() }}" alt="{{ $plot->plot_id }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200" onerror="this.src=''" />
                        @else
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-image text-gray-400 text-sm"></i>
                            </div>
                        @endif
                    </td>
                    <!-- Plot ID -->
                    <td class="px-4 py-3 font-bold text-gray-800">{{ $plot->plot_id }}</td>
                    <!-- Area -->
                    <td class="px-4 py-3 text-gray-600">{{ number_format($plot->area, 2) }}</td>
                    <!-- FSI -->
                    <td class="px-4 py-3 text-gray-600">{{ $plot->fsi }}</td>
                    <!-- Permissible Area -->
                    <td class="px-4 py-3 text-gray-600">{{ number_format($plot->permissible_area, 2) }}</td>
                    <!-- Category -->
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $plot->getCategoryColor() }}">{{ $plot->category }}</span>
                    </td>
                    <!-- Status -->
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $plot->getStatusColor() }}">{{ ucfirst(str_replace('_', ' ', $plot->status)) }}</span>
                    </td>
                    <!-- Road -->
                    <td class="px-4 py-3 text-gray-600 text-sm">{{ $plot->road }}</td>
                    <!-- Corner -->
                    <td class="px-4 py-3">
                        @if($plot->corner)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-teal-100 text-teal-700"><i class="fas fa-check mr-1 text-xs"></i>Yes</span>
                        @else
                            <span class="text-gray-400 text-xs">No</span>
                        @endif
                    </td>
                    <!-- Points Count -->
                    <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $plot->points->count() }} pts</td>
                    <!-- Actions -->
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <!-- Edit -->
                            <a href="{{ route('plots.edit', $plot->id) }}" class="p-1.5 text-indigo-600 hover:bg-indigo-100 rounded-lg transition" title="Edit">
                                <i class="fas fa-pencil-alt text-sm"></i>
                            </a>
                            <!-- Delete (to Trash) -->
                            <button onclick="confirmDelete({{ $plot->id }}, '{{ $plot->plot_id }}')"
                                class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition" title="Move to Trash">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-th-large text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">No plots found</p>
                            <p class="text-gray-400 text-xs">Try clearing your filters or <a href="{{ route('plots.create') }}" class="text-indigo-600 hover:underline">create a new plot</a></p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ─── HIDDEN FORM for Bulk Delete ─── -->
<form id="bulkDeleteForm" method="POST" action="{{ route('plots.multipleDelete') }}" class="hidden">
    @csrf
    <!-- IDs will be injected via JS -->
</form>

@endsection

@push('scripts')
<script>
// ─── Select All ───
function selectAllPlots(master) {
    document.querySelectorAll('.plot-checkbox').forEach(cb => cb.checked = master.checked);
    updateBulkActions();
}

// ─── Update bulk actions bar ───
function updateBulkActions() {
    const checked = document.querySelectorAll('.plot-checkbox:checked');
    const bar = document.getElementById('bulkActions');
    const countEl = document.getElementById('selectedCount');

    if (checked.length > 0) {
        bar.classList.remove('hidden');
        bar.classList.add('flex');
        countEl.textContent = checked.length + ' selected';
    } else {
        bar.classList.add('hidden');
        bar.classList.remove('flex');
    }

    const all = document.querySelectorAll('.plot-checkbox');
    document.getElementById('selectAll').checked =
        checked.length === all.length && all.length > 0;
}

// ─── Clear Selection ───
function clearSelection() {
    document.querySelectorAll('.plot-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

// ─── Single Delete Confirm ───
function confirmDelete(id, name) {
    showConfirmModal(
        "Move to Trash",
        `Are you sure you want to move "${name}" to trash? You can restore it later.`,
        function () {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "{{ route('plots.destroy', '__ID__') }}".replace("__ID__", id);
            form.innerHTML = `
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        },
        "Move to Trash",
        "bg-red-500 hover:bg-red-600"
    );
}

// ─── Bulk Delete ───
function bulkDelete() {
    const checked = document.querySelectorAll(".plot-checkbox:checked");
    const ids = Array.from(checked).map(cb => cb.value);

    if (!ids.length) return;

    showConfirmModal(
        "Bulk Delete",
        `Move ${ids.length} plot(s) to trash? You can restore them later.`,
        function () {
            const form = document.getElementById("bulkDeleteForm");
            form.querySelectorAll(".id-input").forEach(el => el.remove());

            ids.forEach(id => {
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "ids[]";
                input.value = id;
                input.classList.add("id-input");
                form.appendChild(input);
            });

            form.submit();
        },
        `Delete ${ids.length} Plot(s)`,
        "bg-red-500 hover:bg-red-600"
    );
}
</script>
@endpush