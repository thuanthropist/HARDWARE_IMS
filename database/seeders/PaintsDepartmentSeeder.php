<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\CalculatorFormula;
use App\Models\CalculatorInputField;
use App\Models\CalculatorOutputProductMapping;
use App\Models\CalculatorType;
use App\Models\Category;
use App\Models\Department;
use App\Models\DepartmentAttributeSchema;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariant;
use App\Models\StockLevel;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The client's hardware store deals heavily in construction paints — this
 * seeder adds Paints as a full department (sort_order 0, so it leads every
 * department listing) without touching Building Materials / Plumbing /
 * Electrical / Solar, per the "don't delete anything, just make paints the
 * focus" instruction.
 */
class PaintsDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::updateOrCreate(
            ['slug' => 'paints'],
            [
                'name' => 'Paints',
                'code' => 'PNTS',
                'icon' => '🎨',
                'description' => 'Interior and exterior paints, primers, undercoats, and wood & metal finishes for every surface.',
                'sort_order' => 0,
                'is_active' => true,
            ]
        );

        $categories = collect([
            'Interior Paints',
            'Exterior Paints',
            'Primers & Undercoats',
            'Wood & Metal Finishes',
        ])->mapWithKeys(function (string $name) use ($department): array {
            $category = Category::updateOrCreate(
                ['department_id' => $department->id, 'slug' => Str::slug($name)],
                ['name' => $name]
            );

            return [Str::slug($name) => $category];
        });

        $attributeSchemas = [
            ['attribute_key' => 'color_family', 'label' => 'Colour Family', 'input_type' => 'select', 'unit' => null, 'options' => ['White', 'Cream / Off-White', 'Grey', 'Blue', 'Green', 'Red / Maroon', 'Brown', 'Black', 'Yellow', 'Multicolour'], 'sort_order' => 1],
            ['attribute_key' => 'finish', 'label' => 'Finish', 'input_type' => 'select', 'unit' => null, 'options' => ['Matte', 'Eggshell', 'Satin / Silk', 'Gloss', 'High Gloss'], 'sort_order' => 2],
            ['attribute_key' => 'base_type', 'label' => 'Base Type', 'input_type' => 'select', 'unit' => null, 'options' => ['Water-based (Emulsion)', 'Oil-based (Enamel)', 'Solvent-based'], 'sort_order' => 3],
            ['attribute_key' => 'usage_area', 'label' => 'Usage Area', 'input_type' => 'select', 'unit' => null, 'options' => ['Interior', 'Exterior', 'Interior & Exterior'], 'sort_order' => 4],
            ['attribute_key' => 'volume_litres', 'label' => 'Volume', 'input_type' => 'number', 'unit' => 'L', 'options' => null, 'sort_order' => 5],
            ['attribute_key' => 'coverage_per_litre', 'label' => 'Coverage', 'input_type' => 'number', 'unit' => 'm²/L', 'options' => null, 'sort_order' => 6],
        ];

        foreach ($attributeSchemas as $schema) {
            DepartmentAttributeSchema::updateOrCreate(
                ['department_id' => $department->id, 'attribute_key' => $schema['attribute_key']],
                [...$schema, 'is_required' => false]
            );
        }

        $brands = collect(['Sadolin', 'Crown Paints', 'Duco', 'Jotun'])
            ->mapWithKeys(fn (string $name) => [$name => Brand::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])]);

        $warehouses = Warehouse::all();

        $products = [
            [
                'name' => 'Sadolin Weathershield Exterior White 20L',
                'sku' => 'PNT-WSH-WHT-20',
                'category' => 'exterior-paints',
                'brand' => 'Sadolin',
                'cost' => 85000, 'sell' => 110000, 'reorder' => 5,
                'attrs' => ['color_family' => 'White', 'finish' => 'Matte', 'base_type' => 'Water-based (Emulsion)', 'usage_area' => 'Exterior', 'volume_litres' => '20', 'coverage_per_litre' => '12'],
                'stock' => [40, 15, 10],
            ],
            [
                'name' => 'Crown Silk Emulsion Magnolia 4L',
                'sku' => 'PNT-CSE-MAG-04',
                'category' => 'interior-paints',
                'brand' => 'Crown Paints',
                'cost' => 18000, 'sell' => 24000, 'reorder' => 10,
                'attrs' => ['color_family' => 'Cream / Off-White', 'finish' => 'Satin / Silk', 'base_type' => 'Water-based (Emulsion)', 'usage_area' => 'Interior', 'volume_litres' => '4', 'coverage_per_litre' => '12'],
                'stock' => [60, 20, 18],
            ],
            [
                'name' => 'Crown Vinyl Matt White 20L',
                'sku' => 'PNT-CVM-WHT-20',
                'category' => 'interior-paints',
                'brand' => 'Crown Paints',
                'cost' => 75000, 'sell' => 98000, 'reorder' => 8,
                'attrs' => ['color_family' => 'White', 'finish' => 'Matte', 'base_type' => 'Water-based (Emulsion)', 'usage_area' => 'Interior', 'volume_litres' => '20', 'coverage_per_litre' => '13'],
                'stock' => [50, 22, 16],
            ],
            [
                'name' => 'Crown Emulsion Sky Blue 1L',
                'sku' => 'PNT-CE-SKB-01',
                'category' => 'interior-paints',
                'brand' => 'Crown Paints',
                'cost' => 6000, 'sell' => 8500, 'reorder' => 15,
                'attrs' => ['color_family' => 'Blue', 'finish' => 'Matte', 'base_type' => 'Water-based (Emulsion)', 'usage_area' => 'Interior', 'volume_litres' => '1', 'coverage_per_litre' => '12'],
                'stock' => [35, 12, 10],
            ],
            [
                'name' => 'Sadolin Superdec Exterior Satin Brown 4L',
                'sku' => 'PNT-SSD-BRN-04',
                'category' => 'wood-metal-finishes',
                'brand' => 'Sadolin',
                'cost' => 32000, 'sell' => 42000, 'reorder' => 8,
                'attrs' => ['color_family' => 'Brown', 'finish' => 'Satin / Silk', 'base_type' => 'Solvent-based', 'usage_area' => 'Exterior', 'volume_litres' => '4', 'coverage_per_litre' => '10'],
                'stock' => [28, 10, 8],
            ],
            [
                'name' => 'Duco Gloss Enamel Black 4L',
                'sku' => 'PNT-DGE-BLK-04',
                'category' => 'wood-metal-finishes',
                'brand' => 'Duco',
                'cost' => 22000, 'sell' => 29500, 'reorder' => 10,
                'attrs' => ['color_family' => 'Black', 'finish' => 'Gloss', 'base_type' => 'Oil-based (Enamel)', 'usage_area' => 'Interior & Exterior', 'volume_litres' => '4', 'coverage_per_litre' => '14'],
                'stock' => [30, 14, 12],
            ],
            [
                'name' => 'Jotun Multicolor Undercoat White 4L',
                'sku' => 'PNT-JMU-WHT-04',
                'category' => 'primers-undercoats',
                'brand' => 'Jotun',
                'cost' => 16000, 'sell' => 21000, 'reorder' => 12,
                'attrs' => ['color_family' => 'White', 'finish' => 'Matte', 'base_type' => 'Water-based (Emulsion)', 'usage_area' => 'Interior & Exterior', 'volume_litres' => '4', 'coverage_per_litre' => '11'],
                'stock' => [45, 18, 14],
            ],
            [
                'name' => 'Duco Metal Primer Grey 4L',
                'sku' => 'PNT-DMP-GRY-04',
                'category' => 'primers-undercoats',
                'brand' => 'Duco',
                'cost' => 19000, 'sell' => 25000, 'reorder' => 10,
                'attrs' => ['color_family' => 'Grey', 'finish' => 'Matte', 'base_type' => 'Oil-based (Enamel)', 'usage_area' => 'Interior & Exterior', 'volume_litres' => '4', 'coverage_per_litre' => '12'],
                'stock' => [38, 16, 11],
            ],
        ];

        foreach ($products as $data) {
            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'department_id' => $department->id,
                    'category_id' => $categories[$data['category']]->id,
                    'brand_id' => $brands[$data['brand']]->id,
                    'unit_of_measure' => 'can',
                    'cost_price' => $data['cost'],
                    'selling_price' => $data['sell'],
                    'reorder_point' => $data['reorder'],
                    'track_batches' => false,
                    'is_active' => true,
                ]
            );

            foreach ($data['attrs'] as $key => $value) {
                $unit = collect($attributeSchemas)->firstWhere('attribute_key', $key)['unit'] ?? null;

                ProductAttribute::updateOrCreate(
                    ['product_id' => $product->id, 'attribute_key' => $key],
                    ['attribute_value' => $value, 'attribute_unit' => $unit]
                );
            }

            $variant = ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'sku' => $data['sku'].'-STD'],
                ['variant_name' => 'Standard']
            );

            foreach ($warehouses as $index => $warehouse) {
                StockLevel::updateOrCreate(
                    ['product_variant_id' => $variant->id, 'warehouse_id' => $warehouse->id],
                    ['quantity' => $data['stock'][$index] ?? 0, 'reserved_quantity' => 0]
                );
            }
        }

        $this->seedPaintCalculator($department, $categories);
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Category>  $categories
     */
    private function seedPaintCalculator(Department $department, $categories): void
    {
        $calculator = CalculatorType::updateOrCreate(
            ['key' => 'paint-calculator'],
            [
                'name' => 'Paint Calculator',
                'department_id' => $department->id,
                'description' => 'Estimate how many litres of paint (and primer, if needed) a wall or room needs, matched to real cans in stock.',
                'is_active' => true,
            ]
        );

        $inputFields = [
            ['field_key' => 'wall_area', 'label' => 'Wall Area to Paint', 'input_type' => 'number', 'unit' => 'm²', 'options' => null, 'sort_order' => 1],
            ['field_key' => 'coats', 'label' => 'Number of Coats', 'input_type' => 'number', 'unit' => 'coats', 'options' => null, 'sort_order' => 2],
            ['field_key' => 'needs_primer', 'label' => 'New or Bare Surface?', 'input_type' => 'select', 'unit' => null, 'options' => ['choices' => ['yes' => 'Yes — needs primer first', 'no' => 'No — already painted']], 'sort_order' => 3],
        ];

        foreach ($inputFields as $field) {
            CalculatorInputField::updateOrCreate(
                ['calculator_type_id' => $calculator->id, 'field_key' => $field['field_key']],
                [...$field, 'is_required' => true]
            );
        }

        $formulas = [
            ['output_key' => 'total_paint_litres', 'label' => 'Estimated Paint Needed', 'formula_expression' => 'ceil((wall_area * coats) / 11)', 'unit' => 'litres', 'sort_order' => 1],
            ['output_key' => 'paint_cans_20l', 'label' => '20L Paint Cans', 'formula_expression' => 'ceil(total_paint_litres / 20)', 'unit' => 'can', 'sort_order' => 2],
            ['output_key' => 'primer_litres', 'label' => 'Estimated Primer Needed', 'formula_expression' => 'needs_primer == "yes" ? ceil(wall_area / 10) : 0', 'unit' => 'litres', 'sort_order' => 3],
            ['output_key' => 'primer_cans_4l', 'label' => '4L Primer / Undercoat Cans', 'formula_expression' => 'needs_primer == "yes" ? ceil(primer_litres / 4) : 0', 'unit' => 'can', 'sort_order' => 4],
        ];

        foreach ($formulas as $formula) {
            CalculatorFormula::updateOrCreate(
                ['calculator_type_id' => $calculator->id, 'output_key' => $formula['output_key']],
                $formula
            );
        }

        $mappings = [
            'paint_cans_20l' => ['category' => 'interior-paints', 'attributes' => ['volume_litres' => '20']],
            'primer_cans_4l' => ['category' => 'primers-undercoats', 'attributes' => ['volume_litres' => '4']],
        ];

        foreach ($mappings as $outputKey => $filters) {
            CalculatorOutputProductMapping::updateOrCreate(
                ['calculator_type_id' => $calculator->id, 'output_key' => $outputKey],
                ['product_attribute_filters' => $filters, 'selection_strategy' => 'cheapest_in_stock']
            );
        }
    }
}
