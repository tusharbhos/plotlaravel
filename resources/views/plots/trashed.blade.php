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
        <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $plots->total() }} items</span>
    </div>
    <div class="flex items-center gap-2">
        <!-- Bulk Actions -->
        <div id="bulkActions" class="hidden items-center gap-2">
            <span id="selectedCount" class="text-xs text-gray-500 font-semibold">0 selected</span>
            
            <!-- Select All Across Pages Option -->
            <div class="flex items-center gap-2 ml-2 pl-2 border-l border-gray-200">
                <input type="checkbox" id="selectAllAcrossPages" class="rounded text-indigo-600" onchange="toggleSelectAllAcrossPages(this)">
                <label for="selectAllAcrossPages" class="text-xs text-gray-600">Select all {{ $plots->total() }} trashed plots</label>
            </div>
            
            <button onclick="bulkRestore()" class="flex items-center gap-1 text-xs font-semibold text-white bg-green-500 hover:bg-green-600 px-3 py-1.5 rounded-lg transition">
                <i class="fas fa-redo"></i> Restore Selected
            </button>
            <button onclick="bulkForceDelete()" class="flex items-center gap-1 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg transition">
                <i class="fas fa-times-circle"></i> Delete Forever
            </button>
            <button onclick="clearSelection()" class="text-xs text-gray-500 hover:text-gray-700 underline">Clear</button>
        </div>
        <!-- Empty Trash -->
        @if($plots->total() > 0)
        <button onclick="confirmEmptyTrash()" class="flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 px-3 py-2 rounded-lg transition">
            <i class="fas fa-times-circle"></i> Empty Trash
        </button>
        @endif
        <!-- Back to Plots -->
        <a href="{{ route('plots.index') }}" class="flex items-center gap-1.5 text-xs font-semibold text-gray-600 bg-gray-50 border border-gray-200 hover:bg-gray-100 px-3 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i> Back to Plots
        </a>
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

<!-- ─── HIDDEN FORMS ─── -->
<form id="bulkRestoreForm" method="POST" action="{{ route('plots.multipleRestore') }}" class="hidden">
    @csrf
    <input type="hidden" name="all_selected" id="allSelectedInputRestore" value="false">
    <input type="hidden" name="except_ids" id="exceptIdsInputRestore" value="">
    <input type="hidden" name="redirect_url" value="{{ url()->full() }}">
</form>

<form id="bulkForceDeleteForm" method="POST" action="{{ route('plots.multipleForceDelete') }}" class="hidden">
    @csrf
    <input type="hidden" name="all_selected" id="allSelectedInputDelete" value="false">
    <input type="hidden" name="except_ids" id="exceptIdsInputDelete" value="">
    <input type="hidden" name="redirect_url" value="{{ url()->full() }}">
