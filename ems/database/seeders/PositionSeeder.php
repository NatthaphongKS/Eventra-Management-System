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

            // Cloud Infrastructure
            ['pst_name' => 'Cloud Architect', 'pst_team_id' => 30],
            ['pst_name' => 'Cloud Engineer', 'pst_team_id' => 30],
            ['pst_name' => 'Infrastructure Engineer', 'pst_team_id' => 31],
            ['pst_name' => 'Operations Manager', 'pst_team_id' => 31],

            // Data Analytics
            ['pst_name' => 'BI Developer', 'pst_team_id' => 32],
            ['pst_name' => 'BI Analyst', 'pst_team_id' => 32],
            ['pst_name' => 'Data Engineer', 'pst_team_id' => 33],
            ['pst_name' => 'Senior Data Engineer', 'pst_team_id' => 33],
            ['pst_name' => 'Analytics Specialist', 'pst_team_id' => 34],
            ['pst_name' => 'Data Analyst', 'pst_team_id' => 34],

            // Customer Success
            ['pst_name' => 'Support Executive', 'pst_team_id' => 35],
            ['pst_name' => 'Customer Support Specialist', 'pst_team_id' => 35],
            ['pst_name' => 'Implementation Specialist', 'pst_team_id' => 36],
            ['pst_name' => 'Implementation Manager', 'pst_team_id' => 36],
            ['pst_name' => 'Customer Success Manager', 'pst_team_id' => 37],
            ['pst_name' => 'Customer Experience Officer', 'pst_team_id' => 37],

            // Marketing & Brand
            ['pst_name' => 'Brand Manager', 'pst_team_id' => 38],
            ['pst_name' => 'Communications Officer', 'pst_team_id' => 38],
            ['pst_name' => 'Digital Marketing Manager', 'pst_team_id' => 39],
            ['pst_name' => 'Marketing Analyst', 'pst_team_id' => 39],
            ['pst_name' => 'Content Writer', 'pst_team_id' => 40],
            ['pst_name' => 'Social Media Specialist', 'pst_team_id' => 40],

            // Human Resources
            ['pst_name' => 'Recruiter', 'pst_team_id' => 41],
            ['pst_name' => 'Recruitment Manager', 'pst_team_id' => 41],
            ['pst_name' => 'HR Officer', 'pst_team_id' => 42],
            ['pst_name' => 'HR Manager', 'pst_team_id' => 42],
            ['pst_name' => 'Training Coordinator', 'pst_team_id' => 43],
            ['pst_name' => 'L&D Manager', 'pst_team_id' => 43],

            // Legal & Compliance
            ['pst_name' => 'Lawyer', 'pst_team_id' => 44],
            ['pst_name' => 'Legal Counsel', 'pst_team_id' => 44],
            ['pst_name' => 'Compliance Officer', 'pst_team_id' => 45],
            ['pst_name' => 'Risk Manager', 'pst_team_id' => 45],
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
