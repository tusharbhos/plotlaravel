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
        $statusFilter   = request('status', '');
        $categoryFilter = request('category', '');
        $searchQuery    = request('search', '');

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
            'plots'          => $plots,
            'statusFilter'   => $statusFilter,
            'categoryFilter' => $categoryFilter,
            'searchQuery'    => $searchQuery,
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
            'plot_id'  => 'required|string|unique:plots',
            'area'     => 'required|numeric|min:0',
            'fsi'      => 'required|numeric|min:0',
            'status'   => 'required|in:available,sold,under_review,booked',
            'category' => 'required|in:PREMIUM,STANDARD,ECO',
            'road'     => 'required|string',
            'plot_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create Plot
        $plot = Plot::create([
            'plot_id'           => $request->plot_id,
            'area'              => $request->area,
            'fsi'               => $request->fsi,
            'permissible_area'  => $request->permissible_area,
            'rl'                => $request->rl ?? '',
            'status'            => $request->status,
            'road'              => $request->road,
            'plot_type'         => $request->plot_type,
            'category'          => $request->category,
            'corner'            => $request->has('corner'),
            'garden'            => $request->has('garden'),
            'notes'             => $request->notes ?? '',
            'created_by'        => Session::get('user_id'),
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
            'plot_id'  => 'required|string|unique:plots,plot_id,' . $id,
            'area'     => 'required|numeric|min:0',
            'fsi'      => 'required|numeric|min:0',
            'status'   => 'required|in:available,sold,under_review,booked',
            'category' => 'required|in:PREMIUM,STANDARD,ECO',
            'road'     => 'required|string',
            'plot_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $plot->update([
            'plot_id'           => $request->plot_id,
            'area'              => $request->area,
            'fsi'               => $request->fsi,
            'permissible_area'  => $request->permissible_area,
            'rl'                => $request->rl ?? '',
            'status'            => $request->status,
            'road'              => $request->road,
            'plot_type'         => $request->plot_type,
            'category'          => $request->category,
            'corner'            => $request->has('corner'),
            'garden'            => $request->has('garden'),
            'notes'             => $request->notes ?? '',
            'updated_by'        => Session::get('user_id'),
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
            'plot_id'     => $plot->id,
            'image_name'  => $request->image_name,
            'image_path'  => $request->image_path,
            'is_primary'  => $isFirst,
            'sort_order'  => $plot->activeImages()->count(),
        ]);

        return redirect()->route('plots.edit', $plotId)
            ->with('success', 'Image added successfully.');
    }

    // Upload image file
    public function uploadImage(Request $request, $plotId)
    {
        $plot = Plot::findOrFail($plotId);

        $validator = Validator::make($request->all(), [
            'image_name'  => 'required|string|max:255',
            'image_file'  => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $path = $request->file('image_file')->store('plots', 'public');

        $isFirst = $plot->activeImages()->count() === 0;

        PlotImage::create([
            'plot_id'     => $plot->id,
            'image_name'  => $request->image_name,
            'image_path'  => $path,
            'is_primary'  => $isFirst,
            'sort_order'  => $plot->activeImages()->count(),
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
    // Create CSV template if PhpSpreadsheet not installed
    $templateData = [
        ['Plot ID', 'Plot Type', 'Area', 'FSI', 'RL', 'Road', 'Status', 'Category', 'Corner', 'Garden', 'Notes'],
        ['Plot-101', 'Land parcel', '5035.46', '1.1', 'RL 150.5', '12MTR', 'available', 'PREMIUM', 'Yes', 'No', 'Sample plot notes'],
        ['Plot-102', 'Residential', '2500.00', '1.2', '', '15 MTR', 'booked', 'STANDARD', 'No', 'Yes', ''],
        ['Plot-103', 'Commercial', '10000.00', '1.5', 'RL 200.0', '24MTR', 'available', 'PREMIUM', 'Yes', 'No', 'Corner commercial plot'],
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
    private function importPlotRow($row, &$successCount, &$duplicateCount, &$errorRows)
    {
        // Skip empty rows
        if (empty(array_filter($row))) {
            return;
        }

        // Extract data from row (adjust indexes as per your template)
        $plotId = $row[0] ?? 'PLOT-' . time() . '-' . $successCount;
        $plotType = $row[1] ?? 'Land parcel';
        $area = floatval($row[2] ?? 0);
        $fsi = floatval($row[3] ?? 1.1);
        $rl = $row[4] ?? null;
        $road = $row[5] ?? '12MTR';
        $status = $row[6] ?? 'available';
        $category = $row[7] ?? 'STANDARD';
        $corner = isset($row[8]) ? (strtolower($row[8]) === 'yes') : false;
        $garden = isset($row[9]) ? (strtolower($row[9]) === 'yes') : false;
        $notes = $row[10] ?? null;

        // Validate required fields
        if (empty($plotId)) {
            throw new \Exception('Plot ID is required');
        }

        if ($area <= 0) {
            throw new \Exception('Area must be greater than 0');
        }

        if ($fsi <= 0) {
            throw new \Exception('FSI must be greater than 0');
        }

        // Check for duplicate plot ID
        $existingPlot = Plot::where('plot_id', $plotId)->first();
        if ($existingPlot) {
            $duplicateCount++;
            throw new \Exception("Plot ID '$plotId' already exists");
        }

        // Create plot
        Plot::create([
            'plot_id' => $plotId,
            'plot_type' => $plotType,
            'area' => $area,
            'fsi' => $fsi,
            'permissible_area' => $area * $fsi,
            'rl' => $rl,
            'road' => $road,
            'status' => $status,
            'category' => $category,
            'corner' => $corner,
            'garden' => $garden,
            'notes' => $notes,
        ]);

        $successCount++;
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
                        'plot_id'     => $plot->id,
                        'x'           => (float)$point['x'],
                        'y'           => (float)$point['y'],
                        'sort_order'  => $index,
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
                        'plot_id'    => $plot->id,
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
                    'plot_id'    => $plot->id,
                    'image_name' => $name,
                    'image_path' => $path,
                    'is_primary' => $plot->activeImages()->count() === 0,
                    'sort_order' => $plot->activeImages()->count(),
                ]);
            }
        }
    }
}
