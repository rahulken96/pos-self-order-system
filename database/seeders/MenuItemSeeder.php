<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $makananUtama = Category::where('name', 'Makanan Utama')->first();
        $minuman = Category::where('name', 'Minuman')->first();
        $camilan = Category::where('name', 'Camilan')->first();
        $pencuciMulut = Category::where('name', 'Pencuci Mulut')->first();

        // 1. Makanan Utama
        if ($makananUtama) {
            $items = [
                [
                    'name' => 'Nasi Goreng Spesial',
                    'description' => 'Nasi goreng dengan bumbu khas, telur mata sapi, ayam suwir, dan kerupuk.',
                    'price' => 25000,
                ],
                [
                    'name' => 'Ayam Bakar Taliwang',
                    'description' => 'Ayam bakar khas Lombok dengan rasa pedas gurih yang meresap.',
                    'price' => 35000,
                ],
                [
                    'name' => 'Mie Goreng Jawa',
                    'description' => 'Mie goreng dengan resep tradisional Jawa, sayuran segar, dan suwiran ayam.',
                    'price' => 22000,
                ],
            ];

            foreach ($items as $item) {
                MenuItem::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'category_id' => $makananUtama->id,
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'is_available' => true,
                    ]
                );
            }
        }

        // 2. Minuman
        if ($minuman) {
            $items = [
                [
                    'name' => 'Es Teh Manis',
                    'description' => 'Teh manis segar disajikan dengan es batu.',
                    'price' => 5000,
                ],
                [
                    'name' => 'Jus Alpukat',
                    'description' => 'Jus alpukat segar dengan tambahan susu cokelat kental manis.',
                    'price' => 15000,
                ],
                [
                    'name' => 'Kopi Susu Gula Aren',
                    'description' => 'Kopi espresso dengan susu segar dan pemanis gula aren alami.',
                    'price' => 18000,
                ],
            ];

            foreach ($items as $item) {
                MenuItem::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'category_id' => $minuman->id,
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'is_available' => true,
                    ]
                );
            }
        }

        // 3. Camilan
        if ($camilan) {
            $items = [
                [
                    'name' => 'Kentang Goreng',
                    'description' => 'Kentang goreng renyah disajikan dengan saus sambal dan mayones.',
                    'price' => 12000,
                ],
                [
                    'name' => 'Tempe Mendoan',
                    'description' => 'Tempe goreng tepung khas Banyumas yang disajikan hangat dengan sambal kecap.',
                    'price' => 10000,
                ],
            ];

            foreach ($items as $item) {
                MenuItem::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'category_id' => $camilan->id,
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'is_available' => true,
                    ]
                );
            }
        }

        // 4. Pencuci Mulut
        if ($pencuciMulut) {
            $items = [
                [
                    'name' => 'Pisang Bakar Keju',
                    'description' => 'Pisang bakar dengan topping mentega, keju parut, dan susu kental manis.',
                    'price' => 15000,
                ],
                [
                    'name' => 'Es Teler',
                    'description' => 'Es segar berisi potongan nangka, kelapa muda, alpukat, dan susu.',
                    'price' => 18000,
                ],
            ];

            foreach ($items as $item) {
                MenuItem::updateOrCreate(
                    ['name' => $item['name']],
                    [
                        'category_id' => $pencuciMulut->id,
                        'description' => $item['description'],
                        'price' => $item['price'],
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}

