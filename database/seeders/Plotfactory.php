<?php

namespace Database\Factories;

use App\Models\Plot;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlotFactory extends Factory
{
    protected $model = Plot::class;

    public function definition()
    {
        $area = round(rand(2000, 8000) + (rand(0, 99) / 100), 2);
        $fsi  = round(1.0 + (rand(0, 5) / 10), 1);

        return [
            'plot_id'           => 'Plot ' . rand(100, 999),
            'area'              => $area,
            'fsi'               => $fsi,
            'permissible_area'  => round($area * $fsi, 2),
            'rl'                => rand(0, 1) ? 'RL ' . rand(100, 200) . '.5' : '',
            'status'            => $this->faker->randomElement(['available', 'sold', 'under_review', 'booked']),
            'road'              => $this->faker->randomElement(['12MTR', '15 MTR', '18MTR', '24MTR']),
            'plot_type'         => $this->faker->randomElement(['Land parcel', 'Residential', 'Commercial']),
            'category'          => $this->faker->randomElement(['PREMIUM', 'STANDARD', 'ECO']),
            'corner'            => $this->faker->boolean(30),
            'garden'            => $this->faker->boolean(20),
            'notes'             => $this->faker->sentence(),
        ];
    }

    /**
     * Generate 4 random polygon points
     */
    public static function withPoints(Plot $plot)
    {
        $baseX = round(rand(10, 80) + (rand(0, 9) / 10), 1);
        $baseY = round(rand(10, 80) + (rand(0, 9) / 10), 1);
        $size  = round(1.5 + (rand(0, 20) / 10), 1);

        $points = [
            ['x' => $baseX,         'y' => $baseY,         'sort_order' => 0],
            ['x' => $baseX + $size, 'y' => $baseY,         'sort_order' => 1],
            ['x' => $baseX + $size, 'y' => $baseY + $size, 'sort_order' => 2],
            ['x' => $baseX,         'y' => $baseY + $size, 'sort_order' => 3],
        ];

        foreach ($points as $point) {
            \App\Models\PlotPoint::create([
                'plot_id'    => $plot->id,
                'x'          => $point['x'],
                'y'          => $point['y'],
                'sort_order' => $point['sort_order'],
            ]);
        }

        return $points;
    }
}