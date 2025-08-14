<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Team;
use App\Models\Department;
use App\Models\Position;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Team::create([
            'tm_name' => 'Team1',
        ]);

        Department::create([
            'dpm_name' => 'Department1',
        ]);

        Position::create([

            'pst_name' => 'Administrator',
            'pst_name' => 'Human Resources',
            'pst_name' => 'Position1',
        ]);


        Employee::create([
            'emp_id'            => 'CN0000',
            'emp_prefix'        => 'นาย',
            'emp_firstname'     => 'แอดมิน',
            'emp_lastname'      => 'แอดมิน',
            'emp_nickname'      => 'Admin',
            'emp_email'             => 'admin@admin.com',
            'emp_phone'             => '1234567890',
            'emp_position_id'   => '1',
            'emp_department_id' => '1',
            'emp_team_id'       => '1',
            'emp_password'      => Hash::make('Pass1234'),
            'emp_permission'        => 'enabled',
            'emp_delete_status' => 'active',
        ]);
        Category::create([
            'cat_name' => 'ประชุม',
            'cat_delete_status' => 'active',
        ]);
        Category::create([
            'cat_name' => 'สัมนา',
            'cat_delete_status' => 'active',
        ]);
        Category::create([
            'cat_name' => 'อบรม',
            'cat_delete_status' => 'active',
        ]);
    }
}
