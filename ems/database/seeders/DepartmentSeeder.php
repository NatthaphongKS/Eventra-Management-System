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
            ['id' => 1, 'dpm_name' => 'Artificial Intelligence'],
            ['id' => 2, 'dpm_name' => 'Chatcone'],
            ['id' => 3, 'dpm_name' => 'Developer'],
            ['id' => 4, 'dpm_name' => 'Graphic'],
            ['id' => 5, 'dpm_name' => 'IT Support'],
            ['id' => 6, 'dpm_name' => 'Interactive Media'],
            ['id' => 7, 'dpm_name' => 'MakeWebEasy'],
            ['id' => 8, 'dpm_name' => 'Management'],
            ['id' => 9, 'dpm_name' => 'Product Development'],
            ['id' => 10, 'dpm_name' => 'SALE'],
            ['id' => 11, 'dpm_name' => 'SMS'],
            ['id' => 12, 'dpm_name' => 'Software Project'],
            ['id' => 13, 'dpm_name' => 'System Engineer'],
            ['id' => 14, 'dpm_name' => 'System Integration'],
            ['id' => 15, 'dpm_name' => 'Byteforge Project'],
        ];

        DB::table('ems_department')->insert($departments);
    }
}