</form>

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
                <tr class="border-t border-gray-100 hover:bg-red-50/30 transition bg-red-50/10" data-id="{{ $plot->id }}">
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
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold opacity-60 
                            {{ $plot->category == 'PREMIUM' ? 'bg-purple-100 text-purple-700' : 'bg-green-100 text-green-700' }}">
                            {{ $plot->category }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold opacity-60
                            @if($plot->status == 'available') bg-green-100 text-green-700
                            @elseif($plot->status == 'sold') bg-red-100 text-red-700
                            @elseif($plot->status == 'booked') bg-blue-100 text-blue-700
                            @else bg-yellow-100 text-yellow-700
                            @endif">
                            {{ ucfirst(str_replace('_', ' ', $plot->status)) }}
                        </span>
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

<!-- ─── PAGINATION ─── -->
@if($plots->hasPages())
<div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 px-4">
    <div class="text-sm text-gray-500">
        Showing <span class="font-medium">{{ $plots->firstItem() }}</span> to <span class="font-medium">{{ $plots->lastItem() }}</span> of <span class="font-medium">{{ $plots->total() }}</span> trashed plots
    </div>

    <div class="flex items-center gap-1">
        {{-- Previous Page Link --}}
        @if ($plots->onFirstPage())
        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
            <i class="fas fa-chevron-left text-xs"></i>
        </span>
        @else
        <a href="{{ $plots->previousPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 hover:border-indigo-200 transition">
            <i class="fas fa-chevron-left text-xs"></i>
        </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($plots->getUrlRange(1, $plots->lastPage()) as $page => $url)
            @if ($page == $plots->currentPage())
                <span class="px-3 py-1.5 text-sm font-semibold text-white bg-red-600 rounded-lg">{{ $page }}</span>
            @elseif ($page === 1 || $page === $plots->lastPage() || ($page >= $plots->currentPage() - 2 && $page <= $plots->currentPage() + 2))
                <a href="{{ $url }}" class="px-3 py-1.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">{{ $page }}</a>
            @elseif ($page === $plots->currentPage() - 3 || $page === $plots->currentPage() + 3)
                <span class="px-3 py-1.5 text-sm text-gray-400">...</span>
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($plots->hasMorePages())
        <a href="{{ $plots->nextPageUrl() }}" class="px-3 py-1.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
            <i class="fas fa-chevron-right text-xs"></i>
        </a>
        @else
        <span class="px-3 py-1.5 text-sm text-gray-400 bg-gray-100 rounded-lg cursor-not-allowed">
            <i class="fas fa-chevron-right text-xs"></i>
        </span>
        @endif
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    // Store selected IDs across pagination
    let selectedIds = new Set();
    let isAllAcrossPages = false;
    
    // Initialize from localStorage if exists
    document.addEventListener('DOMContentLoaded', function() {
        const stored = localStorage.getItem('trash_selected_ids');
        if (stored) {
            try {
                const parsed = JSON.parse(stored);
                selectedIds = new Set(parsed);
                
                // Check checkboxes based on stored IDs
                document.querySelectorAll('.plot-checkbox').forEach(cb => {
                    if (selectedIds.has(cb.value)) {
                        cb.checked = true;
                    }
                });
                
                updateBulkActions();
            } catch(e) {
                console.error('Error loading stored selections', e);
            }
        }
        
        // Check if "Select All Across Pages" was previously selected
        const storedAllAcross = localStorage.getItem('trash_select_all_across');
        if (storedAllAcross === 'true') {
            isAllAcrossPages = true;
            const selectAllAcross = document.getElementById('selectAllAcrossPages');
            if (selectAllAcross) {
                selectAllAcross.checked = true;
            }
        }
    });

    // ─── Select All on Current Page ───
    function selectAllPlots(master) {
        document.querySelectorAll('.plot-checkbox').forEach(cb => {
            cb.checked = master.checked;
            const id = cb.value;
            if (master.checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
        });
        
        // If unchecking master, also uncheck "Select All Across Pages"
        if (!master.checked) {
            isAllAcrossPages = false;
            const selectAllAcross = document.getElementById('selectAllAcrossPages');
            if (selectAllAcross) {
                selectAllAcross.checked = false;
            }
            localStorage.setItem('trash_select_all_across', 'false');
        }
        
        saveSelection();
        updateBulkActions();
    }

    // ─── Toggle Select All Across Pages ───
    function toggleSelectAllAcrossPages(checkbox) {
        isAllAcrossPages = checkbox.checked;
        localStorage.setItem('trash_select_all_across', checkbox.checked ? 'true' : 'false');
        
        if (checkbox.checked) {
            // When selecting all across pages, check all visible checkboxes
            document.querySelectorAll('.plot-checkbox').forEach(cb => {
                cb.checked = true;
                selectedIds.add(cb.value);
            });
            document.getElementById('selectAll').checked = true;
        } else {
            // When unchecking, clear everything
            clearSelection();
        }
        
        saveSelection();
        updateBulkActions();
    }

    // ─── Update bulk actions bar ───
    function updateBulkActions() {
        const checked = document.querySelectorAll('.plot-checkbox:checked');
        const bar = document.getElementById('bulkActions');
        const countEl = document.getElementById('selectedCount');
        
        // Update selectedIds set based on current page checkboxes
        document.querySelectorAll('.plot-checkbox').forEach(cb => {
            const id = cb.value;
            if (cb.checked) {
                selectedIds.add(id);
            } else {
                if (selectedIds.has(id)) {
                    selectedIds.delete(id);
                }
            }
        });

        // Show bulk actions bar if any are selected
        if (selectedIds.size > 0 || isAllAcrossPages) {
            bar.classList.remove('hidden');
            bar.classList.add('flex');
            
            if (isAllAcrossPages) {
                countEl.textContent = 'All {{ $plots->total() }} trashed plots selected';
            } else {
                countEl.textContent = selectedIds.size + ' selected';
            }
        } else {
            bar.classList.add('hidden');
            bar.classList.remove('flex');
        }

        // Update select all checkbox state for current page
        const all = document.querySelectorAll('.plot-checkbox');
        const allChecked = all.length > 0 && Array.from(all).every(cb => cb.checked);
        document.getElementById('selectAll').checked = allChecked;
        
        saveSelection();
    }

    // ─── Save selection to localStorage ───
    function saveSelection() {
        localStorage.setItem('trash_selected_ids', JSON.stringify(Array.from(selectedIds)));
    }

    // ─── Clear Selection ───
    function clearSelection() {
        document.querySelectorAll('.plot-checkbox').forEach(cb => cb.checked = false);
        selectedIds.clear();
        isAllAcrossPages = false;
        
        const selectAllAcross = document.getElementById('selectAllAcrossPages');
        if (selectAllAcross) {
            selectAllAcross.checked = false;
        }
        
        document.getElementById('selectAll').checked = false;
        
        localStorage.removeItem('trash_selected_ids');
        localStorage.setItem('trash_select_all_across', 'false');
        
        updateBulkActions();
    }

    // ─── Force Delete (Single) ───
    function confirmForceDelete(id, name) {
        if (typeof showConfirmModal === 'function') {
            showConfirmModal(
                "⚠️ Permanently Delete",
                `"${name}" will be permanently deleted. This action CANNOT be undone!`,
                function() {
                    const form = document.createElement("form");
                    form.method = "POST";
                    form.action = "{{ route('plots.forceDelete', '__ID__') }}".replace("__ID__", id);
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                },
                "Delete Forever",
                "bg-red-600 hover:bg-red-700"
            );
        } else {
            if (confirm(`"${name}" will be permanently deleted. This action CANNOT be undone!`)) {
                const form = document.createElement("form");
                form.method = "POST";
                form.action = "{{ route('plots.forceDelete', '__ID__') }}".replace("__ID__", id);
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    }

    // ─── Bulk Restore ───
    function bulkRestore() {
        if (selectedIds.size === 0 && !isAllAcrossPages) return;

        let message;
        if (isAllAcrossPages) {
            message = `Restore ALL {{ $plots->total() }} trashed plots?`;
        } else {
            message = `Restore ${selectedIds.size} plot(s) from trash?`;
        }

        if (typeof showConfirmModal === 'function') {
            showConfirmModal(
                "Restore Plots",
                message,
                function() {
                    executeBulkRestore();
                },
                `Restore ${isAllAcrossPages ? 'ALL' : selectedIds.size} Plot(s)`,
                "bg-green-500 hover:bg-green-600"
            );
        } else {
            if (confirm(message)) {
                executeBulkRestore();
            }
        }
    }

    function executeBulkRestore() {
        const form = document.getElementById('bulkRestoreForm');
        
        // Remove existing dynamic inputs
        form.querySelectorAll('.id-input, .except-id-input').forEach(el => el.remove());

        if (isAllAcrossPages) {
            // Restore ALL across pages
            document.getElementById('allSelectedInputRestore').value = 'true';
            
            // Get IDs that are NOT selected (to exclude them)
            const allVisibleIds = Array.from(document.querySelectorAll('.plot-checkbox')).map(cb => cb.value);
            const exceptIds = allVisibleIds.filter(id => !selectedIds.has(id));
            
            document.getElementById('exceptIdsInputRestore').value = exceptIds.join(',');
            
            // Add individual except IDs
            exceptIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'except_ids[]';
                input.value = id;
                input.classList.add('except-id-input');
                form.appendChild(input);
            });
        } else {
            // Normal selection - add individual IDs
            document.getElementById('allSelectedInputRestore').value = 'false';
            document.getElementById('exceptIdsInputRestore').value = '';
            
            Array.from(selectedIds).forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                input.classList.add('id-input');
                form.appendChild(input);
            });
        }

        form.submit();
        
        // Clear selection after submission
        setTimeout(() => {
            localStorage.removeItem('trash_selected_ids');
            localStorage.removeItem('trash_select_all_across');
        }, 100);
    }

    // ─── Bulk Force Delete ───
    function bulkForceDelete() {
        if (selectedIds.size === 0 && !isAllAcrossPages) return;

        let message;
        if (isAllAcrossPages) {
            message = `⚠️ Permanently delete ALL {{ $plots->total() }} trashed plots? This CANNOT be undone!`;
        } else {
            message = `⚠️ Permanently delete ${selectedIds.size} plot(s)? This CANNOT be undone!`;
        }

        if (typeof showConfirmModal === 'function') {
            showConfirmModal(
                "⚠️ Permanently Delete",
                message,
                function() {
                    executeBulkForceDelete();
                },
                `Delete ${isAllAcrossPages ? 'ALL' : selectedIds.size} Forever`,
                "bg-red-600 hover:bg-red-700"
            );
        } else {
            if (confirm(message)) {
                executeBulkForceDelete();
            }
        }
    }

    function executeBulkForceDelete() {
        const form = document.getElementById('bulkForceDeleteForm');
        
        // Remove existing dynamic inputs
        form.querySelectorAll('.id-input, .except-id-input').forEach(el => el.remove());

        if (isAllAcrossPages) {
            // Delete ALL across pages
            document.getElementById('allSelectedInputDelete').value = 'true';
            
            // Get IDs that are NOT selected (to exclude them)
            const allVisibleIds = Array.from(document.querySelectorAll('.plot-checkbox')).map(cb => cb.value);
            const exceptIds = allVisibleIds.filter(id => !selectedIds.has(id));
            
            document.getElementById('exceptIdsInputDelete').value = exceptIds.join(',');
            
            // Add individual except IDs
            exceptIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'except_ids[]';
                input.value = id;
                input.classList.add('except-id-input');
                form.appendChild(input);
            });
        } else {
            // Normal selection - add individual IDs
            document.getElementById('allSelectedInputDelete').value = 'false';
            document.getElementById('exceptIdsInputDelete').value = '';
            
            Array.from(selectedIds).forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                input.classList.add('id-input');
                form.appendChild(input);
            });
        }

        form.submit();
        
        // Clear selection after submission
        setTimeout(() => {
            localStorage.removeItem('trash_selected_ids');
            localStorage.removeItem('trash_select_all_across');
        }, 100);
    }

    // ─── Empty Trash (All) ───
    function confirmEmptyTrash() {
        const total = {{ $plots->total() }};
        
        if (typeof showConfirmModal === 'function') {
            showConfirmModal(
                "⚠️ Empty Trash",
                `All ${total} trashed plot(s) will be permanently deleted. This CANNOT be undone!`,
                function() {
                    // Set "Select All Across Pages" to true
                    isAllAcrossPages = true;
                    executeBulkForceDelete();
                },
                "Empty Trash",
                "bg-red-600 hover:bg-red-700"
            );
        } else {
            if (confirm(`All ${total} trashed plot(s) will be permanently deleted. This CANNOT be undone!`)) {
                isAllAcrossPages = true;
                executeBulkForceDelete();
            }
        }
    }
</script>
@endpush