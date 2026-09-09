<?php

declare(strict_types=1);

namespace Database\Seeders;

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

/**
 * Fills gaps in the Phase 1/2 catalog so the Phase 3 calculators have real,
 * attribute-distinguishable products to match against (e.g. a 16A vs 32A breaker,
 * a switch vs a socket, pipe fittings, iron bars) instead of hardcoding SKUs.
 */
class CalculatorCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->tagExistingProductAttributes([
            'ELE-SWT-001-STD' => [['key' => 'fixture_type', 'value' => 'switch']],
            'ELE-SOC-001-STD' => [['key' => 'fixture_type', 'value' => 'socket']],
            'ELE-MCB-001-STD' => [['key' => 'amperage', 'value' => '32', 'unit' => 'A']],
            'PLB-PIP-001-STD' => [['key' => 'fitting_type', 'value' => 'pipe']],
            'PLB-PIP-002-STD' => [['key' => 'fitting_type', 'value' => 'pipe']],
            'PLB-PIP-003-STD' => [['key' => 'fitting_type', 'value' => 'pipe']],
            'PLB-VLV-001-STD' => [['key' => 'fitting_type', 'value' => 'valve']],
            'SOL-INV-001-STD' => [['key' => 'inverter_capacity_va', 'value' => '1000', 'unit' => 'VA']],
            'SOL-CHG-001-STD' => [['key' => 'controller_amperage_rating', 'value' => '40', 'unit' => 'A']],
            'SOL-BAT-001-STD' => [['key' => 'battery_capacity_ah', 'value' => '100', 'unit' => 'Ah']],
        ]);

        $warehouses = Warehouse::all();

        $newProducts = [
            [
                'department' => 'Electrical', 'category' => 'Cables & Wires', 'brand' => 'Cabco Wires',
                'name' => 'THHN Cable 1.5mm² (100m roll)', 'sku' => 'ELE-CAB-002', 'unit' => 'roll',
                'cost' => 68000, 'price' => 85000, 'reorder' => 25,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '450', 'unit' => 'V'],
                    ['key' => 'cable_gauge', 'value' => '1.5mm²'],
                ],
                'stock' => [50, 18, 12],
            ],
            [
                'department' => 'Electrical', 'category' => 'Circuit Breakers', 'brand' => 'Schneider Electric',
                'name' => 'MCB Circuit Breaker 16A', 'sku' => 'ELE-MCB-002', 'unit' => 'piece',
                'cost' => 7800, 'price' => 10500, 'reorder' => 40,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '230', 'unit' => 'V'],
                    ['key' => 'amperage', 'value' => '16', 'unit' => 'A'],
                ],
                'stock' => [70, 25, 18],
            ],
            [
                'department' => 'Electrical', 'category' => 'Distribution Boards', 'brand' => 'Schneider Electric',
                'name' => 'Distribution Board 8-Way', 'sku' => 'ELE-DB-001', 'unit' => 'piece',
                'cost' => 42000, 'price' => 55000, 'reorder' => 10,
                'attributes' => [
                    ['key' => 'voltage', 'value' => '230', 'unit' => 'V'],
                    ['key' => 'ways', 'value' => '8', 'unit' => 'ways'],
                ],
                'stock' => [15, 5, 4],
            ],
            [
                'department' => 'Plumbing', 'category' => 'Pipes & Fittings', 'brand' => 'Kilimanjaro Pipes',
                'name' => 'PVC Elbow 50mm', 'sku' => 'PLB-ELB-001', 'unit' => 'piece',
                'cost' => 1800, 'price' => 2500, 'reorder' => 80,
                'attributes' => [
                    ['key' => 'material_type', 'value' => 'PVC'],
                    ['key' => 'pipe_diameter', 'value' => '50', 'unit' => 'mm'],
                    ['key' => 'fitting_type', 'value' => 'elbow'],
                ],
                'stock' => [200, 70, 50],
            ],
            [
                'department' => 'Plumbing', 'category' => 'Pipes & Fittings', 'brand' => 'Kilimanjaro Pipes',
                'name' => 'PVC Tee 50mm', 'sku' => 'PLB-TEE-001', 'unit' => 'piece',
                'cost' => 2100, 'price' => 2900, 'reorder' => 60,
                'attributes' => [
                    ['key' => 'material_type', 'value' => 'PVC'],
                    ['key' => 'pipe_diameter', 'value' => '50', 'unit' => 'mm'],
                    ['key' => 'fitting_type', 'value' => 'tee'],
                ],
                'stock' => [150, 55, 40],
            ],
            [
                'department' => 'Building Materials', 'category' => 'Reinforcement', 'brand' => null,
                'name' => 'Iron Bar 12mm (12m length)', 'sku' => 'BLD-IRN-001', 'unit' => 'piece',
                'cost' => 28000, 'price' => 34000, 'reorder' => 60,
                'attributes' => [
                    ['key' => 'unit_weight', 'value' => '10.7', 'unit' => 'kg'],
                    ['key' => 'bar_diameter', 'value' => '12', 'unit' => 'mm'],
                ],
                'stock' => [180, 60, 45],
            ],
        ];

        foreach ($newProducts as $data) {
            DB::transaction(function () use ($data, $warehouses): void {
                $department = Department::where('name', $data['department'])->firstOrFail();
                $category = Category::where('department_id', $department->id)
                    ->where('slug', Str::slug($data['category']))
                    ->firstOrFail();
                $brand = $data['brand'] ? \App\Models\Brand::where('slug', Str::slug($data['brand']))->first() : null;

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
                            'note' => 'Initial stock seed (calculator catalog augmentation)',
                        ]);
                    }
                }
            });
        }
    }

    /**
     * @param  array<string, array<array{key: string, value: string, unit?: string}>>  $tagsBySku
     */
    private function tagExistingProductAttributes(array $tagsBySku): void
    {
        foreach ($tagsBySku as $variantSku => $attributes) {
            $variant = ProductVariant::where('sku', $variantSku)->first();

            if (! $variant) {
                continue;
            }

            foreach ($attributes as $attribute) {
                $variant->product->productAttributes()->updateOrCreate(
                    ['attribute_key' => $attribute['key']],
                    [
                        'attribute_value' => $attribute['value'],
                        'attribute_unit' => $attribute['unit'] ?? null,
                    ]
                );
            }
        }
    }
}
