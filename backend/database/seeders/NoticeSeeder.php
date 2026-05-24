<?php

namespace Database\Seeders;

use App\Models\Notice;
use Illuminate\Database\Seeder;

class NoticeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->load() as $row) {
            Notice::updateOrCreate(
                ['title' => $row['title']],
                [
                    'summary'       => $row['summary']    ?? null,
                    'category'      => $row['category']   ?? null,
                    'is_active'     => $row['is_active']  ?? true,
                    'is_urgent'     => $row['is_urgent']  ?? false,
                    'published_at'  => $row['published_at'] ?? now(),
                ]
            );
        }
    }

    private function load(): array
    {
        $path = base_path('../frontend/assets/data/notices.json');
        if (!is_file($path)) return [];
        return json_decode(file_get_contents($path), true) ?: [];
    }
}
