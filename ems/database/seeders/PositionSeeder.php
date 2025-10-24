<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Team;
use App\Models\Department;
use App\Models\Position;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run(): void
    {

        $positions = [
            'Project Manager',
            'Senior Graphic Designer',
            'Senior Sales Executive',
            'Senior Application Developer',
            'Senior AR Accountant',
            'Sales Executive',
            'Mobile Development Team Lead',
            'Mobile Developer',
            'Managing Director',
            'Senior Business Analyst',
            'Application Developer',
            'Assistant Project Manager',
            'System Analyst',
            'Graphic Designer',
            'Senior Tester',
            'Customer Support',
            'Senior Tech Specialist',
            'Business Analyst',
            'Project Coordinator',
            'Developer',
            'System Engineer',
            'Helpdesk Officer',
            'Senior Developer',
            'HRD & Financial Accounting Director',
            'IT Support',
            'Android Developer',
            'GL Accountant',
            'Tester',
            'iOS Developer',
            'AP Accountant',
            'Senior Finance Officer',
            'Network & System Engineer',
            'AI&ML Engineer',
            'Front End Developer',
            'Data Operation & Support Officer',
            'QA (Quality Assurance)',
            'Presales Engineer',
            'Developer Team Lead',
            'Senior Business Analyst 1',
            'Full Stack Developer',
            'แม่บ้าน',
            'UI Designer',
            'HR Recruitment',
            'Senior HR Recruitment',
            'HR Manager',
            'DevOps Engineer',
            'Senior HR',
            'Sales Manager',
            'Sales Supervisor',
            'Website Coordinator',
            'Content Marketing',
            'Online Advertising Specialist (Google Adwords)',
            'Facebook Specialist',
            'Customer Relation Management',
            'Assistant Sales Manager',
            'Administrator',
            'Senior Project Manager',
            'Business Analyst 2',
            '3D Modeler',
            'Video Editor',
            'Graphic Designer 1',
            'Unity Developer 1',
            'Senior Specialist 3D Modeler',
            'Back End Developer',
            'Unity Developer',
            'Digital Marketing',
            'Full Stack Specialist Team Lead',
            'Product Owner',
            'Senior Full Stack Developer',
            'Full Stack Developer Team Lead',
            'Website Administrator',
            'Web Content',
            'SEO Specialist',
            'General Manager',
            'Senior Developer 1',
        ];

        foreach ($positions as $name) {
            DB::table('ems_position')->insert([
                'pst_name' => $name,
                'pst_delete_status' => 'active',
            ]);
        }


    }
}
