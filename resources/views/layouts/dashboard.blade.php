<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Plot Management') }} - @yield('title', 'Dashboard')</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #1e293b;
        }

        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Sidebar transition */
        .sidebar {
            transition: width 0.3s ease, transform 0.3s ease;
        }

        .sidebar-collapsed {
            width: 72px !important;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        .sidebar-collapsed .sidebar-section-title {
            display: none;
        }

        /* Tooltip for collapsed sidebar */
        .sidebar-collapsed .nav-item {
            position: relative;
        }

        .sidebar-collapsed .nav-item:hover .tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateX(0);
        }

        .tooltip {
            position: absolute;
            left: 72px;
            top: 50%;
            transform: translateX(-10px) translateY(-50%);
            background: #1e293b;
            color: #f1f5f9;
            padding: 4px 10px;
            border-radius: 6px;
            white-space: nowrap;
            font-size: 13px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s;
            z-index: 50;
            pointer-events: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        /* Active nav */
        .nav-item.active {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
        }

        .nav-item.active .nav-icon,
        .nav-item.active .sidebar-text {
            color: #fff;
        }

        /* Cards hover */
        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.4s ease;
        }

        /* Toast */
        .toast {
            animation: slideInToast 0.4s ease forwards;
        }

        @keyframes slideInToast {
            from {
                transform: translateX(110%);
            }

            to {
                transform: translateX(0);
            }
        }

        .toast.hide {
            animation: slideOutToast 0.3s ease forwards;
        }

        @keyframes slideOutToast {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(110%);
            }
        }
    </style>

    @stack('styles')
</head>

<body class="bg-gray-100 font-sans flex flex-col min-h-screen">

    <!-- ─── NAVBAR (Top) ─── -->
    <nav class="bg-gradient-to-r from-slate-900 to-indigo-900 text-white shadow-lg z-50 fixed top-0 left-0 right-0 h-16 flex items-center justify-between px-4">
        <!-- Left: Logo + Sidebar Toggle -->
        <div class="flex items-center gap-3">
            <button id="sidebarToggle" class="p-2 rounded-lg hover:bg-white/10 transition" onclick="toggleSidebar()">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-sm"></i>
                </div>
                <span class="text-lg font-bold tracking-tight">PlotMGMT</span>
            </div>
        </div>

        <!-- Right: User Info + Logout -->
        <div class="flex items-center gap-4">
            <!-- Notification Bell -->
            <button class="relative p-2 rounded-lg hover:bg-white/10 transition">
                <i class="fas fa-bell text-sm"></i>
                <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
            </button>

            <!-- User Dropdown -->
            <div class="relative" id="userDropdownContainer">
                <button class="flex items-center gap-2 p-2 rounded-lg hover:bg-white/10 transition" onclick="toggleUserDropdown()">
                    <div class="w-8 h-8 bg-indigo-400 rounded-full flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr(session('user_name', 'U'), 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-semibold">{{ session('user_name') }}</div>
                        <div class="text-xs text-indigo-200">{{ ucfirst(session('user_role')) }}</div>
                    </div>
                    <i class="fas fa-chevron-down text-xs text-indigo-200 ml-1"></i>
                </button>
                <!-- Dropdown Menu -->
                <div id="userDropdown" class="hidden absolute right-0 top-10 w-48 bg-white text-gray-800 rounded-xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="p-3 border-b border-gray-100 bg-gray-50">
                        <div class="font-semibold text-sm">{{ session('user_name') }}</div>
                        <div class="text-xs text-gray-500">{{ session('user_email') }}</div>
                    </div>
                    <a href="{{ route('logout') }}" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-red-50 hover:text-red-600 transition">
                        <i class="fas fa-sign-out-alt w-4 text-center"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- ─── SIDEBAR (Left) ─── -->
    <aside id="sidebar" class="sidebar fixed left-0 top-16 bottom-0 bg-slate-900 text-slate-300 w-64 overflow-y-auto z-40 flex flex-col">
        <!-- User Avatar Section -->
        <div class="p-4 border-b border-slate-700">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                    {{ strtoupper(substr(session('user_name', 'U'), 0, 1)) }}
                </div>
                <div class="sidebar-text">
                    <div class="text-sm font-semibold text-white">{{ session('user_name') }}</div>
                    <div class="text-xs text-slate-400">{{ ucfirst(session('user_role')) }}</div>
                </div>
            </div>
        </div>

        <!-- Nav Items -->
        <nav class="flex-1 py-4 px-2 space-y-1">
            <!-- Section: Main -->
            <div class="sidebar-section-title px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-500 mt-2">Main</div>

            <a href="{{ route('dashboard') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-line w-5 text-center text-slate-400 {{ request()->routeIs('dashboard') ? 'text-white' : '' }}"></i>
                <span class="sidebar-text text-sm">Dashboard</span>
                <span class="tooltip">Dashboard</span>
            </a>
