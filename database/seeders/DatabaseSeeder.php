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
        // Delete the admin user if it already exists to avoid duplicates
        User::where('email', 'admin@resume.com')->delete();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@resume.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'subscription_tier' => 'elite',
            'credits' => 100,
        ]);
    }
}
