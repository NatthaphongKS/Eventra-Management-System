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
            ['id' => 1, 'name' => 'Artificial Intelligence'],
            ['id' => 2, 'name' => 'Chatcone'],
            ['id' => 3, 'name' => 'Developer'],
            ['id' => 4, 'name' => 'Graphic'],
            ['id' => 5, 'name' => 'IT Support'],
            ['id' => 6, 'name' => 'Interactive Media'],
            ['id' => 7, 'name' => 'MakeWebEasy'],
            ['id' => 8, 'name' => 'Management'],
            ['id' => 9, 'name' => 'Product Development'],
            ['id' => 10, 'name' => 'SALE'],
            ['id' => 11, 'name' => 'SMS'],
            ['id' => 12, 'name' => 'Software Project'],
            ['id' => 13, 'name' => 'System Engineer'],
            ['id' => 14, 'name' => 'System Integration'],
            ['id' => 15, 'name' => 'Byteforge Project'],
        ];

        foreach ($departments as $name) {
            DB::table('ems_department')->insert([
                'id' => $name[1],
                'dpm_name' => $name[0],
                'dpm_delete_status' => 'active',
            ]);
        }
    }
}
