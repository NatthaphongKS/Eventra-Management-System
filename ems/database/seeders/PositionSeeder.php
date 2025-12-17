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
        // 1) โหลดชื่อทีมทั้งหมดไว้ เพื่อใช้ต่อท้ายเมื่อชื่อซ้ำ
        // สมมติชื่อคอลัมน์ในตารางทีมคือ 'id' และ 'tm_name'
        $teamNames = DB::table('ems_team')
            ->pluck('tm_name', 'id')
            ->toArray(); // [id => name]

        // 2) กำหนดตำแหน่งตามข้อมูลเดิม (id, ชื่อ, team_id)
        $positions = [
            ['id' => 1,  'pst_name' => 'Team Leader',                                 'pst_team_id' => 1],
            ['id' => 2,  'pst_name' => 'Planning Manager',                            'pst_team_id' => 1],
            ['id' => 3,  'pst_name' => 'Planning Engineer',                           'pst_team_id' => 1],
            ['id' => 4,  'pst_name' => 'Development Manager',                         'pst_team_id' => 1],
            ['id' => 5,  'pst_name' => 'Development Engineer',                        'pst_team_id' => 1],
            ['id' => 6,  'pst_name' => 'Quality Manager',                             'pst_team_id' => 1],
            ['id' => 7,  'pst_name' => 'Quality Engineer',                            'pst_team_id' => 1],
            ['id' => 8,  'pst_name' => 'Support Manager',                             'pst_team_id' => 1],
            ['id' => 9,  'pst_name' => 'Support Engineer',                            'pst_team_id' => 1],
            ['id' => 10, 'pst_name' => 'Project Manager (Artificial Intelligence)',   'pst_team_id' => 19],
            ['id' => 11, 'pst_name' => 'Senior Graphic Designer',                     'pst_team_id' => 3],
            ['id' => 12, 'pst_name' => 'Senior Sales Executive',                      'pst_team_id' => 4],
            ['id' => 13, 'pst_name' => 'Senior Application Developer',                'pst_team_id' => 24],
            ['id' => 14, 'pst_name' => 'Senior AR Accountant',                        'pst_team_id' => 6],
            ['id' => 15, 'pst_name' => 'Sales Executive',                             'pst_team_id' => 28],
            ['id' => 16, 'pst_name' => 'Mobile Development Team Lead',                'pst_team_id' => 2],
            ['id' => 17, 'pst_name' => 'Mobile Developer',                            'pst_team_id' => 2],
            ['id' => 18, 'pst_name' => 'Managing Director',                           'pst_team_id' => 2],
            ['id' => 19, 'pst_name' => 'Senior Business Analyst',                     'pst_team_id' => 8],
            ['id' => 20, 'pst_name' => 'Application Developer',                       'pst_team_id' => 24],
            ['id' => 21, 'pst_name' => 'Assistant Project Manager',                   'pst_team_id' => 9],
            ['id' => 22, 'pst_name' => 'System Analyst',                              'pst_team_id' => 4],
            ['id' => 23, 'pst_name' => 'Graphic Designer (Graphic)',                  'pst_team_id' => 3],
            ['id' => 24, 'pst_name' => 'Senior Tester',                               'pst_team_id' => 13],
            ['id' => 25, 'pst_name' => 'Customer Support(Chatcone-SALE&Support)',      'pst_team_id' => 28],
            ['id' => 26, 'pst_name' => 'Senior Tech Specialist',                      'pst_team_id' => 22],
            ['id' => 27, 'pst_name' => 'Business Analyst (Chatcone-Dev)',             'pst_team_id' => 27],
            ['id' => 28, 'pst_name' => 'Project Coordinator (Artificial Intelligence)','pst_team_id' => 19],
            ['id' => 29, 'pst_name' => 'Developer (Artificial Intelligence)',         'pst_team_id' => 19],
            ['id' => 30, 'pst_name' => 'System Engineer',                             'pst_team_id' => 15],
            ['id' => 31, 'pst_name' => 'Helpdesk Officer',                            'pst_team_id' => 8],
            ['id' => 32, 'pst_name' => 'Senior Developer',                            'pst_team_id' => 29],
            ['id' => 33, 'pst_name' => 'HRD & Financial Accounting Director',         'pst_team_id' => 12],
            ['id' => 34, 'pst_name' => 'IT Support (IT Support)',                     'pst_team_id' => 16],
            ['id' => 35, 'pst_name' => 'Android Developer',                           'pst_team_id' => 2],
            ['id' => 36, 'pst_name' => 'GL Accountant',                               'pst_team_id' => 6],
            ['id' => 37, 'pst_name' => 'Tester',                                      'pst_team_id' => 13],
            ['id' => 38, 'pst_name' => 'iOS Developer',                               'pst_team_id' => 2],
            ['id' => 39, 'pst_name' => 'AP Accountant',                               'pst_team_id' => 6],
            ['id' => 40, 'pst_name' => 'Senior Finance Officer',                      'pst_team_id' => 6],
            ['id' => 41, 'pst_name' => 'Network & System Engineer',                   'pst_team_id' => 15],
            ['id' => 42, 'pst_name' => 'AI&ML Engineer',                              'pst_team_id' => 19],
            ['id' => 43, 'pst_name' => 'AI Engineer',                                 'pst_team_id' => 19],
            ['id' => 44, 'pst_name' => 'Front End Developer',                         'pst_team_id' => 19],
            ['id' => 45, 'pst_name' => 'Data Operation & Support Officer',            'pst_team_id' => 19],
            ['id' => 46, 'pst_name' => 'QA (Quality Assurance)',                      'pst_team_id' => 1],
            ['id' => 47, 'pst_name' => 'Presales Engineer',                           'pst_team_id' => 17],
            ['id' => 48, 'pst_name' => 'Developer Team Lead',                         'pst_team_id' => 27],
            ['id' => 49, 'pst_name' => 'Senior Business Analyst 1',                   'pst_team_id' => 2],
            ['id' => 50, 'pst_name' => 'Full Stack Developer',                        'pst_team_id' => 27],
            ['id' => 51, 'pst_name' => 'Full Stack Developer Team Lead',              'pst_team_id' => 27],
            ['id' => 52, 'pst_name' => 'แม่บ้าน',                                     'pst_team_id' => 20],
            ['id' => 53, 'pst_name' => 'UI Designer',                                 'pst_team_id' => 3],
            ['id' => 54, 'pst_name' => 'HR Recruitment',                              'pst_team_id' => 21],
            ['id' => 55, 'pst_name' => 'Senior HR Recruitment',                       'pst_team_id' => 21],
            ['id' => 56, 'pst_name' => 'Product Owner',                               'pst_team_id' => 27],
            ['id' => 57, 'pst_name' => 'Project Coordinator (Chatcone-Dev)',          'pst_team_id' => 27],
            ['id' => 58, 'pst_name' => 'Senior Full Stack Developer',                 'pst_team_id' => 27],
            ['id' => 59, 'pst_name' => 'Business Analyst (Developer)',                'pst_team_id' => 29],
            ['id' => 60, 'pst_name' => 'Developer (Developer)',                       'pst_team_id' => 29],
            ['id' => 61, 'pst_name' => 'General Manager',                             'pst_team_id' => 29],
            ['id' => 62, 'pst_name' => 'Project Coordinator (Developer)',             'pst_team_id' => 29],
            ['id' => 63, 'pst_name' => 'Project Manager (Developer)',                 'pst_team_id' => 29],
            ['id' => 64, 'pst_name' => 'Senior Developer 1',                          'pst_team_id' => 29],
            ['id' => 65, 'pst_name' => '3D Modeler',                                  'pst_team_id' => 22],
            ['id' => 66, 'pst_name' => 'Back End Developer',                          'pst_team_id' => 22],
            ['id' => 67, 'pst_name' => 'Business Analyst (Interactive Media)',        'pst_team_id' => 22],
            ['id' => 68, 'pst_name' => 'Business Analyst 2 (Interactive Media)',      'pst_team_id' => 22],
            ['id' => 69, 'pst_name' => 'Graphic Designer (Interactive Media)',        'pst_team_id' => 22],
            ['id' => 70, 'pst_name' => 'Graphic Designer 1 (Interactive Media)',      'pst_team_id' => 22],
            ['id' => 71, 'pst_name' => 'Project Coordinator (Interactive Media)',     'pst_team_id' => 22],
            ['id' => 72, 'pst_name' => 'Senior Project Manager',                      'pst_team_id' => 22],
            ['id' => 73, 'pst_name' => 'Senior Special 3D Modeler',                   'pst_team_id' => 22],
            ['id' => 74, 'pst_name' => 'Unity Developer 1',                           'pst_team_id' => 22],
            ['id' => 75, 'pst_name' => 'Video Editor (Interactive Media)',            'pst_team_id' => 22],
            ['id' => 76, 'pst_name' => 'Content Marketing',                           'pst_team_id' => 26],
            ['id' => 77, 'pst_name' => 'Digital Marketing',                           'pst_team_id' => 26],
            ['id' => 78, 'pst_name' => 'Facebook Specialist',                         'pst_team_id' => 26],
            ['id' => 79, 'pst_name' => 'Google Ads Specialist',                       'pst_team_id' => 26],
            ['id' => 80, 'pst_name' => 'Online Advertising specialist (Google Adwords)','pst_team_id' => 26],
            ['id' => 81, 'pst_name' => 'SEO Specialist',                              'pst_team_id' => 26],
            ['id' => 82, 'pst_name' => 'Video Editor (Digital Marketing)',            'pst_team_id' => 26],
            ['id' => 83, 'pst_name' => 'Customer Support(SMS-SALE&SUPPORT)',          'pst_team_id' => 4],
        ];

        // 3) นับจำนวนชื่อซ้ำ
        $nameCounts = [];
        foreach ($positions as $pos) {
            $name = $pos['pst_name'];
            if (! isset($nameCounts[$name])) {
                $nameCounts[$name] = 0;
            }
            $nameCounts[$name]++;
        }

        // 4) Insert โดยถ้าชื่อซ้ำมากกว่า 1 ให้ต่อท้ายด้วย (ชื่อทีม)
        foreach ($positions as $pos) {
            $name = $pos['pst_name'];
            if ($nameCounts[$name] > 1) {
                $teamId = $pos['pst_team_id'];
                $teamName = $teamNames[$teamId] ?? null;
                if ($teamName) {
                    $name = "{$name} ({$teamName})";
                }
            }

            DB::table('ems_position')->insert([
                'id'               => $pos['id'],
                'pst_name'         => $name,
                'pst_team_id'      => $pos['pst_team_id'],
                'pst_delete_status'=> 'active',
            ]);
        }
    }
}
