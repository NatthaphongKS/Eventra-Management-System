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
            ['id' => 1,  'tm_name' => 'Byteforge',            'tm_department_id' => 15],
            ['id' => 2,  'tm_name' => 'Mobile',               'tm_department_id' => 1],
            ['id' => 3,  'tm_name' => 'Graphic',              'tm_department_id' => 2],
            ['id' => 4,  'tm_name' => 'SMS-SALE&SUPPORT',     'tm_department_id' => 3],
            ['id' => 5,  'tm_name' => 'SMS-Dev',              'tm_department_id' => 3],
            ['id' => 6,  'tm_name' => 'Accounting',           'tm_department_id' => 4],
            ['id' => 7,  'tm_name' => 'Team Project-1',       'tm_department_id' => 5],
            ['id' => 8,  'tm_name' => 'Team Project-2',       'tm_department_id' => 5],
            ['id' => 9,  'tm_name' => 'Team Project-3',       'tm_department_id' => 5],
            ['id' => 10, 'tm_name' => 'Team Project-4',       'tm_department_id' => 5],
            ['id' => 11, 'tm_name' => 'Team Project-5',       'tm_department_id' => 5],
            ['id' => 12, 'tm_name' => 'Management',           'tm_department_id' => 4],
            ['id' => 13, 'tm_name' => 'Quality Assurance',    'tm_department_id' => 1],
            ['id' => 14, 'tm_name' => 'eMeeting',             'tm_department_id' => 1],
            ['id' => 15, 'tm_name' => 'System Engineer',      'tm_department_id' => 6],
            ['id' => 16, 'tm_name' => 'IT Support',           'tm_department_id' => 7],
            ['id' => 17, 'tm_name' => 'System Integration',   'tm_department_id' => 8],
            ['id' => 18, 'tm_name' => 'SALE-PROJECT',         'tm_department_id' => 9],
            ['id' => 19, 'tm_name' => 'Artificial Intelligence', 'tm_department_id' => 10],
            ['id' => 20, 'tm_name' => 'Housekeeper',          'tm_department_id' => 4],
            ['id' => 21, 'tm_name' => 'Human Resource',       'tm_department_id' => 4],
            ['id' => 22, 'tm_name' => 'Interactive Media',    'tm_department_id' => 11],
            ['id' => 23, 'tm_name' => 'MWE-SALE&SUPPORT',     'tm_department_id' => 12],
            ['id' => 24, 'tm_name' => 'MWE-DEV',              'tm_department_id' => 12],
            ['id' => 25, 'tm_name' => 'MWE-Graphic',          'tm_department_id' => 12],
            ['id' => 26, 'tm_name' => 'Digital Marketing',    'tm_department_id' => 12],
            ['id' => 27, 'tm_name' => 'Chatcone-Dev',         'tm_department_id' => 13],
            ['id' => 28, 'tm_name' => 'Chatcone-SALE&Support','tm_department_id' => 13],
            ['id' => 29, 'tm_name' => 'Developer',            'tm_department_id' => 14],            
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