<!-- Section: Land Images -->
    <div class="sidebar-section-title px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-500 mt-4">Land Images</div>
    
    <a href="{{ route('land-images.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer {{ request()->routeIs('land-images.index') ? 'active' : '' }}">
        <i class="nav-icon fas fa-image w-5 text-center text-slate-400 {{ request()->routeIs('land-images.index') ? 'text-white' : '' }}"></i>
        <span class="sidebar-text text-sm">All Land Images</span>
        <span class="tooltip">All Land Images</span>
    </a>
    
    <a href="{{ route('land-images.create') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer {{ request()->routeIs('land-images.create') ? 'active' : '' }}">
        <i class="nav-icon fas fa-plus w-5 text-center text-slate-400 {{ request()->routeIs('land-images.create') ? 'text-white' : '' }}"></i>
        <span class="sidebar-text text-sm">Add Land Image</span>
        <span class="tooltip">Add Land Image</span>
    </a>
    
    <a href="{{ route('land-images.trashed') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer {{ request()->routeIs('land-images.trashed') ? 'active' : '' }}">
        <i class="nav-icon fas fa-trash w-5 text-center text-slate-400 {{ request()->routeIs('land-images.trashed') ? 'text-white' : '' }}"></i>
        <span class="sidebar-text text-sm flex items-center justify-between w-full">
            Land Trash
            @php $landTrashedCount = App\Models\LandImage::onlyTrashed()->count(); @endphp
            @if($landTrashedCount > 0)
            <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $landTrashedCount }}</span>
            @endif
        </span>
        <span class="tooltip">Land Trash</span>
    </a>
            <!-- Section: Plots -->
            <div class="sidebar-section-title px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-500 mt-4">Plots</div>
            
            <a href="{{ route('plots.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer {{ request()->routeIs('plots.index') ? 'active' : '' }}">
                <i class="nav-icon fas fa-th-large w-5 text-center text-slate-400 {{ request()->routeIs('plots.index') ? 'text-white' : '' }}"></i>
                <span class="sidebar-text text-sm">All Plots</span>
                <span class="tooltip">All Plots</span>
            </a>

            <a href="{{ route('plots.create') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer {{ request()->routeIs('plots.create') ? 'active' : '' }}">
                <i class="nav-icon fas fa-plus-circle w-5 text-center text-slate-400 {{ request()->routeIs('plots.create') ? 'text-white' : '' }}"></i>
                <span class="sidebar-text text-sm">Add Plot</span>
                <span class="tooltip">Add Plot</span>
            </a>

            <a href="{{ route('plots.trashed') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer {{ request()->routeIs('plots.trashed') ? 'active' : '' }}">
                <i class="nav-icon fas fa-trash-alt w-5 text-center text-slate-400 {{ request()->routeIs('plots.trashed') ? 'text-white' : '' }}"></i>
                <span class="sidebar-text text-sm flex items-center justify-between w-full">
                    Trash
                    @php $trashedCount = App\Models\Plot::onlyTrashed()->count(); @endphp
                    @if($trashedCount > 0)
                    <span class="bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $trashedCount }}</span>
                    @endif
                </span>
                <span class="tooltip">Trash</span>
            </a>

            <!-- Section: Data -->
            <div class="sidebar-section-title px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-500 mt-4">Data</div>

            <a href="{{ route('plots.export') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer">
                <i class="nav-icon fas fa-file-export w-5 text-center text-slate-400"></i>
                <span class="sidebar-text text-sm">Export Excel</span>
                <span class="tooltip">Export Excel</span>
            </a>

            <a href="{{ route('plots.template') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 transition cursor-pointer">
                <i class="nav-icon fas fa-file-download w-5 text-center text-slate-400"></i>
                <span class="sidebar-text text-sm">Download Template</span>
                <span class="tooltip">Download Template</span>
            </a>
        </nav>

        <!-- Sidebar Footer: Version -->
        <div class="p-4 border-t border-slate-700">
            <div class="sidebar-text text-xs text-slate-500 text-center">PlotMGMT v1.0.0</div>
        </div>
    </aside>

    <!-- ─── MAIN CONTENT ─── -->
    <main id="mainContent" class="ml-64 mt-16 flex-1 transition-all duration-300" id="mainContent">
        <!-- Breadcrumb -->
        <div class="bg-white/80 backdrop-blur border-b border-gray-200 px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition">Home</a>
                @if(isset($breadcrumbs))
                @foreach($breadcrumbs as $crumb)
                <i class="fas fa-chevron-right text-xs"></i>
                @if($crumb['url'])
                <a href="{{ $crumb['url'] }}" class="hover:text-indigo-600 transition">{{ $crumb['name'] }}</a>
                @else
                <span class="text-gray-700 font-medium">{{ $crumb['name'] }}</span>
                @endif
                @endforeach
                @endif
            </div>
            <div class="text-xs text-gray-400">{{ date('l, F j, Y') }}</div>
        </div>

        <!-- Page Content -->
        <div class="p-6 fade-in">
            <!-- Flash Messages (Toast) -->
            @if(session('success') || session('error'))
            <div id="toastContainer" class="fixed top-20 right-4 z-50 space-y-2">
                @if(session('success'))
                <div class="toast bg-white border border-green-200 text-green-800 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 max-w-sm" id="toastSuccess">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-green-600 text-sm"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-sm">Success</div>
                        <div class="text-xs text-green-600">{{ session('success') }}</div>
                    </div>
                    <button class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.classList.add('hide')">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                @endif
                @if(session('error'))
                <div class="toast bg-white border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 max-w-sm" id="toastError">
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <div class="font-semibold text-sm">Error</div>
                        <div class="text-xs text-red-600">{{ session('error') }}</div>
                    </div>
                    <button class="ml-auto text-gray-400 hover:text-gray-600" onclick="this.parentElement.classList.add('hide')">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                @endif
            </div>
            <script>
                // Auto-hide toasts after 4 seconds
                setTimeout(() => {
                    document.querySelectorAll('.toast').forEach(t => t.classList.add('hide'));
                }, 4000);
            </script>
            @endif

            <!-- Validation Errors -->
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-4">
                <div class="flex items-center gap-2 text-red-700 font-semibold mb-2">
                    <i class="fas fa-exclamation-triangle"></i> Validation Errors
                </div>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- ─── FOOTER ─── -->
    <footer class="ml-64 transition-all duration-300 bg-white border-t border-gray-200 py-4 px-6" id="mainFooter">
        <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center gap-2">
                <i class="fas fa-map-marker-alt text-indigo-500"></i>
                <span>PlotMGMT <span class="text-indigo-500 font-semibold">Pro</span></span>
                <span class="text-gray-300">|</span>
                <span>Plot Management System v1.0</span>
            </div>
            <div class="flex items-center gap-4">
                <span>{{ date('Y') }} &copy; PlotMGMT</span>
                <span class="text-gray-300">|</span>
                <span class="flex items-center gap-1">
                    <i class="fas fa-heart text-red-400" style="font-size:10px"></i> Made with care
                </span>
            </div>
        </div>
    </footer>

    <!-- ─── CONFIRM MODAL ─── -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 overflow-hidden animate-bounce-once">
            <div id="modalIcon" class="p-6 flex justify-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                </div>
            </div>
            <div class="px-6 pb-2 text-center">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-800">Confirm Action</h3>
                <p id="modalMessage" class="text-sm text-gray-500 mt-1">Are you sure you want to proceed?</p>
            </div>
            <div class="px-6 pb-6 flex justify-center gap-3">
                <button onclick="closeModal()" class="px-5 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">Cancel</button>
                <button id="modalConfirmBtn" class="px-5 py-2 text-sm font-semibold text-white bg-red-500 rounded-lg hover:bg-red-600 transition">Confirm</button>
            </div>
        </div>
    </div>

    <!-- ─── IMPORT MODAL ─── -->
    <div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-file-import text-indigo-600"></i> Import Plots from Excel
                </h3>
                <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="p-6">
                <div class="mb-4 bg-indigo-50 rounded-lg p-3">
                    <p class="text-sm text-indigo-700 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        First download the template, fill in data, then upload.
                    </p>
                </div>
                <form action="{{ route('plots.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-indigo-400 transition cursor-pointer" onclick="document.getElementById('excelFileInput').click()">
                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-500">Drag & drop your Excel/CSV file here</p>
                        <p class="text-xs text-gray-400 mt-1">or click to browse</p>
                        <input type="file" name="excel_file" id="excelFileInput" accept=".xlsx,.xls,.csv" class="hidden" onchange="updateFileName(this)" />
                        <div id="selectedFileName" class="mt-3 text-sm text-indigo-600 font-medium hidden"></div>
                    </div>
                    <div class="flex justify-between items-center mt-4">
                        <a href="{{ route('plots.template') }}" class="text-sm text-indigo-600 hover:underline flex items-center gap-1">
                            <i class="fas fa-download text-xs"></i> Download Template
                        </a>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition">
                            <i class="fas fa-upload mr-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ─── SCRIPTS ─── -->
    <script>
        // ─── Sidebar Toggle ───
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const main = document.getElementById('mainContent');
            const footer = document.getElementById('mainFooter');
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');

            if (isCollapsed) {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.style.width = '256px';
                main.style.marginLeft = '256px';
                footer.style.marginLeft = '256px';
            } else {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.style.width = '72px';
                main.style.marginLeft = '72px';
                footer.style.marginLeft = '72px';
            }
        }

        // ─── User Dropdown ───
        function toggleUserDropdown() {
            document.getElementById('userDropdown').classList.toggle('hidden');
        }
        document.addEventListener('click', function(e) {
            const container = document.getElementById('userDropdownContainer');
            if (container && !container.contains(e.target)) {
                document.getElementById('userDropdown').classList.add('hidden');
            }
        });

        // ─── Confirm Modal ───
        let modalCallback = null;

        function showConfirmModal(title, message, onConfirm, btnText = 'Confirm', btnClass = 'bg-red-500 hover:bg-red-600') {
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;
            const btn = document.getElementById('modalConfirmBtn');
            btn.textContent = btnText;
            btn.className = 'px-5 py-2 text-sm font-semibold text-white ' + btnClass + ' rounded-lg transition';
            modalCallback = onConfirm;
            document.getElementById('confirmModal').classList.remove('hidden');
            document.getElementById('confirmModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('confirmModal').classList.add('hidden');
            document.getElementById('confirmModal').classList.remove('flex');
            modalCallback = null;
        }
        document.getElementById('modalConfirmBtn').addEventListener('click', function() {
            if (modalCallback) modalCallback();
            closeModal();
        });

        // ─── Import Modal ───
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            document.getElementById('importModal').classList.add('flex');
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
            document.getElementById('importModal').classList.remove('flex');
        }

        function updateFileName(input) {
            const el = document.getElementById('selectedFileName');
            if (input.files.length) {
                el.textContent = input.files[0].name;
                el.classList.remove('hidden');
            }
        }
        // Close modals on backdrop click
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.getElementById('importModal').addEventListener('click', function(e) {
            if (e.target === this) closeImportModal();
        });
    </script>

    @stack('scripts')

</body>

</html>