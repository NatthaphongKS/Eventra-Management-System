<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = [
            ['Byteforge', 1, 5],
            ['Mobile', 2, 1],
            ['Graphic', 3, 2],
            ['SMS-SALE&SUPPORT', 4, 3],
            ['SMS-Dev', 5, 3],
            ['Accounting', 6, 4],
            ['Team Project-1', 7, 5],
            ['Team Project-2', 8, 5],
            ['Team Project-3', 9, 5],
            ['Team Project-4', 10, 5],
            ['Team Project-5', 11, 5],
            ['Management', 12, 4],
            ['Quality Assurance', 13, 1],
            ['eMeeting', 14, 1],
            ['System Engineer', 15, 6],
            ['IT Support', 16, 7],
            ['System Integration', 17, 8],
            ['SALE-PROJECT', 18, 9],
            ['Artificial Intelligence', 19, 10],
            ['Housekeeper', 20, 4],
            ['Human Resource', 21, 4],
            ['Interactive Media', 22, 11],
            ['MWE-SALE&SUPPORT', 23, 12],
            ['MWE-DEV', 24, 12],
            ['MWE-Graphic', 25, 12],
            ['Digital Marketing', 26, 12],
            ['Chatcone-Dev', 27, 13],
            ['Chatcone-SALE&Support', 28, 13],
            ['Developer', 29, 14],
        ];

        foreach ($teams as $name) {
            DB::table('ems_team')->insert([
                'id' => $name[1],
                'tm_name' => $name[0],
                'tm_department_id' => $name[2],
                'tm_delete_status' => 'active',
            ]);
        }
    }
}
