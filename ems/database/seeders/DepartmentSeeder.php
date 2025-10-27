<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Product Development',
            'Graphic',
            'SMS',
            'Management',
            'Software Project',
            'System Engineer',
            'IT Support',
            'System Integration',
            'SALE',
            'Artificial Intelligence',
            'Interactive Media',
            'MakeWebEasy',
            'Chatcone',
            'Developer',
        ];

        foreach ($departments as $name) {
            DB::table('ems_department')->insert([
                'dpm_name' => $name,
                'dpm_delete_status' => 'active',
            ]);
        }
    }
}
