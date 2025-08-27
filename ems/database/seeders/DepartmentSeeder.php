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

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {


        $departments = [
            'Product Development',
            'Graphic',
            'SMS',
            'Management',
            'Software Project',
            'System Engineer',
            'IT Support',
            'System Integration',
            'SALE',
            'Artificial Intelligence',
            'Interactive Media',
            'MakeWebEasy',
            'Chatcone',
            'Developer',
        ];

        foreach ($departments as $name) {
            DB::table('ems_department')->insert([
                'dpm_name' => $name,
                'dpm_delete_status' => 'active',
            ]);
        }
    }




}
