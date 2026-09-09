<?php

declare(strict_types=1);

namespace Database\Seeders;

use Database\Seeders\Calculators\BuildingCalculatorSeeder;
use Database\Seeders\Calculators\PlumbingCalculatorSeeder;
use Database\Seeders\Calculators\SolarCalculatorSeeder;
use Database\Seeders\Calculators\WiringCalculatorSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DepartmentSeeder::class,
            WarehouseSeeder::class,
            BrandSeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            CalculatorCatalogSeeder::class,
            WiringCalculatorSeeder::class,
            SolarCalculatorSeeder::class,
            PlumbingCalculatorSeeder::class,
            BuildingCalculatorSeeder::class,
        ]);
    }
}
