@extends('layouts.dashboard')
@section('title', 'Import Plots')

@section('content')
    @php $breadcrumbs = [['name' => 'Plots', 'url' => route('plots.index')], ['name' => 'Import', 'url' => null]]; @endphp

    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-indigo-50 border-b border-indigo-100 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-import text-indigo-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-indigo-800">Import Plots</h3>
                        <p class="text-sm text-indigo-600">Upload Excel or CSV file to import multiple plots at once</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Instructions Card -->
                <div class="mb-6 bg-blue-50 border border-blue-100 rounded-xl p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        <h4 class="font-bold text-blue-800 text-sm">Import Instructions</h4>
                    </div>
                    <ul class="space-y-2 text-sm text-blue-700">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 text-xs mt-0.5"></i>
                            <span>First, download the template to see the required format</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 text-xs mt-0.5"></i>
                            <span>Fill in your plot data following the template structure</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 text-xs mt-0.5"></i>
                            <span>Upload the file (Excel .xlsx/.xls or CSV format)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-amber-500 text-xs mt-0.5"></i>
                            <span><strong>Note:</strong> Duplicate Plot IDs will be automatically skipped</span>
                        </li>
                    </ul>
                </div>

                <!-- Download Template Section -->
                <div class="mb-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Step 1: Download Template</h4>
                    <a href="{{ route('plots.template') }}"
                        class="inline-flex items-center gap-2 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition shadow-sm">
                        <i class="fas fa-download"></i> Download Excel Template
                    </a>
                    <p class="text-xs text-gray-500 mt-2">Use this template to ensure proper formatting</p>
                </div>

                <!-- Upload Section -->
                <div>
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Step 2: Upload Your File</h4>

                    <form method="POST" action="{{ route('plots.import') }}" enctype="multipart/form-data" id="importForm">
                        @csrf

                        <!-- File Upload Box -->
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-indigo-400 transition cursor-pointer mb-4"
                            onclick="document.getElementById('excelFile').click()" id="dropZone">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                            <p class="text-lg text-gray-600 font-medium mb-1">Drag & drop your file here</p>
                            <p class="text-sm text-gray-500">or click to browse</p>
                            <p class="text-xs text-gray-400 mt-2">Supports: .xlsx, .xls, .csv (Max: 5MB)</p>
                            <input type="file" name="excel_file" id="excelFile" accept=".xlsx,.xls,.csv" class="hidden"
                                onchange="handleFileSelect(this)" required />
                        </div>

                        <!-- Selected File Info -->
                        <div id="selectedFileInfo" class="hidden mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-file-excel text-green-600 text-xl"></i>
                                    <div>
                                        <p id="fileName" class="font-medium text-gray-800"></p>
                                        <p id="fileSize" class="text-xs text-gray-500"></p>
                                    </div>
                                </div>
                                <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Validation Errors -->
                        <div id="fileErrors" class="mb-4 hidden">
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                                <div class="flex items-center gap-2 text-red-700">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span id="errorMessage" class="text-sm font-medium"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end gap-3">
                            <a href="{{ route('plots.index') }}"
                                class="px-5 py-2 text-gray-600 hover:text-gray-800 font-medium">
                                Cancel
                            </a>
                            <button type="submit" id="importBtn" disabled
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <i class="fas fa-upload"></i> Import Now
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Sample Data -->
                <!-- <div class="mt-8 border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Sample Data Format</h4>
                    <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r border-gray-200">Plot ID</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r border-gray-200">Type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r border-gray-200">Area</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r border-gray-200">FSI</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r border-gray-200">Road</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r border-gray-200">Status</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-3 py-2 border-r border-gray-200">Plot-101</td>
                                    <td class="px-3 py-2 border-r border-gray-200">Land parcel</td>
                                    <td class="px-3 py-2 border-r border-gray-200">5035.46</td>
                                    <td class="px-3 py-2 border-r border-gray-200">1.1</td>
                                    <td class="px-3 py-2 border-r border-gray-200">12MTR</td>
                                    <td class="px-3 py-2 border-r border-gray-200">available</td>
                                    <td class="px-3 py-2">PREMIUM</td>
                                </tr>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-3 py-2 border-r border-gray-200">Plot-102</td>
                                    <td class="px-3 py-2 border-r border-gray-200">Residential</td>
                                    <td class="px-3 py-2 border-r border-gray-200">2500.00</td>
                                    <td class="px-3 py-2 border-r border-gray-200">1.2</td>
                                    <td class="px-3 py-2 border-r border-gray-200">15 MTR</td>
                                    <td class="px-3 py-2 border-r border-gray-200">booked</td>
                                    <td class="px-3 py-2">STANDARD</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> -->

                <!-- Sample Data -->
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-bold text-gray-700 mb-3">Sample Data Format</h4>

                    <div class="bg-gray-50 rounded-lg overflow-x-auto border border-gray-200">
                        <table class="min-w-full text-xs">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Plot ID</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Area</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">FSI</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">RL</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Road</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Status</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Category</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Corner</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Garden</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Notes</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">X</th>
                                    <th class="px-3 py-2 text-left font-semibold text-gray-600 border-r">Y</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-3 py-2 border-r">Plot-101</td>
                                    <td class="px-3 py-2 border-r">Land parcel</td>
                                    <td class="px-3 py-2 border-r">5035.46</td>
                                    <td class="px-3 py-2 border-r">1.1</td>
                                    <td class="px-3 py-2 border-r">RL 150.5</td>
                                    <td class="px-3 py-2 border-r">12MTR</td>
                                    <td class="px-3 py-2 border-r">available</td>
                                    <td class="px-3 py-2 border-r">PREMIUM</td>
                                    <td class="px-3 py-2 border-r">Yes</td>
                                    <td class="px-3 py-2 border-r">No</td>
                                    <td class="px-3 py-2 border-r">Note point 1</td>
                                    <td class="px-3 py-2 border-r">10.5;30.2;50.1</td>
                                    <td class="px-3 py-2 border-r">20.5;40.8;60.9</td>
                                </tr>

                                <tr class="hover:bg-gray-100">
                                    <td class="px-3 py-2 border-r">Plot-101</td>
                                    <td class="px-3 py-2 border-r">Land parcel</td>
                                    <td class="px-3 py-2 border-r">5035.46</td>
                                    <td class="px-3 py-2 border-r">1.1</td>
                                    <td class="px-3 py-2 border-r">RL 150.5</td>
                                    <td class="px-3 py-2 border-r">12MTR</td>
                                    <td class="px-3 py-2 border-r">available</td>
                                    <td class="px-3 py-2 border-r">PREMIUM</td>
                                    <td class="px-3 py-2 border-r">Yes</td>
                                    <td class="px-3 py-2 border-r">No</td>
                                    <td class="px-3 py-2 border-r">Note point 2</td>
                                    <td class="px-3 py-2 border-r">20.5;40.8;60.9</td>
                                    <td class="px-3 py-2 border-r">23.5;45.8;68.9</td>
                                </tr>

                                <tr class="hover:bg-gray-100">
                                    <td class="px-3 py-2 border-r">Plot-102</td>
                                    <td class="px-3 py-2 border-r">Residential</td>
                                    <td class="px-3 py-2 border-r">2500.00</td>
                                    <td class="px-3 py-2 border-r">1.2</td>
                                    <td class="px-3 py-2 border-r">RL 120.0</td>
                                    <td class="px-3 py-2 border-r">15 MTR</td>
                                    <td class="px-3 py-2 border-r">booked</td>
                                    <td class="px-3 py-2 border-r">STANDARD</td>
                                    <td class="px-3 py-2 border-r">No</td>
                                    <td class="px-3 py-2 border-r">Yes</td>
                                    <td class="px-3 py-2 border-r">Note point 1</td>
                                    <td class="px-3 py-2 border-r">10.5;34.8;40.9</td>
                                    <td class="px-3 py-2 border-r">28.5;48.8;68.9</td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                </div>


            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        let selectedFile = null;

        function handleFileSelect(input) {
            const file = input.files[0];
            if (!file) return;

            // Validate file type
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const validExtensions = ['xlsx', 'xls', 'csv'];

            const errorDiv = document.getElementById('fileErrors');
            const importBtn = document.getElementById('importBtn');

            if (!validExtensions.includes(fileExtension)) {
                errorDiv.querySelector('#errorMessage').textContent =
                    'Invalid file type. Please upload .xlsx, .xls or .csv files.';
                errorDiv.classList.remove('hidden');
                importBtn.disabled = true;
                return;
            }

            // Validate file size (5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxSize) {
                errorDiv.querySelector('#errorMessage').textContent =
                    'File size exceeds 5MB limit. Please choose a smaller file.';
                errorDiv.classList.remove('hidden');
                importBtn.disabled = true;
                return;
            }

            // Hide errors
            errorDiv.classList.add('hidden');

            // Show file info
            selectedFile = file;
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('fileSize').textContent = formatFileSize(file.size);
            document.getElementById('selectedFileInfo').classList.remove('hidden');
            importBtn.disabled = false;

            // Change drop zone style
            document.getElementById('dropZone').classList.add('border-green-400', 'bg-green-50');
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function removeFile() {
            selectedFile = null;
            document.getElementById('excelFile').value = '';
            document.getElementById('selectedFileInfo').classList.add('hidden');
            document.getElementById('dropZone').classList.remove('border-green-400', 'bg-green-50');
            document.getElementById('importBtn').disabled = true;
            document.getElementById('fileErrors').classList.add('hidden');
        }

        // Drag and drop functionality
        document.addEventListener('DOMContentLoaded', function () {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('excelFile');

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });

            function highlight() {
                dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
            }

            function unhighlight() {
                dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
            }

            dropZone.addEventListener('drop', handleDrop, false);

            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;

                if (files.length > 0) {
                    fileInput.files = files;
                    handleFileSelect(fileInput);
                }
            }
        });

        // Form submission with loading state
        document.getElementById('importForm').addEventListener('submit', function (e) {
            if (!selectedFile) {
                e.preventDefault();
                const errorDiv = document.getElementById('fileErrors');
                errorDiv.querySelector('#errorMessage').textContent = 'Please select a file to import.';
                errorDiv.classList.remove('hidden');
                return;
            }

            const importBtn = document.getElementById('importBtn');
            importBtn.disabled = true;
            importBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
        });
    </script>
@endpush