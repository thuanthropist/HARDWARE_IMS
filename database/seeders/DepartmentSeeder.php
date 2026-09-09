<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\DepartmentAttributeSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Building Materials',
                'code' => 'BLDG',
                'icon' => 'cube',
                'description' => 'Cement, blocks, roofing sheets, sand, aggregates and timber.',
                'sort_order' => 1,
                'categories' => ['Cement', 'Blocks & Bricks', 'Roofing Sheets', 'Sand & Aggregates', 'Timber', 'Reinforcement'],
                'attributes' => [
                    ['key' => 'unit_weight', 'label' => 'Unit Weight', 'type' => 'number', 'unit' => 'kg', 'required' => true, 'sort' => 1],
                    ['key' => 'material_grade', 'label' => 'Material Grade', 'type' => 'select', 'unit' => null, 'required' => false, 'sort' => 2, 'options' => ['32.5N', '42.5N', '42.5R']],
                    ['key' => 'bag_size', 'label' => 'Bag Size', 'type' => 'select', 'unit' => null, 'required' => false, 'sort' => 3, 'options' => ['25kg', '40kg', '50kg']],
                    ['key' => 'bar_diameter', 'label' => 'Bar Diameter', 'type' => 'number', 'unit' => 'mm', 'required' => false, 'sort' => 4],
                ],
            ],
            [
                'name' => 'Plumbing',
                'code' => 'PLUM',
                'icon' => 'wrench',
                'description' => 'Pipes, fittings, valves, taps, water tanks and sanitary ware.',
                'sort_order' => 2,
                'categories' => ['Pipes & Fittings', 'Valves & Taps', 'Water Tanks', 'Sanitary Ware'],
                'attributes' => [
                    ['key' => 'material_type', 'label' => 'Material Type', 'type' => 'select', 'unit' => null, 'required' => true, 'sort' => 1, 'options' => ['PVC', 'PPR', 'Copper', 'Galvanized Steel', 'HDPE']],
                    ['key' => 'pipe_diameter', 'label' => 'Pipe Diameter', 'type' => 'number', 'unit' => 'mm', 'required' => false, 'sort' => 2],
                    ['key' => 'connection_type', 'label' => 'Connection Type', 'type' => 'select', 'unit' => null, 'required' => false, 'sort' => 3, 'options' => ['Threaded', 'Push-fit', 'Welded', 'Flanged', 'Solvent Weld']],
                    ['key' => 'fitting_type', 'label' => 'Fitting Type', 'type' => 'select', 'unit' => null, 'required' => false, 'sort' => 4, 'options' => ['pipe', 'elbow', 'tee', 'valve']],
                ],
            ],
            [
                'name' => 'Electrical',
                'code' => 'ELEC',
                'icon' => 'bolt',
                'description' => 'Cables, switches, sockets, circuit breakers and lighting.',
                'sort_order' => 3,
                'categories' => ['Cables & Wires', 'Switches & Sockets', 'Circuit Breakers', 'Lighting', 'Distribution Boards'],
                'attributes' => [
                    ['key' => 'voltage', 'label' => 'Voltage', 'type' => 'number', 'unit' => 'V', 'required' => true, 'sort' => 1],
                    ['key' => 'wattage', 'label' => 'Wattage', 'type' => 'number', 'unit' => 'W', 'required' => false, 'sort' => 2],
                    ['key' => 'cable_gauge', 'label' => 'Cable Gauge', 'type' => 'select', 'unit' => null, 'required' => false, 'sort' => 3, 'options' => ['1.5mm²', '2.5mm²', '4mm²', '6mm²', '10mm²']],
                    ['key' => 'fixture_type', 'label' => 'Fixture Type', 'type' => 'select', 'unit' => null, 'required' => false, 'sort' => 4, 'options' => ['socket', 'switch']],
                    ['key' => 'amperage', 'label' => 'Amperage', 'type' => 'number', 'unit' => 'A', 'required' => false, 'sort' => 5],
                    ['key' => 'ways', 'label' => 'Ways', 'type' => 'number', 'unit' => 'ways', 'required' => false, 'sort' => 6],
                ],
            ],
            [
                'name' => 'Solar',
                'code' => 'SOLR',
                'icon' => 'sun',
                'description' => 'Solar panels, batteries, inverters and charge controllers.',
                'sort_order' => 4,
                'categories' => ['Solar Panels', 'Batteries', 'Inverters', 'Charge Controllers'],
                'attributes' => [
                    ['key' => 'voltage', 'label' => 'System Voltage', 'type' => 'number', 'unit' => 'V', 'required' => true, 'sort' => 1],
                    ['key' => 'panel_capacity_watts', 'label' => 'Panel Capacity', 'type' => 'number', 'unit' => 'W', 'required' => false, 'sort' => 2],
                    ['key' => 'battery_type', 'label' => 'Battery Type', 'type' => 'select', 'unit' => null, 'required' => false, 'sort' => 3, 'options' => ['Lithium-ion (LiFePO4)', 'Lead-acid (AGM)', 'Gel']],
                    ['key' => 'inverter_capacity_va', 'label' => 'Inverter Capacity', 'type' => 'number', 'unit' => 'VA', 'required' => false, 'sort' => 4],
                    ['key' => 'controller_amperage_rating', 'label' => 'Charge Controller Rating', 'type' => 'number', 'unit' => 'A', 'required' => false, 'sort' => 5],
                    ['key' => 'battery_capacity_ah', 'label' => 'Battery Capacity', 'type' => 'number', 'unit' => 'Ah', 'required' => false, 'sort' => 6],
                ],
            ],
        ];

        foreach ($departments as $data) {
            $department = Department::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'code' => $data['code'],
                    'icon' => $data['icon'],
                    'description' => $data['description'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($data['categories'] as $categoryName) {
                Category::firstOrCreate([
                    'department_id' => $department->id,
                    'slug' => Str::slug($categoryName),
                ], [
                    'name' => $categoryName,
                ]);
            }

            foreach ($data['attributes'] as $attribute) {
                DepartmentAttributeSchema::updateOrCreate(
                    [
                        'department_id' => $department->id,
                        'attribute_key' => $attribute['key'],
                    ],
                    [
                        'label' => $attribute['label'],
                        'input_type' => $attribute['type'],
                        'unit' => $attribute['unit'],
                        'options' => $attribute['options'] ?? null,
                        'is_required' => $attribute['required'],
                        'sort_order' => $attribute['sort'],
                    ]
                );
            }
        }
    }
}
