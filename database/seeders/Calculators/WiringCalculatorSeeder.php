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
 * Wiring Consultant — Electrical department.
 *
 * All coefficients below (cable run lengths per point, points per switch gang,
 * points per circuit, etc.) are reasonable starting estimates based on common
 * residential wiring practice, NOT measured data from this business. Refine them
 * from real job costings via the admin formula editor once available.
 */
class WiringCalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::where('slug', 'electrical')->firstOrFail();

        $calculatorType = CalculatorType::updateOrCreate(
            ['key' => 'wiring-consultant'],
            [
                'name' => 'Wiring Consultant',
                'department_id' => $department->id,
                'description' => 'Estimate cable, switches, sockets, breakers and distribution board size for a house wiring job.',
                'is_active' => true,
            ]
        );

        $inputFields = [
            [
                'field_key' => 'rooms', 'label' => 'Number of Rooms', 'input_type' => 'number',
                'unit' => 'rooms', 'required' => true, 'sort' => 1,
            ],
            [
                'field_key' => 'outlets_per_room', 'label' => 'Power Outlets per Room', 'input_type' => 'number',
                'unit' => 'outlets', 'required' => true, 'sort' => 2,
            ],
            [
                'field_key' => 'lighting_points_per_room', 'label' => 'Lighting Points per Room', 'input_type' => 'number',
                'unit' => 'points', 'required' => true, 'sort' => 3,
            ],
            [
                'field_key' => 'house_type', 'label' => 'House Type', 'input_type' => 'select',
                'options' => ['choices' => ['single' => 'Single Storey', 'double' => 'Double Storey']],
                'unit' => null, 'required' => true, 'sort' => 4,
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
                'output_key' => 'total_sockets', 'label' => 'Power Socket Outlets', 'unit' => 'pieces', 'sort' => 1,
                'expression' => 'rooms * outlets_per_room',
            ],
            [
                'output_key' => 'total_lighting_points', 'label' => 'Lighting Points', 'unit' => 'pieces', 'sort' => 2,
                'expression' => 'rooms * lighting_points_per_room',
            ],
            [
                'output_key' => 'switch_gangs', 'label' => 'Switch Points', 'unit' => 'pieces', 'sort' => 3,
                // Assumes ~2 lighting points per switch gang on average.
                'expression' => 'ceil(total_lighting_points / 2)',
            ],
            [
                'output_key' => 'cable_1_5mm_rolls', 'label' => '1.5mm² Lighting Cable (100m rolls)', 'unit' => 'roll', 'sort' => 4,
                // Assumes ~8m of cable run per lighting point; +15% extra for a double-storey vertical run.
                'expression' => 'ceil((total_lighting_points * 8 * (house_type == "double" ? 1.15 : 1.0)) / 100)',
            ],
            [
                'output_key' => 'cable_2_5mm_rolls', 'label' => '2.5mm² Socket Cable (100m rolls)', 'unit' => 'roll', 'sort' => 5,
                // Assumes ~10m of cable run per socket; +15% extra for a double-storey vertical run.
                'expression' => 'ceil((total_sockets * 10 * (house_type == "double" ? 1.15 : 1.0)) / 100)',
            ],
            [
                'output_key' => 'lighting_circuits', 'label' => 'Lighting Circuits', 'unit' => 'circuits', 'sort' => 6,
                // Assumes up to 8 lighting points per circuit.
                'expression' => 'ceil(total_lighting_points / 8)',
            ],
            [
                'output_key' => 'socket_circuits', 'label' => 'Socket Circuits', 'unit' => 'circuits', 'sort' => 7,
                // Assumes up to 8 sockets per ring/radial circuit.
                'expression' => 'ceil(total_sockets / 8)',
            ],
            [
                'output_key' => 'lighting_breakers_count', 'label' => '16A Lighting Circuit Breakers', 'unit' => 'pieces', 'sort' => 8,
                'expression' => 'lighting_circuits',
            ],
            [
                'output_key' => 'socket_breakers_count', 'label' => '32A Socket Circuit Breakers', 'unit' => 'pieces', 'sort' => 9,
                'expression' => 'socket_circuits',
            ],
            [
                'output_key' => 'recommended_db_ways', 'label' => 'Recommended Distribution Board Size', 'unit' => 'ways', 'sort' => 10,
                // Rounds up to the nearest common board size (min 8-way), with headroom for spare ways.
                'expression' => 'max(8, ceil((lighting_circuits + socket_circuits) / 4) * 4)',
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
            ['output_key' => 'total_sockets', 'filters' => ['category' => 'switches-sockets', 'attributes' => ['fixture_type' => 'socket']]],
            ['output_key' => 'total_lighting_points', 'filters' => ['category' => 'lighting']],
            ['output_key' => 'switch_gangs', 'filters' => ['category' => 'switches-sockets', 'attributes' => ['fixture_type' => 'switch']]],
            ['output_key' => 'cable_1_5mm_rolls', 'filters' => ['category' => 'cables-wires', 'attributes' => ['cable_gauge' => '1.5mm²']]],
            ['output_key' => 'cable_2_5mm_rolls', 'filters' => ['category' => 'cables-wires', 'attributes' => ['cable_gauge' => '2.5mm²']]],
            ['output_key' => 'lighting_breakers_count', 'filters' => ['category' => 'circuit-breakers', 'attributes' => ['amperage' => '16']]],
            ['output_key' => 'socket_breakers_count', 'filters' => ['category' => 'circuit-breakers', 'attributes' => ['amperage' => '32']]],
            // lighting_circuits, socket_circuits, recommended_db_ways are left unmapped — informational only.
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
