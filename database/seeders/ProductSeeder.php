<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();

        $products = [
            // Building Materials
            [
                'department' => 'Building Materials', 'category' => 'Cement', 'brand' => 'Twiga Cement',
                'name' => 'Twiga Cement 50kg', 'sku' => 'BLD-CEM-001', 'unit' => 'bag',
                'cost' => 15500, 'price' => 17500, 'reorder' => 100,
                'attributes' => [
                    ['key' => 'unit_weight', 'value' => '50', 'unit' => 'kg'],
                    ['key' => 'material_grade', 'value' => '42.5N'],
                    ['key' => 'bag_size', 'value' => '50kg'],
                ],
                'stock' => [300, 120, 90],
            ],
            [
                'department' => 'Building Materials', 'category' => 'Cement', 'brand' => 'Simba Cement',
                'name' => 'Simba Cement 50kg', 'sku' => 'BLD-CEM-002', 'unit' => 'bag',
                'cost' => 15200, 'price' => 17200, 'reorder' => 100,
                'attributes' => [
                    ['key' => 'unit_weight', 'value' => '50', 'unit' => 'kg'],
                    ['key' => 'material_grade', 'value' => '32.5N'],
                    ['key' => 'bag_size', 'value' => '50kg'],
                ],
                'stock' => [250, 80, 60],
            ],
            [
                'department' => 'Building Materials', 'category' => 'Blocks & Bricks', 'brand' => null,
                'name' => 'Solid Concrete Block 6"', 'sku' => 'BLD-BLK-001', 'unit' => 'piece',
                'cost' => 1400, 'price' => 1800, 'reorder' => 500,
                'attributes' => [
                    ['key' => 'unit_weight', 'value' => '18', 'unit' => 'kg'],
                ],
                'stock' => [2000, 600, 400],
            ],
            [
                'department' => 'Building Materials', 'category' => 'Roofing Sheets', 'brand' => null,
                'name' => 'Corrugated Iron Sheet (Gauge 28)', 'sku' => 'BLD-ROF-001', 'unit' => 'sheet',
                'cost' => 22000, 'price' => 26500, 'reorder' => 80,
                'attributes' => [
                    ['key' => 'unit_weight', 'value' => '4.2', 'unit' => 'kg'],
                ],
                'stock' => [150, 40, 35],
            ],
            [
                'department' => 'Building Materials', 'category' => 'Sand & Aggregates', 'brand' => null,
                'name' => 'River Sand (per tonne)', 'sku' => 'BLD-SND-001', 'unit' => 'tonne',
                'cost' => 45000, 'price' => 55000, 'reorder' => 20,
                'attributes' => [
                    ['key' => 'unit_weight', 'value' => '1000', 'unit' => 'kg'],
                ],
                'stock' => [60, 25, 15],
            ],

            // Plumbing
            [
                'department' => 'Plumbing', 'category' => 'Pipes & Fittings', 'brand' => 'Kilimanjaro Pipes',
                'name' => 'PVC Pipe 50mm x 6m', 'sku' => 'PLB-PIP-001', 'unit' => 'piece',
                'cost' => 12500, 'price' => 15000, 'reorder' => 60,
                'attributes' => [
                    ['key' => 'material_type', 'value' => 'PVC'],
                    ['key' => 'pipe_diameter', 'value' => '50', 'unit' => 'mm'],
                    ['key' => 'connection_type', 'value' => 'Solvent Weld'],
                ],
                'stock' => [180, 70, 50],
            ],
            [
                'department' => 'Plumbing', 'category' => 'Pipes & Fittings', 'brand' => 'AquaFlow',
                'name' => 'PPR Pipe 25mm x 4m', 'sku' => 'PLB-PIP-002', 'unit' => 'piece',
                'cost' => 8200, 'price' => 10500, 'reorder' => 60,
                'attributes' => [
                    ['key' => 'material_type', 'value' => 'PPR'],
                    ['key' => 'pipe_diameter', 'value' => '25', 'unit' => 'mm'],
                    ['key' => 'connection_type', 'value' => 'Welded'],
                ],
                'stock' => [140, 55, 40],
            ],
            [
                'department' => 'Plumbing', 'category' => 'Pipes & Fittings', 'brand' => 'AquaFlow',
                'name' => 'Copper Pipe 15mm x 3m', 'sku' => 'PLB-PIP-003', 'unit' => 'piece',
                'cost' => 21000, 'price' => 26000, 'reorder' => 30,
                'attributes' => [
                    ['key' => 'material_type', 'value' => 'Copper'],
                    ['key' => 'pipe_diameter', 'value' => '15', 'unit' => 'mm'],
                    ['key' => 'connection_type', 'value' => 'Threaded'],
                ],
                'stock' => [70, 20, 15],
            ],
            [
                'department' => 'Plumbing', 'category' => 'Valves & Taps', 'brand' => 'Kilimanjaro Pipes',
                'name' => 'Gate Valve 1 inch', 'sku' => 'PLB-VLV-001', 'unit' => 'piece',
                'cost' => 9500, 'price' => 12500, 'reorder' => 40,
                'attributes' => [
                    ['key' => 'material_type', 'value' => 'Galvanized Steel'],
                    ['key' => 'pipe_diameter', 'value' => '25', 'unit' => 'mm'],
                    ['key' => 'connection_type', 'value' => 'Threaded'],
                ],
                'stock' => [90, 30, 25],
            ],
            [
                'department' => 'Plumbing', 'category' => 'Water Tanks', 'brand' => 'AquaFlow',
                'name' => 'Plastic Water Tank 1000L', 'sku' => 'PLB-TNK-001', 'unit' => 'piece',
                'cost' => 380000, 'price' => 450000, 'reorder' => 10,
                'attributes' => [
                    ['key' => 'material_type', 'value' => 'HDPE'],
                ],
                'stock' => [25, 8, 6],
            ],

            // Electrical
            [
                'department' => 'Electrical', 'category' => 'Cables & Wires', 'brand' => 'Cabco Wires',
                'name' => 'THHN Cable 2.5mm² (100m roll)', 'sku' => 'ELE-CAB-001', 'unit' => 'roll',
                'cost' => 95000, 'price' => 118000, 'reorder' => 25,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '450', 'unit' => 'V'],
                    ['key' => 'cable_gauge', 'value' => '2.5mm²'],
                ],
                'stock' => [60, 20, 15],
            ],
            [
                'department' => 'Electrical', 'category' => 'Switches & Sockets', 'brand' => 'Schneider Electric',
                'name' => 'Single Gang Switch', 'sku' => 'ELE-SWT-001', 'unit' => 'piece',
                'cost' => 2800, 'price' => 4000, 'reorder' => 100,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '250', 'unit' => 'V'],
                ],
                'stock' => [400, 120, 90],
            ],
            [
                'department' => 'Electrical', 'category' => 'Switches & Sockets', 'brand' => 'Schneider Electric',
                'name' => '13A Socket Outlet', 'sku' => 'ELE-SOC-001', 'unit' => 'piece',
                'cost' => 3200, 'price' => 4500, 'reorder' => 100,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '250', 'unit' => 'V'],
                ],
                'stock' => [350, 100, 80],
            ],
            [
                'department' => 'Electrical', 'category' => 'Circuit Breakers', 'brand' => 'Schneider Electric',
                'name' => 'MCB Circuit Breaker 32A', 'sku' => 'ELE-MCB-001', 'unit' => 'piece',
                'cost' => 8500, 'price' => 11500, 'reorder' => 40,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '230', 'unit' => 'V'],
                ],
                'stock' => [80, 30, 20],
            ],
            [
                'department' => 'Electrical', 'category' => 'Lighting', 'brand' => null,
                'name' => 'LED Bulb 12W', 'sku' => 'ELE-LED-001', 'unit' => 'piece',
                'cost' => 3500, 'price' => 5000, 'reorder' => 150,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '220', 'unit' => 'V'],
                    ['key' => 'wattage', 'value' => '12', 'unit' => 'W'],
                ],
                'stock' => [500, 150, 120],
            ],

            // Solar
            [
                'department' => 'Solar', 'category' => 'Solar Panels', 'brand' => 'Victron Energy',
                'name' => 'Monocrystalline Solar Panel 300W', 'sku' => 'SOL-PNL-001', 'unit' => 'piece',
                'cost' => 210000, 'price' => 265000, 'reorder' => 15,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '24', 'unit' => 'V'],
                    ['key' => 'panel_capacity_watts', 'value' => '300', 'unit' => 'W'],
                ],
                'stock' => [40, 12, 10],
            ],
            [
                'department' => 'Solar', 'category' => 'Solar Panels', 'brand' => 'Victron Energy',
                'name' => 'Monocrystalline Solar Panel 150W', 'sku' => 'SOL-PNL-002', 'unit' => 'piece',
                'cost' => 115000, 'price' => 145000, 'reorder' => 15,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '12', 'unit' => 'V'],
                    ['key' => 'panel_capacity_watts', 'value' => '150', 'unit' => 'W'],
                ],
                'stock' => [35, 10, 8],
            ],
            [
                'department' => 'Solar', 'category' => 'Batteries', 'brand' => 'Davis & Shirtliff',
                'name' => 'Lithium LiFePO4 Battery 100Ah', 'sku' => 'SOL-BAT-001', 'unit' => 'piece',
                'cost' => 620000, 'price' => 780000, 'reorder' => 8,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '12', 'unit' => 'V'],
                    ['key' => 'battery_type', 'value' => 'Lithium-ion (LiFePO4)'],
                ],
                'stock' => [18, 5, 4],
            ],
            [
                'department' => 'Solar', 'category' => 'Inverters', 'brand' => 'Victron Energy',
                'name' => 'Pure Sine Wave Inverter 1000W', 'sku' => 'SOL-INV-001', 'unit' => 'piece',
                'cost' => 275000, 'price' => 340000, 'reorder' => 10,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '24', 'unit' => 'V'],
                ],
                'stock' => [22, 7, 5],
            ],
            [
                'department' => 'Solar', 'category' => 'Charge Controllers', 'brand' => 'Davis & Shirtliff',
                'name' => 'MPPT Solar Charge Controller 40A', 'sku' => 'SOL-CHG-001', 'unit' => 'piece',
                'cost' => 165000, 'price' => 210000, 'reorder' => 10,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '24', 'unit' => 'V'],
                ],
                'stock' => [20, 6, 5],
            ],
        ];

        foreach ($products as $data) {
            DB::transaction(function () use ($data, $warehouses): void {
                $department = Department::where('name', $data['department'])->firstOrFail();
                $category = Category::where('department_id', $department->id)
                    ->where('slug', Str::slug($data['category']))
                    ->firstOrFail();
                $brand = $data['brand'] ? Brand::where('slug', Str::slug($data['brand']))->first() : null;

                $product = Product::firstOrCreate(
                    ['sku' => $data['sku']],
                    [
                        'name' => $data['name'],
                        'slug' => Str::slug($data['name']),
                        'description' => null,
                        'department_id' => $department->id,
                        'category_id' => $category->id,
                        'brand_id' => $brand?->id,
                        'unit_of_measure' => $data['unit'],
                        'cost_price' => $data['cost'],
                        'selling_price' => $data['price'],
                        'reorder_point' => $data['reorder'],
                        'is_active' => true,
                    ]
                );

                foreach ($data['attributes'] as $attribute) {
                    $product->productAttributes()->updateOrCreate(
                        ['attribute_key' => $attribute['key']],
                        [
                            'attribute_value' => $attribute['value'],
                            'attribute_unit' => $attribute['unit'] ?? null,
                        ]
                    );
                }

                $variant = ProductVariant::firstOrCreate(
                    ['sku' => $data['sku'].'-STD'],
                    [
                        'product_id' => $product->id,
                        'variant_name' => 'Standard',
                        'barcode' => null,
                        'additional_price' => 0,
                    ]
                );

                foreach ($warehouses as $index => $warehouse) {
                    $quantity = $data['stock'][$index] ?? 0;

                    $stockLevel = StockLevel::firstOrCreate(
                        ['product_variant_id' => $variant->id, 'warehouse_id' => $warehouse->id],
                        ['quantity' => 0, 'reserved_quantity' => 0]
                    );

                    if ($stockLevel->wasRecentlyCreated && $quantity > 0) {
                        $stockLevel->update(['quantity' => $quantity]);

                        StockMovement::create([
                            'product_variant_id' => $variant->id,
                            'warehouse_id' => $warehouse->id,
                            'type' => 'in',
                            'quantity' => $quantity,
                            'reference_type' => 'initial_stock',
                            'reference_id' => null,
                            'performed_by' => null,
                            'note' => 'Initial stock seed',
                        ]);
                    }
                }
            });
        }
    }
}
