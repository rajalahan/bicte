<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = $this->load();
        foreach ($rows as $i => $row) {
            Department::updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name'                => $row['name'],
                    'name_en'             => $row['name_en']             ?? null,
                    'summary'             => $row['summary']             ?? null,
                    'icon'                => $row['icon']                ?? null,
                    'room'                => $row['room']                ?? null,
                    'floor'               => $row['floor']               ?? null,
                    'contact_person'      => $row['contact_person']      ?? null,
                    'contact_designation' => $row['contact_designation'] ?? null,
                    'phone'               => $row['phone']               ?? null,
                    'email'               => $row['email']               ?? null,
                    'timings'             => $row['timings']             ?? null,
                    'charter'             => $row['charter']             ?? null,
                    'public_url'          => $row['public_url']          ?? null,
                    'services'            => $row['services']            ?? [],
                    'required_documents'  => $row['required_documents']  ?? [],
                    'process'             => $row['process']             ?? [],
                    'fees'                => $row['fees']                ?? [],
                    'forms'               => $row['forms']               ?? [],
                    'sort_order'          => $i,
                    'is_active'           => true,
                ]
            );
        }
    }

    private function load(): array
    {
        $path = base_path('../frontend/assets/data/departments.json');
        if (!is_file($path)) return [];
        return json_decode(file_get_contents($path), true) ?: [];
    }
}
