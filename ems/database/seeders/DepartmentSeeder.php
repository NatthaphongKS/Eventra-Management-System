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
            ['Product Development', 1],
            ['Graphic', 2],
            ['SMS', 3],
            ['Management', 4],
            ['Software Project', 5],
            ['System Engineer', 6],
            ['IT Support', 7],
            ['System Integration', 8],
            ['SALE', 9],
            ['Artificial Intelligence', 10],
            ['Interactive Media', 11],
            ['MakeWebEasy', 12],
            ['Chatcone', 13],
            ['Developer', 14],
            ['Byteforge Project', 15],
            ['Development', 16],
            ['Quality', 17],
            ['Support', 18],
            ['Planning', 19],
            ['Team Leader', 20],
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
