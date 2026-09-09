<?php

declare(strict_types=1);

namespace Database\Seeders\Calculators;

use App\Models\CalculatorFormula;
use App\Models\CalculatorInputField;
use App\Models\CalculatorOutputProductMapping;
use App\Models\CalculatorType;
use App\Models\Department;
use Illuminate\Database\Seeder;

/**
 * Building Materials Calculator — Building Materials department.
 *
 * Coefficients (cement bags/sqm, sand tonnage/sqm, blocks/sqm, rebar/sqm, labor
 * days/sqm) are reasonable starting estimates for a basic single-family build,
 * NOT measured data from this business. labor_days is intentionally left without a
 * product mapping — it's informational only, not a purchasable line item.
 */
class BuildingCalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::where('slug', 'building-materials')->firstOrFail();

        $calculatorType = CalculatorType::updateOrCreate(
            ['key' => 'building-calculator'],
            [
                'name' => 'Building Materials Calculator',
                'department_id' => $department->id,
                'description' => 'Estimate cement, sand, blocks and reinforcement from building area, wall type and floor count.',
                'is_active' => true,
            ]
        );

        $inputFields = [
            ['field_key' => 'area_sqm', 'label' => 'Building Area', 'input_type' => 'number', 'unit' => 'sqm', 'required' => true, 'sort' => 1],
            [
                'field_key' => 'wall_type', 'label' => 'Wall Type / Thickness', 'input_type' => 'select',
                'options' => ['choices' => ['block_6in' => '6-inch Block Wall', 'block_9in' => '9-inch Block Wall']],
                'unit' => null, 'required' => true, 'sort' => 2,
            ],
            ['field_key' => 'floors', 'label' => 'Number of Floors', 'input_type' => 'number', 'unit' => 'floors', 'required' => true, 'sort' => 3],
        ];

        foreach ($inputFields as $field) {
            CalculatorInputField::updateOrCreate(
                ['calculator_type_id' => $calculatorType->id, 'field_key' => $field['field_key']],
                [
                    'label' => $field['label'],
                    'input_type' => $field['input_type'],
                    'options' => $field['options'] ?? null,
                    'unit' => $field['unit'],
                    'is_required' => $field['required'],
                    'sort_order' => $field['sort'],
                ]
            );
        }

        $formulas = [
            [
                'output_key' => 'cement_bags', 'label' => 'Cement (50kg bags)', 'unit' => 'bags', 'sort' => 1,
                // ~0.4 bags per sqm per floor for a basic block-wall build.
                'expression' => 'ceil(area_sqm * floors * 0.4)',
            ],
            [
                'output_key' => 'sand_tons', 'label' => 'Sand', 'unit' => 'tonnes', 'sort' => 2,
                // ~0.12 tonnes of sand per sqm per floor.
                'expression' => 'round(area_sqm * floors * 0.12, 1)',
            ],
            [
                'output_key' => 'blocks_count', 'label' => 'Concrete Blocks', 'unit' => 'pieces', 'sort' => 3,
                // ~10 blocks/sqm for a 6" wall, ~12 blocks/sqm for a 9" wall (more blocks per sqm for the thicker wall type).
                'expression' => 'ceil(area_sqm * floors * (wall_type == "block_9in" ? 12 : 10))',
            ],
            [
                'output_key' => 'iron_bars_count', 'label' => 'Iron Bars (12mm, 12m lengths)', 'unit' => 'pieces', 'sort' => 4,
                // ~0.15 bars per sqm per floor for basic lintel/column reinforcement.
                'expression' => 'ceil(area_sqm * floors * 0.15)',
            ],
            [
                'output_key' => 'labor_days', 'label' => 'Estimated Labor Days', 'unit' => 'days', 'sort' => 5,
                // Informational only — ~1 labor-day per 15 sqm of built area, no product mapping.
                'expression' => 'ceil((area_sqm * floors) / 15)',
            ],
        ];

        foreach ($formulas as $formula) {
            CalculatorFormula::updateOrCreate(
                ['calculator_type_id' => $calculatorType->id, 'output_key' => $formula['output_key']],
                [
                    'label' => $formula['label'],
                    'formula_expression' => $formula['expression'],
                    'unit' => $formula['unit'],
                    'sort_order' => $formula['sort'],
                ]
            );
        }

        $mappings = [
            ['output_key' => 'cement_bags', 'filters' => ['category' => 'cement', 'attributes' => ['material_grade' => '42.5N']]],
            ['output_key' => 'sand_tons', 'filters' => ['category' => 'sand-aggregates']],
            ['output_key' => 'blocks_count', 'filters' => ['category' => 'blocks-bricks']],
            ['output_key' => 'iron_bars_count', 'filters' => ['category' => 'reinforcement', 'attributes' => ['bar_diameter' => '12']]],
            // labor_days is intentionally unmapped — informational only.
        ];

        foreach ($mappings as $mapping) {
            CalculatorOutputProductMapping::updateOrCreate(
                ['calculator_type_id' => $calculatorType->id, 'output_key' => $mapping['output_key']],
                [
                    'product_attribute_filters' => $mapping['filters'],
                    'selection_strategy' => 'cheapest_in_stock',
                ]
            );
        }
    }
}
