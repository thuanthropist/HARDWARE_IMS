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
 * Plumbing Calculator — Plumbing department.
 *
 * Pipe-run lengths, fittings-per-fixture ratios, and daily water demand per fixture
 * (toilet ~150L, bathroom ~100L, kitchen ~80L, +50% for commercial use) are reasonable
 * starting estimates, NOT measured data from this business. Pipe quantities are
 * expressed directly in purchase units (pieces of the specific mapped product's
 * standard length — 6m/4m/3m) so pricing lines up correctly; refine both the length
 * assumptions and the reference lengths via the admin formula editor as needed.
 */
class PlumbingCalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::where('slug', 'plumbing')->firstOrFail();

        $calculatorType = CalculatorType::updateOrCreate(
            ['key' => 'plumbing-calculator'],
            [
                'name' => 'Plumbing Calculator',
                'department_id' => $department->id,
                'description' => 'Estimate pipe runs, fittings and water tank capacity from room counts and floor count.',
                'is_active' => true,
            ]
        );

        $inputFields = [
            ['field_key' => 'toilets', 'label' => 'Number of Toilets', 'input_type' => 'number', 'unit' => 'toilets', 'required' => true, 'sort' => 1],
            ['field_key' => 'bathrooms', 'label' => 'Number of Bathrooms', 'input_type' => 'number', 'unit' => 'bathrooms', 'required' => true, 'sort' => 2],
            ['field_key' => 'kitchens', 'label' => 'Number of Kitchens', 'input_type' => 'number', 'unit' => 'kitchens', 'required' => true, 'sort' => 3],
            ['field_key' => 'floors', 'label' => 'Number of Floors', 'input_type' => 'number', 'unit' => 'floors', 'required' => true, 'sort' => 4],
            [
                'field_key' => 'building_type', 'label' => 'Building Type', 'input_type' => 'select',
                'options' => ['choices' => ['residential' => 'Residential', 'commercial' => 'Commercial']],
                'unit' => null, 'required' => true, 'sort' => 5,
            ],
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
                'output_key' => 'pipe_50mm_meters', 'label' => '50mm Drain Pipe Run', 'unit' => 'm', 'sort' => 1,
                // ~6m of 50mm drain pipe per toilet/bathroom, per floor.
                'expression' => '(toilets + bathrooms) * 6 * floors',
            ],
            [
                'output_key' => 'pipe_50mm_pieces', 'label' => '50mm PVC Drain Pipe (6m lengths)', 'unit' => 'piece', 'sort' => 2,
                'expression' => 'ceil(pipe_50mm_meters / 6)',
            ],
            [
                'output_key' => 'pipe_25mm_meters', 'label' => '25mm Supply Pipe Run', 'unit' => 'm', 'sort' => 3,
                // ~8m of 25mm supply pipe per bathroom/kitchen, per floor.
                'expression' => '(bathrooms + kitchens) * 8 * floors',
            ],
            [
                'output_key' => 'pipe_25mm_pieces', 'label' => '25mm PPR Supply Pipe (4m lengths)', 'unit' => 'piece', 'sort' => 4,
                'expression' => 'ceil(pipe_25mm_meters / 4)',
            ],
            [
                'output_key' => 'pipe_15mm_meters', 'label' => '15mm Fixture Pipe Run', 'unit' => 'm', 'sort' => 5,
                // ~3m of 15mm feed pipe per fixture room, per floor.
                'expression' => '(toilets + bathrooms + kitchens) * 3 * floors',
            ],
            [
                'output_key' => 'pipe_15mm_pieces', 'label' => '15mm Copper Pipe (3m lengths)', 'unit' => 'piece', 'sort' => 6,
                'expression' => 'ceil(pipe_15mm_meters / 3)',
            ],
            [
                'output_key' => 'elbows_count', 'label' => 'PVC Elbows (50mm)', 'unit' => 'pieces', 'sort' => 7,
                // ~4 elbows per fixture room, per floor (bends around fixtures/corners).
                'expression' => 'ceil((toilets + bathrooms + kitchens) * 4 * floors)',
            ],
            [
                'output_key' => 'tees_count', 'label' => 'PVC Tees (50mm)', 'unit' => 'pieces', 'sort' => 8,
                // ~2 tee-offs per toilet/bathroom, per floor.
                'expression' => 'ceil((toilets + bathrooms) * 2 * floors)',
            ],
            [
                'output_key' => 'valves_count', 'label' => 'Isolation Valves', 'unit' => 'pieces', 'sort' => 9,
                // One isolation valve per fixture room, per floor.
                'expression' => 'ceil((toilets + bathrooms + kitchens) * floors)',
            ],
            [
                'output_key' => 'recommended_tank_capacity_liters', 'label' => 'Recommended Water Tank Capacity', 'unit' => 'L', 'sort' => 10,
                // Rough daily demand: toilet ~150L, bathroom ~100L, kitchen ~80L; commercial use +50%.
                'expression' => '((toilets * 150) + (bathrooms * 100) + (kitchens * 80)) * floors * (building_type == "commercial" ? 1.5 : 1.0)',
            ],
            [
                'output_key' => 'tank_units_needed', 'label' => 'Water Tanks (1000L reference)', 'unit' => 'pieces', 'sort' => 11,
                'expression' => 'ceil(recommended_tank_capacity_liters / 1000)',
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
            ['output_key' => 'pipe_50mm_pieces', 'filters' => ['category' => 'pipes-fittings', 'attributes' => ['material_type' => 'PVC', 'pipe_diameter' => '50', 'fitting_type' => 'pipe']]],
            ['output_key' => 'pipe_25mm_pieces', 'filters' => ['category' => 'pipes-fittings', 'attributes' => ['material_type' => 'PPR', 'pipe_diameter' => '25', 'fitting_type' => 'pipe']]],
            ['output_key' => 'pipe_15mm_pieces', 'filters' => ['category' => 'pipes-fittings', 'attributes' => ['material_type' => 'Copper', 'pipe_diameter' => '15', 'fitting_type' => 'pipe']]],
            ['output_key' => 'elbows_count', 'filters' => ['category' => 'pipes-fittings', 'attributes' => ['fitting_type' => 'elbow']]],
            ['output_key' => 'tees_count', 'filters' => ['category' => 'pipes-fittings', 'attributes' => ['fitting_type' => 'tee']]],
            ['output_key' => 'valves_count', 'filters' => ['category' => 'valves-taps', 'attributes' => ['fitting_type' => 'valve']]],
            ['output_key' => 'tank_units_needed', 'filters' => ['category' => 'water-tanks', 'attributes' => ['material_type' => 'HDPE']]],
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
