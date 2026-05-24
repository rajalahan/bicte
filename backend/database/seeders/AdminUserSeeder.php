<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@lahanmun.gov.np'],
            ['name' => 'Site Administrator', 'password' => Hash::make('changeme')]
        );
        $admin->syncRoles(['admin']);

        // Reminder for the operator
        $this->command?->warn('  Default admin: admin@lahanmun.gov.np / changeme  (CHANGE IMMEDIATELY)');
    }
}
