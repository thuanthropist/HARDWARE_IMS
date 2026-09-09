<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Twiga Cement',
            'Simba Cement',
            'Kilimanjaro Pipes',
            'AquaFlow',
            'Victron Energy',
            'Davis & Shirtliff',
            'Schneider Electric',
            'Cabco Wires',
        ];

        foreach ($brands as $name) {
            Brand::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]);
        }
    }
}
