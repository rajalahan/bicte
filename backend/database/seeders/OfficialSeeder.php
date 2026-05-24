<?php

namespace Database\Seeders;

use App\Models\Official;
use Illuminate\Database\Seeder;

class OfficialSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->load();
        $i = 0;
        foreach (['mayor', 'deputy', 'cao'] as $role) {
            if (empty($data[$role])) continue;
            $row = $data[$role];
            Official::updateOrCreate(
                ['role' => $role],
                [
                    'name'        => $row['name']        ?? '—',
                    'designation' => $row['designation'] ?? null,
                    'photo'       => $row['photo']       ?? null,
                    'phone'       => $row['phone']       ?? null,
                    'email'       => $row['email']       ?? null,
                    'message'     => $row['message']     ?? null,
                    'sort_order'  => $i++,
                    'is_active'   => true,
                ]
            );
        }
    }

    private function load(): array
    {
        $path = base_path('../frontend/assets/data/officials.json');
        if (!is_file($path)) return [];
        return json_decode(file_get_contents($path), true) ?: [];
    }
}
