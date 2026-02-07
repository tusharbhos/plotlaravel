<?php

namespace Database\Seeders;

use Database\Factories\PlotFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the database
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            PlotSeeder::class
        ]);
    }
}