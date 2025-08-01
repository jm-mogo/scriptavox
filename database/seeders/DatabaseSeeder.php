<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\BibleSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            BibleSeeder::class, // <-- Add this line
            // You can add other seeders here if needed
        ]);

        User::factory()->create([
            'name' => 'Test',
            'email' => 'test@test',
            'password' => bcrypt('password'),
        ]);
    }
}
