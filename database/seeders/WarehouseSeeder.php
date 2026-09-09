<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            ['name' => 'Main Warehouse - Kariakoo', 'location' => 'Kariakoo, Dar es Salaam'],
            ['name' => 'Mwanza Branch Warehouse', 'location' => 'Mwanza City'],
            ['name' => 'Arusha Branch Warehouse', 'location' => 'Arusha City'],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::firstOrCreate(
                ['name' => $warehouse['name']],
                ['location' => $warehouse['location'], 'is_active' => true]
            );
        }
    }
}
