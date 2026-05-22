<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Admin POS',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@pos.com'],
            [
                'name' => 'Kasir POS',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );

        User::updateOrCreate(
            ['email' => 'dapur@pos.com'],
            [
                'name' => 'Dapur POS',
                'password' => Hash::make('password'),
                'role' => 'dapur',
            ]
        );
    }
}

