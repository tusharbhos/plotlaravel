<?php

namespace Database\Seeders;

use App\Models\Plot;
use App\Models\PlotPoint;
use App\Models\PlotImage;
use Illuminate\Database\Seeder;

class PlotSeeder extends Seeder
{
    /**
     * Seed the exact plots provided by the user
     */
    public function run(): void
    {
        $plots = [
            [
                'plot_id'          => 'Plot 9',
                'area'             => 5035.46,
                'fsi'              => 1.1,
                'permissible_area' => 982.34,
                'rl'               => '',
                'status'           => 'available',
                'road'             => '18MTR',
                'plot_type'        => 'Land parcel',
                'category'         => 'PREMIUM',
                'corner'           => true,
                'garden'           => false,
                'notes'            => 'Premium corner plot on 18 meter road',
                'points' => [
                    ['x' => 15.4, 'y' => 77.0],
                    ['x' => 17.9, 'y' => 77.0],
                    ['x' => 17.9, 'y' => 78.0],
                    ['x' => 15.4, 'y' => 78.0],
                ],
            ],
            [
                'plot_id'          => 'Plot 419',
                'area'             => 3501.74,
                'fsi'              => 1.1,
                'permissible_area' => 683.14,
                'rl'               => '',
                'status'           => 'available',
                'road'             => '18MTR',
                'plot_type'        => 'Land parcel',
                'category'         => 'PREMIUM',
                'corner'           => false,
                'garden'           => false,
                'notes'            => 'Standard premium plot',
                'points' => [
                    ['x' => 44.3, 'y' => 60.6],
                    ['x' => 46.8, 'y' => 60.6],
                    ['x' => 46.8, 'y' => 61.7],
                    ['x' => 44.3, 'y' => 61.7],
                ],
            ],
            [
                'plot_id'          => 'Plot 645',
                'area'             => 3491.41,
                'fsi'              => 1.1,
                'permissible_area' => 681.12,
                'rl'               => '',
                'status'           => 'available',
                'road'             => '18MTR',
                'plot_type'        => 'Land parcel',
                'category'         => 'PREMIUM',
                'corner'           => false,
                'garden'           => false,
                'notes'            => 'Irregular shape premium plot',
                'points' => [
                    ['x' => 70.6, 'y' => 20.3],
                    ['x' => 72.1, 'y' => 20.5],
                    ['x' => 71.8, 'y' => 23.5],
                    ['x' => 70.3, 'y' => 23.4],
                ],
            ],
            [
                'plot_id'          => 'Plot 556',
                'area'             => 4278.81,
                'fsi'              => 1.1,
                'permissible_area' => 834.73,
                'rl'               => '',
                'status'           => 'available',
                'road'             => '15 MTR',
                'plot_type'        => 'Land parcel',
                'category'         => 'PREMIUM',
                'corner'           => false,
                'garden'           => false,
                'notes'            => 'Multi-point polygon plot on 15 meter road',
                'points' => [
                    ['x' => 56.8, 'y' => 20.0],
                    ['x' => 56.9, 'y' => 19.7],
                    ['x' => 57.1, 'y' => 19.4],
                    ['x' => 57.4, 'y' => 19.3],
                    ['x' => 58.2, 'y' => 19.3],
                    ['x' => 58.1, 'y' => 21.4],
                    ['x' => 56.5, 'y' => 21.4],
                ],
            ],
        ];

        foreach ($plots as $plotData) {
            $points = $plotData['points'];
            unset($plotData['points']);

            $plot = Plot::create($plotData);

            // Create polygon points
            foreach ($points as $index => $point) {
                PlotPoint::create([
                    'plot_id'    => $plot->id,
                    'x'          => $point['x'],
                    'y'          => $point['y'],
                    'sort_order' => $index,
                ]);
            }
        }

        // Add some sample images
        $plot9 = Plot::where('plot_id', 'Plot 9')->first();
        if ($plot9) {
            PlotImage::create([
                'plot_id'    => $plot9->id,
                'image_name' => 'Plot 9 Front View',
                'image_path' => 'https://images.unsplash.com/photo-1433086966358-54859d0ed716?w=400',
                'is_primary' => true,
                'sort_order' => 0,
            ]);
            PlotImage::create([
                'plot_id'    => $plot9->id,
                'image_name' => 'Plot 9 Side View',
                'image_path' => 'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?w=400',
                'is_primary' => false,
                'sort_order' => 1,
            ]);
        }

        $plot419 = Plot::where('plot_id', 'Plot 419')->first();
        if ($plot419) {
            PlotImage::create([
                'plot_id'    => $plot419->id,
                'image_name' => 'Plot 419 Overview',
                'image_path' => 'https://images.unsplash.com/photo-1518923652384-59caf86962ea?w=400',
                'is_primary' => true,
                'sort_order' => 0,
            ]);
        }

        $this->command->info('4 Plots seeded with polygon points and sample images!');
    }
}