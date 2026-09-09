<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Twiga Cement Ltd', 'contact_person' => 'Juma Mwakalinga', 'phone' => '+255 22 286 1000', 'email' => 'sales@twigacement.example', 'address' => 'Wazo Hill, Dar es Salaam', 'departments' => ['Building Materials']],
            ['name' => 'Kilimanjaro Pipes Co', 'contact_person' => 'Neema Shirima', 'phone' => '+255 27 275 4000', 'email' => 'orders@kilimanjaropipes.example', 'address' => 'Moshi Industrial Area', 'departments' => ['Plumbing']],
            ['name' => 'Victron Energy Tanzania', 'contact_person' => 'David Mushi', 'phone' => '+255 68 123 4567', 'email' => 'info@victron-tz.example', 'address' => 'Mikocheni, Dar es Salaam', 'departments' => ['Solar']],
            ['name' => 'Cabco Electrical Supplies', 'contact_person' => 'Grace Mrema', 'phone' => '+255 75 998 2211', 'email' => 'supply@cabco.example', 'address' => 'Kariakoo, Dar es Salaam', 'departments' => ['Electrical']],
            ['name' => 'Davis & Shirtliff Tanzania', 'contact_person' => 'Peter Kileo', 'phone' => '+255 22 244 6600', 'email' => 'tz@davisandshirtliff.example', 'address' => 'Nyerere Road, Dar es Salaam', 'departments' => ['Solar', 'Plumbing']],
        ];

        foreach ($suppliers as $data) {
            $supplier = Supplier::firstOrCreate(
                ['name' => $data['name']],
                [
                    'contact_person' => $data['contact_person'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'address' => $data['address'],
                    'is_active' => true,
                ]
            );

            $departmentIds = Department::whereIn('name', $data['departments'])->pluck('id');
            $supplier->departments()->syncWithoutDetaching($departmentIds);
        }
    }
}
