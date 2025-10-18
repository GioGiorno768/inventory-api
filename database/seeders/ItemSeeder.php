<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Laptop Dell', 'category' => 'Elektronik', 'stock' => 15, 'unit' => 'pcs', 'threshold' => 5],
            ['name' => 'Mouse Logitech', 'category' => 'Elektronik', 'stock' => 50, 'unit' => 'pcs', 'threshold' => 10],
            ['name' => 'Kertas A4', 'category' => 'Alat Tulis', 'stock' => 200, 'unit' => 'rim', 'threshold' => 20],
            ['name' => 'Pulpen', 'category' => 'Alat Tulis', 'stock' => 8, 'unit' => 'box', 'threshold' => 10],
            ['name' => 'Monitor LG', 'category' => 'Elektronik', 'stock' => 25, 'unit' => 'pcs', 'threshold' => 5],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}