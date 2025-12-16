<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['Team Leader', 1, 1],
            ['Planning Manager', 2, 1],
            ['Planning Engineer', 3, 1],
            ['Development Manager', 4, 1],
            ['Development Engineer', 5, 1],
            ['Quality Manager', 6, 1],
            ['Quality Engineer', 7, 1],
            ['Support Manager', 8, 1],
            ['Support Engineer', 9, 1],
            ['Project Manager (Artificial Intelligence)', 10, 19],
            ['Senior Graphic Designer', 11, 3],
            ['Senior Sales Executive', 12, 4],
            ['Senior Application Developer', 13, 24],
            ['Senior AR Accountant', 14, 6],
            ['Sales Executive', 15, 28],
            ['Mobile Development Team Lead', 16, 2],
            ['Mobile Developer', 17, 2],
            ['Managing Director', 18, 2],
            ['Senior Business Analyst', 19, 8],
            ['Application Developer', 20, 24],
            ['Assistant Project Manager', 21, 9],
            ['System Analyst', 22, 4],
            ['Graphic Designer (Graphic)', 23, 3],
            ['Senior Tester', 24, 13],
            ['Customer Support(Chatcone-SALE&Support)', 25, 28],
            ['Senior Tech Specialist', 26, 22],
            ['Business Analyst (Chatcone-Dev)', 27, 27],
            ['Project Coordinator (Artificial Intelligence)', 28, 19],
            ['Developer (Artificial Intelligence)', 29, 19],
            ['System Engineer', 30, 15],
            ['Helpdesk Officer', 31, 8],
            ['Senior Developer', 32, 29],
            ['HRD & Financial Accounting Director', 33, 12],
            ['IT Support (IT Support)', 34, 16],
            ['Android Developer', 35, 2],
            ['GL Accountant', 36, 6],
            ['Tester', 37, 13],
            ['iOS Developer', 38, 2],
            ['AP Accountant', 39, 6],
            ['Senior Finance Officer', 40, 6],
            ['Network & System Engineer', 41, 15],
            ['AI&ML Engineer', 42, 19],
            ['AI Engineer', 43, 19],
            ['Front End Developer', 44, 19],
            ['Data Operation & Support Officer', 45, 19],
            ['QA (Quality Assurance)', 46, 1],
            ['Presales Engineer', 47, 17],
            ['Developer Team Lead', 48, 27],
            ['Senior Business Analyst 1', 49, 2],
            ['Full Stack Developer', 50, 27],
            ['Full Stack Developer Team Lead', 51, 27],
            ['แม่บ้าน', 52, 20],
            ['UI Designer', 53, 3],
            ['HR Recruitment', 54, 21],
            ['Senior HR Recruitment', 55, 21],
            ['Product Owner', 56, 27],
            ['Project Coordinator (Chatcone-Dev)', 57, 27],
            ['Senior Full Stack Developer', 58, 27],
            ['Business Analyst (Developer)', 59, 29],
            ['Developer (Developer)', 60, 29],
            ['General Manager', 61, 29],
            ['Project Coordinator (Developer)', 62, 29],
            ['Project Manager (Developer)', 63, 29],
            ['Senior Developer 1', 64, 29],
            ['3D Modeler', 65, 22],
            ['Back End Developer', 66, 22],
            ['Business Analyst (Interactive Media)', 67, 22],
            ['Business Analyst 2 (Interactive Media)', 68, 22],
            ['Graphic Designer (Interactive Media)', 69, 22],
            ['Graphic Designer 1 (Interactive Media)', 70, 22],
            ['Project Coordinator (Interactive Media)', 71, 22],
            ['Senior Project Manager', 72, 22],
            ['Senior Special 3D Modeler', 73, 22],
            ['Unity Developer 1', 74, 22],
            ['Video Editor (Interactive Media)', 75, 22],
            ['Content Marketing', 76, 26],
            ['Digital Marketing', 77, 26],
            ['Facebook Specialist', 78, 26],
            ['Google Ads Specialist', 79, 26],
            ['Online Advertising specialist (Google Adwords)', 80, 26],
            [' SEO Specialist', 81, 26],
            ['Video Editor (Digital Marketing)', 82, 26],
            ['Customer Support(SMS-SALE&SUPPORT)', 83, 4],

           
        ];

        foreach ($positions as $name) {
            DB::table('ems_position')->insert([
                'id' => $name[1],
                'pst_name' => $name[0],
                'pst_team_id' => $name[2],
                'pst_delete_status' => 'active',
            ]);
        }
    }
}
