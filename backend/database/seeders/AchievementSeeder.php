<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $i = 0;
        foreach ($this->load() as $row) {
            Achievement::updateOrCreate(
                ['title' => $row['title']],
                [
                    'year'        => $row['year'],
                    'description' => $row['description'] ?? null,
                    'sort_order'  => $i++,
                    'is_active'   => true,
                ]
            );
        }
    }

    private function load(): array
    {
        $path = base_path('../frontend/assets/data/achievements.json');
        if (!is_file($path)) return [];
        return json_decode(file_get_contents($path), true) ?: [];
    }
}
