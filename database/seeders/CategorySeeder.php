<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan Utama',
                'description' => 'Menu hidangan utama yang lezat dan mengenyangkan',
            ],
            [
                'name' => 'Minuman',
                'description' => 'Aneka minuman dingin dan hangat penyejuk dahaga',
            ],
            [
                'name' => 'Camilan',
                'description' => 'Makanan ringan untuk menemani waktu santai Anda',
            ],
            [
                'name' => 'Pencuci Mulut',
                'description' => 'Hidangan manis penutup makan malam Anda',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                ['description' => $category['description']]
            );
        }
    }
}

