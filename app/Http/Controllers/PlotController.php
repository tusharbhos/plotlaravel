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
use PhpOffice\PhpSpreadsheet\IOFactory; // Add this line
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PlotController extends Controller
{
    // ─────────────────────────────────────────────
    // INDEX - List all plots
    // ─────────────────────────────────────────────
    public function index()
    {
        $plots = Plot::with('points', 'activeImages', 'primaryImage')
            ->orderBy('created_at', 'desc')
            ->get();

        // Filter params
        $statusFilter = request('status', '');
        $categoryFilter = request('category', '');
        $searchQuery = request('search', '');

        if ($statusFilter) {
            $plots = $plots->filter(fn($p) => $p->status === $statusFilter);
        }
        if ($categoryFilter) {
            $plots = $plots->filter(fn($p) => $p->category === $categoryFilter);
        }
        if ($searchQuery) {
            $plots = $plots->filter(
                fn($p) =>
                str_contains(strtolower($p->plot_id), strtolower($searchQuery))
            );
        }

        return view('plots.index', [
            'plots' => $plots,
            'statusFilter' => $statusFilter,
            'categoryFilter' => $categoryFilter,
            'searchQuery' => $searchQuery,
        ]);
    }

    // ─────────────────────────────────────────────
    // CREATE - Show form
    // ─────────────────────────────────────────────
    public function create()
    {
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
            'status' => 'required|in:available,sold,under_review,booked',
            'category' => 'required|in:PREMIUM,STANDARD,ECO',
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
            'area' => $request->area,
            'fsi' => $request->fsi,
            'permissible_area' => $request->permissible_area,
            'rl' => $request->rl ?? '',
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

        return redirect()->route('plots.index')
            ->with('success', 'Plot "' . $plot->plot_id . '" created successfully!');
    }

    // ─────────────────────────────────────────────
    // EDIT - Show edit form
    // ─────────────────────────────────────────────
    public function edit($id)
    {
        $plot = Plot::with('points', 'activeImages')->withTrashed()->findOrFail($id);
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
            'status' => 'required|in:available,sold,under_review,booked',
            'category' => 'required|in:PREMIUM,STANDARD,ECO',
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
            'area' => $request->area,
            'fsi' => $request->fsi,
            'permissible_area' => $request->permissible_area,
            'rl' => $request->rl ?? '',
            'status' => $request->status,
            'road' => $request->road,
            'plot_type' => $request->plot_type,
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

        return redirect()->route('plots.index')
            ->with('success', 'Plot "' . $plot->plot_id . '" updated successfully!');
    }

    // ─────────────────────────────────────────────
    // DELETE - Soft delete (move to trash)
    // ─────────────────────────────────────────────
    public function destroy($id)
    {
        $plot = Plot::findOrFail($id);
        $plot->delete();
        return redirect()->route('plots.index')
            ->with('success', 'Plot "' . $plot->plot_id . '" moved to trash.');
    }

    // ─────────────────────────────────────────────
    // MULTIPLE DELETE - Bulk soft delete
    // ─────────────────────────────────────────────
    public function multipleDelete(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        $count = 0;
        foreach ($ids as $id) {
            $plot = Plot::find($id);
            if ($plot) {
                $plot->delete();
                $count++;
            }
        }
        return redirect()->route('plots.index')
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
            ->get();

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
    // MULTIPLE RESTORE - Bulk restore
    // ─────────────────────────────────────────────
    public function multipleRestore(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        $count = 0;
        foreach ($ids as $id) {
            $plot = Plot::onlyTrashed()->find($id);
            if ($plot) {
                $plot->restore();
                $count++;
            }
        }
        return redirect()->route('plots.trashed')
            ->with('success', $count . ' plot(s) restored successfully.');
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
    // MULTIPLE FORCE DELETE
    // ─────────────────────────────────────────────
    public function multipleForceDelete(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array'])['ids'];
        $count = 0;
        foreach ($ids as $id) {
            $plot = Plot::onlyTrashed()->find($id);
            if ($plot) {
                $plot->points()->delete();
                $plot->images()->delete();
                $plot->forceDelete();
                $count++;
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
            return redirect()->back()->withErrors($validator);
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
            return redirect()->back()->withErrors($validator);
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
                            'plot_id' => $data[0] ?? 'PLOT-' . time(),
                            'plot_type' => $data[1] ?? 'Land parcel',
                            'area' => floatval($data[2] ?? 0),
                            'fsi' => floatval($data[3] ?? 1.1),
                            'permissible_area' => floatval($data[2] ?? 0) * floatval($data[3] ?? 1.1),
                            'road' => $data[4] ?? '12MTR',
                            'status' => $data[5] ?? 'available',
                            'category' => $data[6] ?? 'STANDARD',
                            'notes' => $data[7] ?? null,
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
    // PlotController.php मध्ये
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
            DB::transaction(function () use ($row, &$successCount) {

                // Column mapping 
                [
                    $plotCode,   // 0
                    $plotType,   // 1
                    $area,       // 2
                    $fsi,        // 3
                    $rl,         // 4
                    $road,       // 5
                    $status,     // 6
                    $category,   // 7
                    $corner,     // 8
                    $garden,     // 9
                    $notes,      // 10
                    $xRaw,       // 11 
                    $yRaw        // 12 
                ] = array_pad($row, 13, null);

                if (!$plotCode) {
                    throw new \Exception('Plot ID is required');
                }

                if ($area !== null && $area !== '' && (float) $area <= 0) {
                    throw new \Exception("Invalid area for Plot ID {$plotCode}");
                }

                if ($fsi !== null && $fsi !== '' && (float) $fsi <= 0) {
                    throw new \Exception("Invalid FSI for Plot ID {$plotCode}");
                }

                $plot = Plot::firstOrCreate(
                    ['plot_id' => trim($plotCode)],
                    [
                        'plot_type' => $plotType ?? 'Land parcel',
                        'area' => (float) ($area ?? 0),
                        'fsi' => (float) ($fsi ?? 1.1),
                        'permissible_area' => (float) ($area ?? 0) * (float) ($fsi ?? 1.1),
                        'rl' => $rl,
                        'road' => $road ?? '12MTR',
                        'status' => $status ?? 'available',
                        'category' => $category ?? 'STANDARD',
                        'corner' => strtolower($corner ?? '') === 'yes',
                        'garden' => strtolower($garden ?? '') === 'yes',
                        'notes' => $notes,
                    ]
                );

                if ($xRaw !== null && $yRaw !== null && trim($xRaw) !== '' && trim($yRaw) !== '') {

                    $xValues = array_map('trim', explode(';', $xRaw));
                    $yValues = array_map('trim', explode(';', $yRaw));

                    if (count($xValues) !== count($yValues)) {
                        throw new \Exception(
                            "X and Y coordinate count mismatch for Plot ID {$plotCode}"
                        );
                    }

                    foreach ($xValues as $i => $x) {
                        $y = $yValues[$i];

                        if (!is_numeric($x) || !is_numeric($y)) {
                            throw new \Exception(
                                "Invalid coordinate value for Plot ID {$plotCode}"
                            );
                        }

                        PlotPoint::create([
                            'plot_id' => $plot->id,
                            'x' => (float) $x,
                            'y' => (float) $y,
                            'sort_order' => $i,
                        ]);
                    }
                }

                $successCount++;
            });
        } catch (\Exception $e) {
            $errorRows[] = $e->getMessage();
        }
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    private function savePolygonPoints(Plot $plot, Request $request)
    {
        if (isset($request->points) && is_array($request->points)) {
            foreach ($request->points as $index => $point) {
                if (isset($point['x']) && isset($point['y'])) {
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