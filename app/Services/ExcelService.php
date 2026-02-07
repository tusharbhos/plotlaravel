<?php

namespace App\Services;

use App\Models\Plot;
use App\Models\PlotPoint;
use App\Models\PlotImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelService
{
    public function export($plots)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $sheet->setCellValue('A1', 'Plot ID');
        $sheet->setCellValue('B1', 'Area');
        $sheet->setCellValue('C1', 'FSI');
        $sheet->setCellValue('D1', 'Permissible Area');
        $sheet->setCellValue('E1', 'RL');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Road');
        $sheet->setCellValue('H1', 'Plot Type');
        $sheet->setCellValue('I1', 'Category');
        $sheet->setCellValue('J1', 'Corner');
        $sheet->setCellValue('K1', 'Garden');
        $sheet->setCellValue('L1', 'Notes');
        $sheet->setCellValue('M1', 'Polygon Points');
        
        // Data
        $row = 2;
        foreach ($plots as $plot) {
            $points = $plot->points->map(function($point) {
                return $point->x . ',' . $point->y;
            })->implode(';');
            
            $sheet->setCellValue('A' . $row, $plot->plot_id);
            $sheet->setCellValue('B' . $row, $plot->area);
            $sheet->setCellValue('C' . $row, $plot->fsi);
            $sheet->setCellValue('D' . $row, $plot->permissible_area);
            $sheet->setCellValue('E' . $row, $plot->rl);
            $sheet->setCellValue('F' . $row, $plot->status);
            $sheet->setCellValue('G' . $row, $plot->road);
            $sheet->setCellValue('H' . $row, $plot->plot_type);
            $sheet->setCellValue('I' . $row, $plot->category);
            $sheet->setCellValue('J' . $row, $plot->corner ? 'Yes' : 'No');
            $sheet->setCellValue('K' . $row, $plot->garden ? 'Yes' : 'No');
            $sheet->setCellValue('L' . $row, $plot->notes);
            $sheet->setCellValue('M' . $row, $points);
            $row++;
        }
        
        // Auto size columns
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'plots_export_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers with instructions
        $sheet->setCellValue('A1', 'Plot ID*');
        $sheet->setCellValue('B1', 'Area* (e.g., 5035.46)');
        $sheet->setCellValue('C1', 'FSI* (e.g., 1.1)');
        $sheet->setCellValue('D1', 'Permissible Area (auto-calculated if empty)');
        $sheet->setCellValue('E1', 'RL (e.g., RL 150.5)');
        $sheet->setCellValue('F1', 'Status* (available/sold/booked/under_review)');
        $sheet->setCellValue('G1', 'Road* (12MTR/15 MTR/18MTR/24MTR)');
        $sheet->setCellValue('H1', 'Plot Type* (Land parcel/Residential/Commercial)');
        $sheet->setCellValue('I1', 'Category* (PREMIUM/STANDARD/ECO)');
        $sheet->setCellValue('J1', 'Corner (Yes/No)');
        $sheet->setCellValue('K1', 'Garden (Yes/No)');
        $sheet->setCellValue('L1', 'Notes');
        $sheet->setCellValue('M1', 'Polygon Points* (format: x1,y1;x2,y2;x3,y3)');
        
        // Sample data
        $sheet->setCellValue('A2', 'Plot-101');
        $sheet->setCellValue('B2', 5035.46);
        $sheet->setCellValue('C2', 1.1);
        $sheet->setCellValue('D2', '');
        $sheet->setCellValue('E2', 'RL 150.5');
        $sheet->setCellValue('F2', 'available');
        $sheet->setCellValue('G2', '12MTR');
        $sheet->setCellValue('H2', 'Land parcel');
        $sheet->setCellValue('I2', 'PREMIUM');
        $sheet->setCellValue('J2', 'Yes');
        $sheet->setCellValue('K2', 'No');
        $sheet->setCellValue('L2', 'Sample plot');
        $sheet->setCellValue('M2', '0,0;100,0;100,50;0,50');
        
        // Formatting
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'plots_template_' . date('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
    
    public function import($file)
    {
        try {
            // Create imports directory if it doesn't exist
            $importDir = storage_path('app/imports');
            if (!file_exists($importDir)) {
                mkdir($importDir, 0755, true);
            }
            
            // Store the uploaded file
            $filePath = $file->store('imports', 'local');
            $fullPath = storage_path('app/' . $filePath);
            
            Log::info('Import file stored at: ' . $fullPath);
            
            if (!file_exists($fullPath)) {
                throw new \Exception('File not found after upload: ' . $fullPath);
            }
            
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Remove header row
            array_shift($rows);
            
            $imported = 0;
            $skipped = 0;
            $errors = [];
            
            DB::beginTransaction();
            
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // Excel row number (accounting for header)
                
                try {
                    // Skip empty rows
                    if (empty($row[0])) {
                        continue;
                    }
                    
                    $plotId = trim($row[0]);
                    
                    // Check if plot already exists
                    if (Plot::where('plot_id', $plotId)->exists()) {
                        $skipped++;
                        $errors[] = "Row $rowNum: Plot ID '$plotId' already exists";
                        continue;
                    }
                    
                    // Validate required fields
                    if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[5]) || empty($row[6]) || empty($row[7]) || empty($row[8])) {
                        $errors[] = "Row $rowNum: Missing required fields";
                        continue;
                    }
                    
                    // Calculate permissible area if empty
                    $permissibleArea = $row[3];
                    if (empty($permissibleArea) && !empty($row[1]) && !empty($row[2])) {
                        $permissibleArea = $row[1] * $row[2];
                    }
                    
                    // Create plot
                    $plot = Plot::create([
                        'plot_id' => $plotId,
                        'area' => floatval($row[1]),
                        'fsi' => floatval($row[2]),
                        'permissible_area' => floatval($permissibleArea),
                        'rl' => $row[4] ?? '',
                        'status' => strtolower(trim($row[5])),
                        'road' => trim($row[6]),
                        'plot_type' => trim($row[7]),
                        'category' => trim($row[8]),
                        'corner' => isset($row[9]) && strtolower(trim($row[9])) === 'yes',
                        'garden' => isset($row[10]) && strtolower(trim($row[10])) === 'yes',
                        'notes' => $row[11] ?? '',
                        'created_by' => session('user_id'),
                    ]);
                    
                    // Add polygon points if provided
                    if (!empty($row[12])) {
                        $pointsStr = trim($row[12]);
                        $pointPairs = explode(';', $pointsStr);
                        
                        foreach ($pointPairs as $order => $pair) {
                            $pair = trim($pair);
                            if (!empty($pair)) {
                                $coordinates = explode(',', $pair);
                                if (count($coordinates) >= 2) {
                                    PlotPoint::create([
                                        'plot_id' => $plot->id,
                                        'x' => floatval(trim($coordinates[0])),
                                        'y' => floatval(trim($coordinates[1])),
                                        'sort_order' => $order,
                                    ]);
                                }
                            }
                        }
                    }
                    
                    $imported++;
                    
                } catch (\Exception $e) {
                    $errors[] = "Row $rowNum: " . $e->getMessage();
                    continue;
                }
            }
            
            DB::commit();
            
            // Clean up the uploaded file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            return [
                'success' => true,
                'message' => "Imported $imported plots successfully. $skipped skipped." . 
                            ($errors ? ' Errors: ' . implode(', ', array_slice($errors, 0, 3)) . 
                            (count($errors) > 3 ? '... and ' . (count($errors) - 3) . ' more' : '') : '')
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import failed: ' . $e->getMessage());
            
            // Clean up file on error
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            return [
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ];
        }
    }
}