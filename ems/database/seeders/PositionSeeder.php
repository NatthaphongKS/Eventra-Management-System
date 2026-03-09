<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            // Artificial Intelligence
            ['pst_name' => 'AI Engineer', 'pst_team_id' => 1],
            ['pst_name' => 'AI & ML Engineer', 'pst_team_id' => 1],
            ['pst_name' => 'Data Operation & Support Officer', 'pst_team_id' => 1],
            ['pst_name' => 'Developer', 'pst_team_id' => 1],
            ['pst_name' => 'Front End Developer', 'pst_team_id' => 1],
            ['pst_name' => 'Project Coordinator', 'pst_team_id' => 1],
            ['pst_name' => 'Project Manager', 'pst_team_id' => 1],

            // Chatcone-Dev
            ['pst_name' => 'Business Analyst', 'pst_team_id' => 2],
            ['pst_name' => 'Full Stack Developer', 'pst_team_id' => 2],
            ['pst_name' => 'Full Stack Team Lead', 'pst_team_id' => 2],
            ['pst_name' => 'Product Owner', 'pst_team_id' => 2],
            ['pst_name' => 'Project Coordinator', 'pst_team_id' => 2],

            // Developer
            ['pst_name' => 'Business Analyst', 'pst_team_id' => 4],
            ['pst_name' => 'Developer', 'pst_team_id' => 4],
            ['pst_name' => 'Senior Developer', 'pst_team_id' => 4],
            ['pst_name' => 'Project Manager', 'pst_team_id' => 4],

            // Graphic
            ['pst_name' => 'Graphic Designer', 'pst_team_id' => 5],
            ['pst_name' => 'Senior Graphic Designer', 'pst_team_id' => 5],
            ['pst_name' => 'UI Designer', 'pst_team_id' => 5],

            // IT Support
            ['pst_name' => 'IT Support', 'pst_team_id' => 6],

            // Quality Assurance
            ['pst_name' => 'QA', 'pst_team_id' => 17],
            ['pst_name' => 'Tester', 'pst_team_id' => 17],
            ['pst_name' => 'Senior Tester', 'pst_team_id' => 17],

            // System Engineer
            ['pst_name' => 'DevOps Engineer', 'pst_team_id' => 27],
            ['pst_name' => 'Network & System Engineer', 'pst_team_id' => 27],
            ['pst_name' => 'System Engineer', 'pst_team_id' => 27],

            // System Integration
            ['pst_name' => 'Presales Engineer', 'pst_team_id' => 28],
            ['pst_name' => 'Project Manager', 'pst_team_id' => 28],

            // Byteforge
            ['pst_name' => 'Team Lead', 'pst_team_id' => 29],
            ['pst_name' => 'Planning Manager', 'pst_team_id' => 29],
            ['pst_name' => 'Planning Engineer', 'pst_team_id' => 29],
            ['pst_name' => 'Development Manager', 'pst_team_id' => 29],
            ['pst_name' => 'Development Engineer', 'pst_team_id' => 29],
            ['pst_name' => 'Quality Manager', 'pst_team_id' => 29],
            ['pst_name' => 'Quality Engineer', 'pst_team_id' => 29],
            ['pst_name' => 'Support Manager', 'pst_team_id' => 29],
            ['pst_name' => 'Support Engineer', 'pst_team_id' => 29],
        ];

        foreach ($positions as $pos) {
            DB::table('ems_position')->updateOrInsert(
                [
                    'pst_name' => $pos['pst_name'],
                    'pst_team_id' => $pos['pst_team_id'],
                ],
                [
                    'pst_delete_status' => 'active',
                ]
            );
        }
    }
}
