<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed default users
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name'     => 'Admin User',
            'phone'    => '9876543210',
            'email'    => 'admin@plotmgmt.com',
            'password' => 'admin123',  // Will be hashed by mutator
            'role'     => 'admin',
            'is_active' => true,
        ]);

        // Regular user
        User::create([
            'name'     => 'John Doe',
            'phone'    => '1234567890',
            'email'    => 'john@plotmgmt.com',
            'password' => 'john123',
            'role'     => 'user',
            'is_active' => true,
        ]);

        $this->command->info('Users seeded successfully!');
        $this->command->info('  Admin: admin@plotmgmt.com / admin123');
        $this->command->info('  User:  john@plotmgmt.com  / john123');
    }
}