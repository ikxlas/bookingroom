<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'password' => bcrypt('admin'),
            'role' => 'admin'
        ]);

        // Mock rooms
        \App\Models\Room::create(['name' => 'Ruang Rapat Alpha', 'capacity' => 10, 'target' => 'User', 'status' => 'available']);
        \App\Models\Room::create(['name' => 'Ruang Seminar Beta', 'capacity' => 50, 'target' => 'Admin', 'status' => 'available']);
        \App\Models\Room::create(['name' => 'Ruang Eksekutif Gamma', 'capacity' => 5, 'target' => 'Guest', 'status' => 'maintenance']);
    }
}
