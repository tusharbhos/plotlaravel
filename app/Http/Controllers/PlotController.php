<?php

namespace App\Http\Controllers;

use App\Models\Plot;
use App\Models\PlotPoint;
use App\Models\PlotImage;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PlotController extends Controller
{
    // ─────────────────────────────────────────────
    // INDEX - List all plots with pagination
    // ─────────────────────────────────────────────
    public function index()
    {
        $query = Plot::with('points', 'activeImages', 'primaryImage');

        // Apply filters first
        if ($statusFilter = request('status', '')) {
            $query->where('status', $statusFilter);
        }
        if ($categoryFilter = request('category', '')) {
            $query->where('category', $categoryFilter);
        }
        if ($searchQuery = request('search', '')) {
            $query->where('plot_id', 'LIKE', "%{$searchQuery}%");
        }

        // Natural sorting for MySQL
        if (DB::connection()->getDriverName() === 'mysql') {
            $query->orderByRaw('LENGTH(plot_id), plot_id');
        } else {
            $query->orderBy('plot_id');
        }

        // Add pagination - 10 items per page with query string persistence
        $plots = $query->paginate(10)->withQueryString();

        return view('plots.index', [
            'plots' => $plots,
            'statusFilter' => request('status', ''),
            'categoryFilter' => request('category', ''),
            'searchQuery' => request('search', ''),
        ]);
    }

    // ─────────────────────────────────────────────
    // CREATE - Show form
    // ─────────────────────────────────────────────
    public function create()
    {
        // Store the previous URL for back button
        if (
            url()->previous() !== url()->current() &&
            str_contains(url()->previous(), route('plots.index'))
        ) {
            session(['plot_index_url' => url()->previous()]);
        }

        return view('plots.create');
    }

    // ─────────────────────────────────────────────
    // STORE - Save new plot
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plot_id' => 'required|string|unique:plots',
            'area' => 'required|numeric|min:0',
            'fsi' => 'required|numeric|min:0',
            'permissible_area' => 'required|numeric|min:0',
            'status' => 'required|in:available,sold,under_review,booked',
            'category' => 'required|in:PREMIUM,ECONOMY',
            'road' => 'required|string',
            'plot_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create Plot
        $plot = Plot::create([
            'plot_id' => $request->plot_id,
            'plot_type' => $request->plot_type, // FIXED: Added missing field
            'area' => $request->area,
            'fsi' => $request->fsi,
            'permissible_area' => $request->permissible_area,
            'rl' => $request->rl,
            'status' => $request->status,
            'road' => $request->road,
            'plot_type' => $request->plot_type,
            'category' => $request->category,
            'corner' => $request->has('corner'),
            'garden' => $request->has('garden'),
            'notes' => $request->notes ?? '',
            'created_by' => Session::get('user_id'),
        ]);

        // Save Polygon Points
        $this->savePolygonPoints($plot, $request);

        // Save Images
        $this->saveImages($plot, $request);

        // Get the stored index URL or fallback to index
        $redirectUrl = session('plot_index_url', route('plots.index'));

        return redirect($redirectUrl)
            ->with('success', 'Plot "' . $plot->plot_id . '" created successfully!');
    }

    // ─────────────────────────────────────────────
    // EDIT - Show edit form
    // ─────────────────────────────────────────────
    public function edit($id)
    {
        $plot = Plot::with('points', 'activeImages')->withTrashed()->findOrFail($id);

        // Store the previous URL (plots index with pagination/filters)
        if (
            url()->previous() !== url()->current() &&
            str_contains(url()->previous(), route('plots.index'))
        ) {
            session(['plot_index_url' => url()->previous()]);
        }

        return view('plots.edit', compact('plot'));
    }

    // ─────────────────────────────────────────────
    // UPDATE - Save changes
    // ─────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $plot = Plot::withTrashed()->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'plot_id' => 'required|string|unique:plots,plot_id,' . $id,
            'area' => 'required|numeric|min:0',
            'fsi' => 'required|numeric|min:0',
            'permissible_area' => 'required|numeric|min:0',
            'status' => 'required|in:available,sold,under_review,booked',
            'category' => 'required|in:PREMIUM,ECONOMY',
            'road' => 'required|string',
            'plot_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $plot->update([
            'plot_id' => $request->plot_id,
            'plot_type' => $request->plot_type, // FIXED: Added missing field
            'area' => $request->area,
            'fsi' => $request->fsi,
            'permissible_area' => $request->permissible_area,
            'rl' => $request->rl,
            'status' => $request->status,
            'road' => $request->road,
            'category' => $request->category,
            'corner' => $request->has('corner'),
            'garden' => $request->has('garden'),
            'notes' => $request->notes ?? '',
            'updated_by' => Session::get('user_id'),
        ]);

        // Delete old points and re-save
        $plot->points()->delete();
        $this->savePolygonPoints($plot, $request);

        // Save new images (keep existing, add new)
        $this->saveImages($plot, $request);

        // Get the stored index URL or fallback to index
        $redirectUrl = $request->input('redirect_url', session('plot_index_url', route('plots.index')));

        return redirect($redirectUrl)
            ->with('success', 'Plot "' . $plot->plot_id . '" updated successfully!');
    }

    // ─────────────────────────────────────────────
    // DELETE - Soft delete (move to trash) with redirect back
    // ─────────────────────────────────────────────
    public function destroy($id)
    {
        $plot = Plot::findOrFail($id);
        $plot->delete();

        // Get the redirect URL from request or fallback to index with query string
        $redirectUrl = request('redirect_url', url()->previous());

        // Make sure we're redirecting to the index page with filters
        if (!str_contains($redirectUrl, route('plots.index'))) {
            $redirectUrl = session('plot_index_url', route('plots.index'));
        }

        return redirect($redirectUrl)
            ->with('success', 'Plot "' . $plot->plot_id . '" moved to trash.');
    }

    // ─────────────────────────────────────────────
    // MULTIPLE DELETE - Bulk soft delete
    // ─────────────────────────────────────────────
    // ─────────────────────────────────────────────
    // MULTIPLE DELETE - Bulk soft delete (Supports ALL pages)
    // ─────────────────────────────────────────────
    public function multipleDelete(Request $request)
    {
        $count = 0;

        // Check if "All" is selected
        if ($request->input('all_selected') === 'true') {
            // Get except_ids from request
            $exceptIds = [];
            if ($request->has('except_ids')) {
                if (is_string($request->except_ids)) {
                    // Handle comma-separated string
                    $exceptIds = explode(',', $request->except_ids);
                } else {
                    // Handle array
                    $exceptIds = $request->except_ids;
                }
            }

            // Build query excluding specified IDs
            $query = Plot::query();
            if (!empty($exceptIds)) {
                $query->whereNotIn('id', $exceptIds);
            }

            // Apply current filters
            if ($statusFilter = $request->input('status', '')) {
                $query->where('status', $statusFilter);
            }
            if ($categoryFilter = $request->input('category', '')) {
                $query->where('category', $categoryFilter);
            }
            if ($searchQuery = $request->input('search', '')) {
                $query->where('plot_id', 'LIKE', "%{$searchQuery}%");
            }

            $plots = $query->get();
            foreach ($plots as $plot) {
                $plot->delete();
                $count++;
            }
        } else {
            // Delete only selected IDs from current page
            $ids = $request->input('ids', []);
            foreach ($ids as $id) {
                $plot = Plot::find($id);
                if ($plot) {
                    $plot->delete();
                    $count++;
                }
            }
        }

        // Get the redirect URL from request or fallback to index with query string
        $redirectUrl = $request->input('redirect_url', session('plot_index_url', route('plots.index')));

        return redirect($redirectUrl)
            ->with('success', $count . ' plot(s) moved to trash.');
    }

    // ─────────────────────────────────────────────
    // TRASH - Show trashed plots
    // ─────────────────────────────────────────────
    public function trashed()
    {
        $plots = Plot::onlyTrashed()
            ->with('points', 'activeImages')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('plots.trashed', compact('plots'));
    }

    // ─────────────────────────────────────────────
    // RESTORE - Restore from trash
    // ─────────────────────────────────────────────
    public function restore($id)
    {
        $plot = Plot::onlyTrashed()->findOrFail($id);
        $plot->restore();
        return redirect()->route('plots.trashed')
            ->with('success', 'Plot "' . $plot->plot_id . '" restored successfully!');
    }



    // ─────────────────────────────────────────────
    // FORCE DELETE - Permanently delete from trash
    // ─────────────────────────────────────────────
    public function forceDelete($id)
    {
        $plot = Plot::onlyTrashed()->findOrFail($id);
        $name = $plot->plot_id;
        $plot->points()->delete();
        $plot->images()->delete();
        $plot->forceDelete();
        return redirect()->route('plots.trashed')
            ->with('success', 'Plot "' . $name . '" permanently deleted.');
    }

    // ─────────────────────────────────────────────
    // MULTIPLE RESTORE - Bulk restore (Supports ALL pages)
    // ─────────────────────────────────────────────
    public function multipleRestore(Request $request)
    {
        $count = 0;

        // Check if "All" is selected
        if ($request->input('all_selected') === 'true') {
            // Get except_ids from request
            $exceptIds = [];
            if ($request->has('except_ids')) {
                if (is_string($request->except_ids)) {
                    // Handle comma-separated string
                    $exceptIds = explode(',', $request->except_ids);
                } else {
                    // Handle array
                    $exceptIds = $request->except_ids;
                }
            }

            // Build query excluding specified IDs
            $query = Plot::onlyTrashed();
            if (!empty($exceptIds)) {
                $query->whereNotIn('id', $exceptIds);
            }

            $plots = $query->get();
            foreach ($plots as $plot) {
                $plot->restore();
                $count++;
            }
        } else {
            // Restore only selected IDs
            $ids = $request->validate(['ids' => 'required|array'])['ids'];
            foreach ($ids as $id) {
                $plot = Plot::onlyTrashed()->find($id);
                if ($plot) {
                    $plot->restore();
                    $count++;
                }
            }
        }

        return redirect()->route('plots.trashed')
            ->with('success', $count . ' plot(s) restored successfully.');
    }

    // ─────────────────────────────────────────────
    // MULTIPLE FORCE DELETE (Supports ALL pages)
    // ─────────────────────────────────────────────
    public function multipleForceDelete(Request $request)
    {
        $count = 0;

        // Check if "All" is selected
        if ($request->input('all_selected') === 'true') {
            // Get except_ids from request
            $exceptIds = [];
            if ($request->has('except_ids')) {
                if (is_string($request->except_ids)) {
                    // Handle comma-separated string
                    $exceptIds = explode(',', $request->except_ids);
                } else {
                    // Handle array
                    $exceptIds = $request->except_ids;
                }
            }

            // Build query excluding specified IDs
            $query = Plot::onlyTrashed();
            if (!empty($exceptIds)) {
                $query->whereNotIn('id', $exceptIds);
            }

            $plots = $query->get();
            foreach ($plots as $plot) {
                $plot->points()->delete();
                $plot->images()->delete();
                $plot->forceDelete();
                $count++;
            }
        } else {
            // Delete only selected IDs
            $ids = $request->validate(['ids' => 'required|array'])['ids'];
            foreach ($ids as $id) {
                $plot = Plot::onlyTrashed()->find($id);
                if ($plot) {
                    $plot->points()->delete();
                    $plot->images()->delete();
                    $plot->forceDelete();
                    $count++;
                }
            }
        }

        return redirect()->route('plots.trashed')
            ->with('success', $count . ' plot(s) permanently deleted.');
    }

    // ─────────────────────────────────────────────
    // IMAGE CRUD
    // ─────────────────────────────────────────────

    // Add image to plot
    public function addImage(Request $request, $plotId)
    {
        $plot = Plot::findOrFail($plotId);

        $validator = Validator::make($request->all(), [
            'image_name' => 'required|string|max:255',
            'image_path' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $isFirst = $plot->activeImages()->count() === 0;

        PlotImage::create([
            'plot_id' => $plot->id,
            'image_name' => $request->image_name,
            'image_path' => $request->image_path,
            'is_primary' => $isFirst,
            'sort_order' => $plot->activeImages()->count(),
        ]);

        return redirect()->route('plots.edit', $plotId)
            ->with('success', 'Image added successfully.');
    }

    // Upload image file
    public function uploadImage(Request $request, $plotId)
    {
        $plot = Plot::findOrFail($plotId);

        $validator = Validator::make($request->all(), [
            'image_name' => 'required|string|max:255',
            'image_file' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $path = $request->file('image_file')->store('plots', 'public');

        $isFirst = $plot->activeImages()->count() === 0;

        PlotImage::create([
            'plot_id' => $plot->id,
            'image_name' => $request->image_name,
            'image_path' => $path,
            'is_primary' => $isFirst,
            'sort_order' => $plot->activeImages()->count(),
        ]);

        return redirect()->route('plots.edit', $plotId)
            ->with('success', 'Image uploaded successfully.');
    }

    // Delete image (soft)
    public function deleteImage($imageId)
    {
        $image = PlotImage::findOrFail($imageId);
        $plotId = $image->plot_id;
        $image->delete();

        // If primary was deleted, set next one as primary
        $next = PlotImage::where('plot_id', $plotId)->whereNull('deleted_at')->first();
        if ($next) {
            PlotImage::where('plot_id', $plotId)->whereNull('deleted_at')->update(['is_primary' => false]);
            $next->update(['is_primary' => true]);
        }

        return redirect()->route('plots.edit', $plotId)
            ->with('success', 'Image deleted.');
    }

    // Set primary image
    public function setPrimaryImage($imageId)
    {
        $image = PlotImage::findOrFail($imageId);
        $plotId = $image->plot_id;
        PlotImage::where('plot_id', $plotId)->update(['is_primary' => false]);
        $image->update(['is_primary' => true]);

        return redirect()->route('plots.edit', $plotId)
            ->with('success', 'Primary image set.');
    }

    // ─────────────────────────────────────────────
    // EXCEL EXPORT
    // ─────────────────────────────────────────────
    public function exportExcel()
    {
        $plots = Plot::with('points')->orderBy('plot_id')->get();
        return (new ExcelService())->export($plots);
    }

    // ─────────────────────────────────────────────
    // EXCEL TEMPLATE DOWNLOAD
    // ─────────────────────────────────────────────
    /**
     * Download import template
     */
    public function downloadTemplate()
    {
        $templateData = [
            [
                'Plot ID',
                'Plot Type',
                'Area',
                'FSI',
                'Permissible Area',
                'RL',
                'Road',
                'Status',
                'Category',
                'Corner',
                'Garden',
                'Notes',
                'X',
                'Y'
            ],

            // 1) Circle (approx using multiple points)
            [
                'Plot-201',
                'Land parcel',
                '3140',
                '1.1',
                '3454',
                'RL 100.0',
                '18MTR',
                'available',
                'PREMIUM',
                'No',
                'No',
                'Building like shape',
                '50;60;70;60;50;40;30;40',
                '40;50;40;30;20;30;40;50'
            ],

            // 2) Triangle
            [
                'Plot-202',
                'Residential',
                '1800',
                '1.0',
                '1800',
                'RL 110.0',
                '12MTR',
                'available',
                'STANDARD',
                'Yes',
                'No',
                'Triangle shape',
                '10;40;25',
                '10;10;40'
            ],

            // 3) Square
            [
                'Plot-203',
                'Residential',
                '1600',
                '1.0',
                '1600',
                'RL 115.0',
                '15MTR',
                'available',
                'STANDARD',
                'No',
                'Yes',
                'Square shape',
                '10;40;40;10',
                '10;10;40;40'
            ],

            // 4) Irregular / real plot-like shape
            [
                'Plot-204',
                'Commercial',
                '5200',
                '1.5',
                '7800',
                'RL 130.0',
                '24MTR',
                'under_review',
                'PREMIUM',
                'Yes',
                'Yes',
                'Irregular plot shape',
                '5;20;45;60;55;30',
                '10;5;15;35;55;45'
            ],
        ];

        // Create CSV content
        $csv = '';
        foreach ($templateData as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'plot_template_') . '.csv';
        file_put_contents($tempFile, $csv);

        // Return download response
        return response()->download($tempFile, 'plot-import-template.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="plot-import-template.csv"',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Simple CSV import without PhpSpreadsheet
     */
    public function simpleImport(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $file = $request->file('csv_file');
            $path = $file->getRealPath();

            $successCount = 0;
            $errors = [];

            // Read CSV file
            if (($handle = fopen($path, 'r')) !== false) {
                // Skip header
                fgetcsv($handle);

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    if (count($data) < 7) {
                        continue; // Skip invalid rows
                    }

                    try {
                        // Create plot
                        Plot::create([
                            'plot_id'          => $data[0] ?? 'PLOT-' . time(),
                            'plot_type'        => $data[1] ?? 'Land parcel',
                            'area'             => floatval($data[2] ?? 0),
                            'fsi'              => floatval($data[3] ?? 1.1),
                            'permissible_area' => floatval($data[4] ?? 0),
                            'road'             => $data[5] ?? '12MTR',
                            'status'           => $data[6] ?? 'available',
                            'category'         => $data[7] ?? 'STANDARD',
                            'notes'            => $data[8] ?? null,
                        ]);

                        $successCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Error importing row: " . $e->getMessage();
                    }
                }

                fclose($handle);
            }

            $message = "Imported $successCount plots successfully.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
            }

            return redirect()->route('plots.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('plots.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Show import form
     */
    public function showImportForm()
    {
        return view('plots.import');
    }
    
    // ─────────────────────────────────────────────
    // EXCEL IMPORT
    // ─────────────────────────────────────────────
    /**
     * Import plots from Excel/CSV
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ]);

        try {
            $file = $request->file('excel_file');
            $extension = $file->getClientOriginalExtension();

            // Create imports directory if not exists
            $importsPath = storage_path('app/imports');
            if (!File::exists($importsPath)) {
                File::makeDirectory($importsPath, 0755, true);
            }

            $successCount = 0;
            $errorRows = [];
            $duplicateCount = 0;

            if ($extension === 'csv' || $extension === 'txt') {
                // Handle CSV/TXT file
                $handle = fopen($file->getPathname(), 'r');

                // Skip header if exists
                $header = fgetcsv($handle, 1000, ',');

                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    try {
                        $this->importPlotRow($row, $successCount, $duplicateCount, $errorRows);
                    } catch (\Exception $e) {
                        $errorRows[] = "Error: " . $e->getMessage();
                    }
                }

                fclose($handle);
            } else {
                // Handle Excel files (requires PhpSpreadsheet)
                if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                    return redirect()->route('plots.index')
                        ->with('error', 'PhpSpreadsheet package not installed. Please install it via: composer require phpoffice/phpspreadsheet');
                }

                $spreadsheet = IOFactory::load($file->getPathname());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                // Remove header row
                array_shift($rows);

                foreach ($rows as $index => $row) {
                    try {
                        $this->importPlotRow($row, $successCount, $duplicateCount, $errorRows);
                    } catch (\Exception $e) {
                        $errorRows[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                    }
                }
            }

            // Prepare message
            $message = "Import completed: $successCount plots imported successfully.";

            if ($duplicateCount > 0) {
                $message .= " $duplicateCount duplicate plot(s) skipped.";
            }

            if (!empty($errorRows)) {
                $errorCount = min(count($errorRows), 5);
                $message .= " Errors: " . implode(', ', array_slice($errorRows, 0, $errorCount));
                if (count($errorRows) > 5) {
                    $message .= " and " . (count($errorRows) - 5) . " more errors.";
                }
            }

            return redirect()->route('plots.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('plots.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Import single plot row
     */
    private function importPlotRow(array $row, &$successCount, &$duplicateCount, array &$errorRows)
    {
        if (empty(array_filter($row))) {
            return;
        }

        try {
            DB::transaction(function () use ($row, &$successCount, &$duplicateCount) {

                [
                    $plotCode,          // 0
                    $plotType,          // 1
                    $area,              // 2
                    $fsi,               // 3
                    $permissibleArea,   // 4
                    $rl,                // 5
                    $road,              // 6
                    $status,            // 7
                    $category,          // 8
                    $corner,            // 9
                    $garden,            // 10
                    $notes,             // 11
                    $xRaw,              // 12 
                    $yRaw               // 13 
                ] = array_pad($row, 14, null);


                $plotCode = trim($plotCode);

                // 🔴 Plot ID mandatory
                if (!$plotCode || $plotCode === '') {
                    $errorRows[] = 'Plot ID is required.';
                    return; // skip this row
                }

                // ❌ Only numeric allowed
                if (!ctype_digit($plotCode)) {
                    $errorRows[] = 'Invalid Plot ID: ' . $plotCode;
                    return; // skip this row and continue next
                }

                // 🔁 Duplicate check
                $existingPlot = Plot::where('plot_id', trim($plotCode))->first();
                if ($existingPlot) {
                    $duplicateCount++;
                    return;
                }

                // 🟢 Create Plot (NO default values)
                $plot = Plot::create([
                    'plot_id'          => trim($plotCode),
                    'plot_type'        => $plotType,
                    'area'             => $this->normalizeNumber($area),
                    'fsi'              => $this->normalizeNumber($fsi),
                    'permissible_area' => $this->normalizeNumber($permissibleArea),
                    'rl'               => $rl,
                    'road'             => $road,
                    'status'           => strtolower(trim($status)),
                    'category'         => strtoupper(trim($category)),
                    'corner'           => strtolower(trim($corner)) === 'yes',
                    'garden'           => strtolower(trim($garden)) === 'yes',
                    'notes'            => $notes,
                ]);

                // 🟡 Polygon Points Save (Even if dash or blank skip silently)
                if ($xRaw !== null && $yRaw !== null && trim($xRaw) !== '' && trim($yRaw) !== '') {

                    $xValues = array_map('trim', explode(';', $xRaw));
                    $yValues = array_map('trim', explode(';', $yRaw));

                    if (count($xValues) === count($yValues)) {

                        foreach ($xValues as $i => $x) {

                            $y = $yValues[$i];

                            $xVal = $this->normalizeNumber($x);
                            $yVal = $this->normalizeNumber($y);

                            // skip invalid coordinate but DO NOT break insert
                            if ($xVal === null || $yVal === null) {
                                continue;
                            }

                            PlotPoint::create([
                                'plot_id'    => $plot->id,
                                'x'          => $xVal,
                                'y'          => $yVal,
                                'sort_order' => $i,
                            ]);
                        }
                    }
                }

                $successCount++;
            });
        } catch (\Exception $e) {
            $errorRows[] = $e->getMessage();
        }
    }

    /**
     * Remove comma separators and return numeric value
     */
    private function normalizeNumber($value)
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        // Treat dash, underscore, empty as null
        if ($value === '' || $value === '-' || $value === '_') {
            return null;
        }

        $value = str_replace(',', '', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    private function savePolygonPoints(Plot $plot, Request $request)
    {
        if (isset($request->points) && is_array($request->points)) {
            foreach ($request->points as $index => $point) {
                if (isset($point['x']) && isset($point['y']) && $point['x'] !== '' && $point['y'] !== '') {
                    PlotPoint::create([
                        'plot_id' => $plot->id,
                        'x' => (float) $point['x'],
                        'y' => (float) $point['y'],
                        'sort_order' => $index,
                    ]);
                }
            }
        }
    }

    private function saveImages(Plot $plot, Request $request)
    {
        // Save URL-based images
        if (isset($request->new_images) && is_array($request->new_images)) {
            foreach ($request->new_images as $img) {
                if (!empty($img['name']) && !empty($img['path'])) {
                    PlotImage::create([
                        'plot_id' => $plot->id,
                        'image_name' => $img['name'],
                        'image_path' => $img['path'],
                        'is_primary' => $plot->activeImages()->count() === 0,
                        'sort_order' => $plot->activeImages()->count(),
                    ]);
                }
            }
        }

        // Upload file-based images
        if ($request->hasFile('image_files')) {
            foreach ($request->file('image_files') as $index => $file) {
                $name = $request->new_image_names[$index] ?? 'image_' . ($index + 1);
                $path = $file->store('plots', 'public');
                PlotImage::create([
                    'plot_id' => $plot->id,
                    'image_name' => $name,
                    'image_path' => $path,
                    'is_primary' => $plot->activeImages()->count() === 0,
                    'sort_order' => $plot->activeImages()->count(),
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────
    // Get all plots ( WITHOUT THE LOGIN OR AUTHENTICATION...)
    // ─────────────────────────────────────────────
    public function getPlotsJson()
    {
        return response()->json(
            Plot::all()
        );
    }

    // ─────────────────────────────────────────────
    //  Get all plot points ( WITHOUT THE LOGIN OR AUTHENTICATION... )
    // ─────────────────────────────────────────────
    public function getPlotPointsJson()
    {
        return response()->json(
            PlotPoint::all()
        );
    }
}
