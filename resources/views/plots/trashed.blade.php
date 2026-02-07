@extends('layouts.dashboard')
@section('title', 'Trash')

@section('content')
@php $breadcrumbs = [['name' => 'Plots', 'url' => route('plots.index')], ['name' => 'Trash', 'url' => null]]; @endphp

<!-- ─── TOP BAR ─── -->
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-trash-alt text-red-600"></i>
        </div>
        <div>
            <h2 class="text-lg font-bold text-gray-800">Trash</h2>
            <p class="text-xs text-gray-500">Items here will be permanently deleted after 30 days</p>
        </div>
        <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $plots->count() }} items</span>
    </div>
    <div class="flex items-center gap-2">
        <!-- Bulk Actions -->
        <div id="bulkActions" class="hidden items-center gap-2">
            <span id="selectedCount" class="text-xs text-gray-500 font-semibold">0 selected</span>
            <button onclick="bulkRestore()" class="flex items-center gap-1 text-xs font-semibold text-white bg-green-500 hover:bg-green-600 px-3 py-1.5 rounded-lg transition">
                <i class="fas fa-redo"></i> Restore
            </button>
            <button onclick="bulkForceDelete()" class="flex items-center gap-1 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg transition">
                <i class="fas fa-times-circle"></i> Delete Forever
            </button>
            <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
        </div>
        <!-- Empty Trash -->
        @if($plots->count() > 0)
        <button onclick="confirmEmptyTrash()" class="flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 px-3 py-2 rounded-lg transition">
            <i class="fas fa-times-circle"></i> Empty Trash
        </button>
        @endif
    </div>
</div>

<!-- ─── INFO BANNER ─── -->
<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 mb-5 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500 mt-0.5"></i>
    <div>
        <p class="text-sm text-amber-800 font-semibold">Trash Items</p>
        <p class="text-xs text-amber-600">These plots have been moved to trash. You can restore them or permanently delete them. Once permanently deleted, they cannot be recovered.</p>
    </div>
</div>

