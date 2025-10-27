<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ems_employees')->insert([
            [
                'emp_id' => 'CN0000',
                'emp_company_id' => 1,
                'emp_prefix' => 'นางสาว',
                'emp_firstname' => 'แอดมิน',
                'emp_lastname' => 'แอดมิน',
                'emp_nickname' => 'Admin',
                'emp_email' => 'admin@admin.com',
                'emp_phone' => '1234567890',
                'emp_position_id' => 1,
                'emp_department_id' => 1,
                'emp_team_id' => 1,
                'emp_password' => Hash::make('password'),
                'emp_permission' => 'enabled',
                'emp_create_at' => now(),
                'emp_create_by' => null,
                'emp_delete_status' => 'active',
            ],
            [
                'emp_id' => 'CN0001',
                'emp_company_id' => 1,
                'emp_prefix' => 'นาย',
                'emp_firstname' => 'ๅๅๅ',
                'emp_lastname' => 'หล่อสุด',
                'emp_nickname' => 'ปอนด์',
                'emp_email' => '66160083@go.buu.ac.th',
                'emp_phone' => '1234567809',
                'emp_position_id' => 2,
                'emp_department_id' => 10,
                'emp_team_id' => 27,
                'emp_password' => Hash::make('password'),
                'emp_permission' => 'disabled',
                'emp_create_at' => now(),
                'emp_create_by' => 1,
                'emp_delete_status' => 'active',
            ],
            [
                'emp_id' => 'CN0002',
                'emp_company_id' => 1,
                'emp_prefix' => 'นาง',
                'emp_firstname' => 'ณัฐพงศ์',
                'emp_lastname' => 'คงคำมา',
                'emp_nickname' => 'โอมซ์ส์',
                'emp_email' => '66160100@go.buu.ac.th',
                'emp_phone' => '1234567098',
                'emp_position_id' => 26,
                'emp_department_id' => 11,
                'emp_team_id' => 26,
                'emp_password' => Hash::make('password'),
                'emp_permission' => 'disabled',
                'emp_create_at' => now(),
                'emp_create_by' => 1,
                'emp_delete_status' => 'active',
            ],
        ]);
    }
}
