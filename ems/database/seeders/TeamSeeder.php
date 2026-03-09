<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [

            // ===============================
            // Artificial Intelligence
            // ===============================
            ['tm_name' => 'Artificial Intelligence', 'tm_department_id' => 1],

            // ===============================
            // Chatcone
            // ===============================
            ['tm_name' => 'Chatcone-Dev', 'tm_department_id' => 2],
            ['tm_name' => 'Chatcone-SALE&Support', 'tm_department_id' => 2],

            // ===============================
            // Developer
            // ===============================
            ['tm_name' => 'Developer', 'tm_department_id' => 3],

            // ===============================
            // Graphic
            // ===============================
            ['tm_name' => 'Graphic', 'tm_department_id' => 4],

            // ===============================
            // IT Support
            // ===============================
            ['tm_name' => 'IT Support', 'tm_department_id' => 5],

            // ===============================
            // Interactive Media
            // ===============================
            ['tm_name' => 'Interactive Media', 'tm_department_id' => 6],

            // ===============================
            // MakeWebEasy
            // ===============================
            ['tm_name' => 'Digital Marketing', 'tm_department_id' => 7],
            ['tm_name' => 'MWE-DEV', 'tm_department_id' => 7],
            ['tm_name' => 'MWE-Graphic', 'tm_department_id' => 7],
            ['tm_name' => 'MWE-SALE&SUPPORT', 'tm_department_id' => 7],

            // ===============================
            // Management
            // ===============================
            ['tm_name' => 'Accounting', 'tm_department_id' => 8],
            ['tm_name' => 'Housekeeper', 'tm_department_id' => 8],
            ['tm_name' => 'Human Resource', 'tm_department_id' => 8],
            ['tm_name' => 'Management', 'tm_department_id' => 8],

            // ===============================
            // Product Development
            // ===============================
            ['tm_name' => 'Mobile', 'tm_department_id' => 9],
            ['tm_name' => 'Quality Assurance', 'tm_department_id' => 9],
            ['tm_name' => 'eMeeting', 'tm_department_id' => 9],

            // ===============================
            // SALE
            // ===============================
            ['tm_name' => 'SALE-PROJECT', 'tm_department_id' => 10],

            // ===============================
            // SMS
            // ===============================
            ['tm_name' => 'SMS-Dev', 'tm_department_id' => 11],
            ['tm_name' => 'SMS-SALE&SUPPORT', 'tm_department_id' => 11],

            // ===============================
            // Software Project
            // ===============================
            ['tm_name' => 'Team Project-1', 'tm_department_id' => 12],
            ['tm_name' => 'Team Project-2', 'tm_department_id' => 12],
            ['tm_name' => 'Team Project-3', 'tm_department_id' => 12],
            ['tm_name' => 'Team Project-4', 'tm_department_id' => 12],
            ['tm_name' => 'Team Project-5', 'tm_department_id' => 12],

            // ===============================
            // System
            // ===============================
            ['tm_name' => 'System Engineer', 'tm_department_id' => 13],
            ['tm_name' => 'System Integration', 'tm_department_id' => 14],

            // ===============================
            // Byteforge
            // ===============================
            ['tm_name' => 'Byteforge', 'tm_department_id' => 15],

            // ===============================
            // Cloud Infrastructure
            // ===============================
            ['tm_name' => 'AWS & Cloud Team', 'tm_department_id' => 16],
            ['tm_name' => 'Infrastructure Operations', 'tm_department_id' => 16],

            // ===============================
            // Data Analytics
            // ===============================
            ['tm_name' => 'Business Intelligence', 'tm_department_id' => 17],
            ['tm_name' => 'Data Engineering', 'tm_department_id' => 17],
            ['tm_name' => 'Analytics & Reporting', 'tm_department_id' => 17],

            // ===============================
            // Customer Success
            // ===============================
            ['tm_name' => 'Customer Support', 'tm_department_id' => 18],
            ['tm_name' => 'Implementation Team', 'tm_department_id' => 18],
            ['tm_name' => 'Customer Experience', 'tm_department_id' => 18],

            // ===============================
            // Marketing & Brand
            // ===============================
            ['tm_name' => 'Brand & Communications', 'tm_department_id' => 19],
            ['tm_name' => 'Digital Marketing', 'tm_department_id' => 19],
            ['tm_name' => 'Content & Social Media', 'tm_department_id' => 19],

            // ===============================
            // Human Resources
            // ===============================
            ['tm_name' => 'Recruitment & Talent', 'tm_department_id' => 20],
            ['tm_name' => 'Employee Relations', 'tm_department_id' => 20],
            ['tm_name' => 'Learning & Development', 'tm_department_id' => 20],

            // ===============================
            // Legal & Compliance
            // ===============================
            ['tm_name' => 'Legal Team', 'tm_department_id' => 21],
            ['tm_name' => 'Compliance & Risk', 'tm_department_id' => 21],
        ];

        foreach ($teams as $team) {
            DB::table('ems_team')->updateOrInsert(
                ['tm_name' => $team['tm_name']],
                ['tm_department_id' => $team['tm_department_id']]
            );
        }
    }
}