<!-- ─── TRASHED PLOTS TABLE ─── -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" id="selectAll" class="rounded" onchange="selectAllPlots(this)" />
                    </th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Plot ID</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Area</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Category</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Road</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Deleted</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-500 text-xs uppercase">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plots as $plot)
                <tr class="border-t border-gray-100 hover:bg-red-50/30 transition bg-red-50/10">
                    <td class="px-4 py-3">
                        <input type="checkbox" class="plot-checkbox rounded" value="{{ $plot->id }}" onchange="updateBulkActions()" />
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                                <i class="fas fa-trash-alt text-red-400 text-sm"></i>
                            </div>
                            <span class="font-bold text-gray-700 line-through">{{ $plot->plot_id }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 line-through">{{ number_format($plot->area, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold opacity-60 {{ $plot->getCategoryColor() }}">{{ $plot->category }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold opacity-60 {{ $plot->getStatusColor() }}">{{ ucfirst(str_replace('_', ' ', $plot->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $plot->road }}</td>
                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $plot->deleted_at->diffForHumans() }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1.5">
                            <!-- Restore -->
                            <form action="{{ route('plots.restore', $plot->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-1.5 text-green-600 hover:bg-green-100 rounded-lg transition" title="Restore">
                                    <i class="fas fa-redo text-sm"></i>
                                </button>
                            </form>
                            <!-- Force Delete -->
                            <button onclick="confirmForceDelete({{ $plot->id }}, '{{ $plot->plot_id }}')"
                                class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Delete Forever">
                                <i class="fas fa-times-circle text-sm"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center">
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">Trash is empty!</p>
                            <p class="text-gray-400 text-xs">No deleted plots to recover.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ─── HIDDEN FORMS ─── -->
<form id="bulkRestoreForm" method="POST" action="{{ route('plots.multipleRestore') }}" class="hidden">
    @csrf
</form>
<form id="bulkForceDeleteForm" method="POST" action="{{ route('plots.multipleForceDelete') }}" class="hidden">
    @csrf
</form>

@endsection

@push('scripts')
<script>
function selectAllPlots(master) {
    document.querySelectorAll(".plot-checkbox").forEach(cb => cb.checked = master.checked);
    updateBulkActions();
}

function updateBulkActions() {
    const checked = document.querySelectorAll(".plot-checkbox:checked");
    const bar = document.getElementById("bulkActions");
    const countEl = document.getElementById("selectedCount");
    if (checked.length > 0) {
        bar.classList.remove("hidden"); bar.classList.add("flex");
        countEl.textContent = checked.length + " selected";
    } else {
        bar.classList.add("hidden"); bar.classList.remove("flex");
    }
    document.getElementById("selectAll").checked = checked.length === document.querySelectorAll(".plot-checkbox").length && checked.length > 0;
}

function clearSelection() {
    document.querySelectorAll(".plot-checkbox").forEach(cb => cb.checked = false);
    document.getElementById("selectAll").checked = false;
    updateBulkActions();
}

// ─── Force Delete (Single) ───
function confirmForceDelete(id, name) {
    showConfirmModal(
        "⚠️ Permanently Delete",
        "\"" + name + "\" will be permanently deleted. This action CANNOT be undone!",
        function() {
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "{{ route('plots.forceDelete', '__ID__') }}".replace("__ID__", id);
            form.innerHTML = `<input type="hidden" name="_csrf-token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        },
        "Delete Forever",
        "bg-red-600 hover:bg-red-700"
    );
}

// ─── Bulk Restore ───
function bulkRestore() {
    const ids = Array.from(document.querySelectorAll(".plot-checkbox:checked")).map(cb => cb.value);
    if (!ids.length) return;
    showConfirmModal(
        "Restore Plots",
        "Restore " + ids.length + " plot(s) from trash?",
        function() {
            const form = document.getElementById("bulkRestoreForm");
            form.querySelectorAll(".id-input").forEach(el => el.remove());
            ids.forEach(id => {
                const input = document.createElement("input");
                input.type = "hidden"; input.name = "ids[]"; input.value = id; input.classList.add("id-input");
                form.appendChild(input);
            });
            form.submit();
        },
        "Restore " + ids.length,
        "bg-green-500 hover:bg-green-600"
    );
}

// ─── Bulk Force Delete ───
function bulkForceDelete() {
    const ids = Array.from(document.querySelectorAll(".plot-checkbox:checked")).map(cb => cb.value);
    if (!ids.length) return;
    showConfirmModal(
        "⚠️ Permanently Delete",
        "Permanently delete " + ids.length + " plot(s)? This CANNOT be undone!",
        function() {
            const form = document.getElementById("bulkForceDeleteForm");
            form.querySelectorAll(".id-input").forEach(el => el.remove());
            ids.forEach(id => {
                const input = document.createElement("input");
                input.type = "hidden"; input.name = "ids[]"; input.value = id; input.classList.add("id-input");
                form.appendChild(input);
            });
            form.submit();
        },
        "Delete Forever",
        "bg-red-600 hover:bg-red-700"
    );
}

// ─── Empty Trash (All) ───
function confirmEmptyTrash() {
    const allIds = Array.from(document.querySelectorAll(".plot-checkbox")).map(cb => cb.value);
    showConfirmModal(
        "⚠️ Empty Trash",
        "All " + allIds.length + " plot(s) will be permanently deleted. This CANNOT be undone!",
        function() {
            const form = document.getElementById("bulkForceDeleteForm");
            form.querySelectorAll(".id-input").forEach(el => el.remove());
            allIds.forEach(id => {
                const input = document.createElement("input");
                input.type = "hidden"; input.name = "ids[]"; input.value = id; input.classList.add("id-input");
                form.appendChild(input);
            });
            form.submit();
        },
        "Empty Trash",
        "bg-red-600 hover:bg-red-700"
    );
}
</script>
@endpush