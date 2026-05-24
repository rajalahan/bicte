<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $i = 0;
        foreach ($this->load() as $row) {
            $deptId = !empty($row['department'])
                ? Department::where('slug', $row['department'])->value('id')
                : null;

            Faq::updateOrCreate(
                ['intent' => $row['intent']],
                [
                    'department_id' => $deptId,
                    'keywords'      => $row['keywords']  ?? [],
                    'question'      => $row['question'],
                    'answer_ne'     => $row['answer_ne'] ?? '',
                    'answer_en'     => $row['answer_en'] ?? null,
                    'sort_order'    => $i++,
                    'is_active'     => true,
                ]
            );
        }
    }

    private function load(): array
    {
        $path = base_path('../frontend/assets/data/faq.json');
        if (!is_file($path)) return [];
        return json_decode(file_get_contents($path), true) ?: [];
    }
}
