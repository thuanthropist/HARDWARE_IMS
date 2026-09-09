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
 * Solar Calculator — Solar department.
 *
 * Coefficients (4.5 peak sun hours, 30% system loss margin, 1.25 VA/W power-factor
 * allowance, 20% battery safety margin, 12V reference system) are reasonable starting
 * estimates for a Tanzania-latitude off-grid/hybrid system, NOT measured data from
 * this business. Refine via the admin formula editor once real job data exists.
 */
class SolarCalculatorSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::where('slug', 'solar')->firstOrFail();

        $calculatorType = CalculatorType::updateOrCreate(
            ['key' => 'solar-calculator'],
            [
                'name' => 'Solar Calculator',
                'department_id' => $department->id,
                'description' => 'Size a solar system — panels, battery bank, inverter and charge controller — from an appliance list and desired backup time.',
                'is_active' => true,
            ]
        );

        $inputFields = [
            [
                'field_key' => 'appliances', 'label' => 'Appliances', 'input_type' => 'repeater',
                'options' => [
                    'fields' => [
                        ['key' => 'name', 'label' => 'Appliance Name', 'type' => 'text'],
                        ['key' => 'wattage', 'label' => 'Wattage', 'type' => 'number', 'unit' => 'W'],
                        ['key' => 'hours_per_day', 'label' => 'Hours per Day', 'type' => 'number', 'unit' => 'h'],
                    ],
                ],
                'unit' => null, 'required' => true, 'sort' => 1,
            ],
            [
                'field_key' => 'backup_hours', 'label' => 'Backup Hours Desired', 'input_type' => 'number',
                'unit' => 'hours', 'required' => true, 'sort' => 2,
            ],
            [
                'field_key' => 'system_type', 'label' => 'System Type', 'input_type' => 'select',
                'options' => ['choices' => ['dc_only' => 'DC Only', 'ac_inverter' => 'AC (Inverter)']],
                'unit' => null, 'required' => true, 'sort' => 3,
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
                'output_key' => 'total_daily_wh', 'label' => 'Total Daily Energy Demand', 'unit' => 'Wh', 'sort' => 1,
                'expression' => 'sum_product(appliances, "wattage", "hours_per_day")',
            ],
            [
                'output_key' => 'peak_load_watts', 'label' => 'Peak Simultaneous Load', 'unit' => 'W', 'sort' => 2,
                // Worst case: all listed appliances running at once.
                'expression' => 'sum(appliances, "wattage")',
            ],
            [
                'output_key' => 'recommended_panel_watts', 'label' => 'Recommended Panel Capacity', 'unit' => 'W', 'sort' => 3,
                // 4.5 = avg peak sun hours; 1.3 = 30% margin for system losses and cloudy days.
                'expression' => 'ceil((total_daily_wh / 4.5) * 1.3)',
            ],
            [
                'output_key' => 'panel_units_needed', 'label' => 'Solar Panels (300W reference)', 'unit' => 'pieces', 'sort' => 4,
                'expression' => 'ceil(recommended_panel_watts / 300)',
            ],
            [
                'output_key' => 'hourly_load_wh', 'label' => 'Average Hourly Load', 'unit' => 'Wh', 'sort' => 5,
                'expression' => 'total_daily_wh / 24',
            ],
            [
                'output_key' => 'recommended_battery_ah', 'label' => 'Recommended Battery Capacity', 'unit' => 'Ah', 'sort' => 6,
                // 1.2 = 20% safety margin; /12 assumes a 12V battery bank.
                'expression' => 'ceil((hourly_load_wh * backup_hours * 1.2) / 12)',
            ],
            [
                'output_key' => 'battery_units_needed', 'label' => 'Batteries (100Ah reference)', 'unit' => 'pieces', 'sort' => 7,
                'expression' => 'ceil(recommended_battery_ah / 100)',
            ],
            [
                'output_key' => 'recommended_inverter_va', 'label' => 'Recommended Inverter Capacity', 'unit' => 'VA', 'sort' => 8,
                // 1.25 = VA/W allowance for a ~0.8 power factor. Not needed for DC-only systems.
                'expression' => 'system_type == "ac_inverter" ? ceil(peak_load_watts * 1.25) : 0',
            ],
            [
                'output_key' => 'inverter_units_needed', 'label' => 'Inverters (1000VA reference)', 'unit' => 'pieces', 'sort' => 9,
                'expression' => 'system_type == "ac_inverter" ? ceil(recommended_inverter_va / 1000) : 0',
            ],
            [
                'output_key' => 'recommended_controller_amperage', 'label' => 'Recommended Charge Controller Rating', 'unit' => 'A', 'sort' => 10,
                // Panel current at 12V with a 25% safety margin.
                'expression' => 'ceil((recommended_panel_watts / 12) * 1.25)',
            ],
            [
                'output_key' => 'controller_units_needed', 'label' => 'Charge Controllers (40A reference)', 'unit' => 'pieces', 'sort' => 11,
                'expression' => 'ceil(recommended_controller_amperage / 40)',
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
            ['output_key' => 'panel_units_needed', 'filters' => ['category' => 'solar-panels', 'attributes' => ['panel_capacity_watts' => '300']]],
            ['output_key' => 'battery_units_needed', 'filters' => ['category' => 'batteries', 'attributes' => ['battery_capacity_ah' => '100']]],
            ['output_key' => 'inverter_units_needed', 'filters' => ['category' => 'inverters', 'attributes' => ['inverter_capacity_va' => '1000']]],
            ['output_key' => 'controller_units_needed', 'filters' => ['category' => 'charge-controllers', 'attributes' => ['controller_amperage_rating' => '40']]],
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
