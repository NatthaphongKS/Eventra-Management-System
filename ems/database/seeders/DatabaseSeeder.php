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
use Database\Seeders\PositionSeeder;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\TeamSeeder;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PositionSeeder::class,
            DepartmentSeeder::class,
            TeamSeeder::class,
        ]);



        Company::create([
            'com_name' => 'CN',
        ]);
        Company::create([
            'com_name' => 'CNI',
        ]);
        Company::create([
            'com_name' => 'CNT',
        ]);
        Company::create([
            'com_name' => 'WA',
        ]);


        Employee::create([
            'emp_id'            => 'CN0000',
            'emp_company_id'  => '1',
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
            'emp_create_by'     => null,
            'emp_create_at'     => now(),
            'emp_delete_by'     => null,
            'emp_delete_at'     => null,
        ]);
        Employee::create([
            'emp_id'            => 'CN0001',
            'emp_company_id'  => '1',
            'emp_prefix'        => 'นาย',
            'emp_firstname'     => 'ชิตดนัย',
            'emp_lastname'      => 'หล่อสุด',
            'emp_nickname'      => 'ปอนด์',
            'emp_email'             => '66160083@go.buu.ac.th',
            'emp_phone'             => '1234567809',
            'emp_position_id'   => '2',
            'emp_department_id' => '2',
            'emp_team_id'       => '2',
            'emp_password'      => Hash::make('Pass1234'),
            'emp_permission'        => 'disabled',
            'emp_delete_status' => 'active',
            'emp_create_by'     => 1,
            'emp_create_at'     => now(),
            'emp_delete_by'     => null,
            'emp_delete_at'     => null,
        ]);
        Employee::create([
            'emp_id'            => 'CN0002',
            'emp_company_id'  => '1',
            'emp_prefix'        => 'นาย',
            'emp_firstname'     => 'ณัฐพงศ์',
            'emp_lastname'      => 'คงคำมา',
            'emp_nickname'      => 'โอมซ์',
            'emp_email'             => '66160100@go.buu.ac.th',
            'emp_phone'             => '1234567098',
            'emp_position_id'   => '3',
            'emp_department_id' => '2',
            'emp_team_id'       => '3',
            'emp_password'      => Hash::make('Pass1234'),
            'emp_permission'        => 'disabled',
            'emp_delete_status' => 'active',
            'emp_create_by'     => 1,
            'emp_create_at'     => now(),
            'emp_delete_by'     => null,
            'emp_delete_at'     => null,

        ]);
        Category::create([
            'cat_name' => 'ประชุม',
            'cat_delete_status' => 'active',
            'cat_created_by' => 1,
        ]);
        Category::create([
            'cat_name' => 'สัมนา',
            'cat_delete_status' => 'active',
            'cat_created_by' => 1,
        ]);
        Category::create([
            'cat_name' => 'อบรม',
            'cat_delete_status' => 'active',
            'cat_created_by' => 1,
        ]);
    }
}
