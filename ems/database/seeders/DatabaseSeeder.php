<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Team;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Team::create([
            'etm_name' => 'Team1',
        ]);

        Department::create([
            'edm_name' => 'Department1',
        ]);

        Position::create([

            'ept_name' => 'Administrator',
            'ept_name' => 'Human Resources',
            'ept_name' => 'Position1',
        ]);

        // ปรับชื่อคอลัมน์ให้ตรงกับตารางของคุณ (email/phone หรือ emp_email/emp_phone)
        Employee::create([
            'emp_id'            => 'CN0000',          // ใช้ได้ถ้าเป็นคอลัมน์ string/unique; ถ้า PK auto-increment ให้ลบออก
            'emp_prefix'        => 'นาย',
            'emp_firstname'     => 'แอดมิน',
            'emp_lastname'      => 'แอดมิน',
            'email'             => 'admin@admin.com', // ถ้าตารางใช้ emp_email ให้เปลี่ยนเป็น 'emp_email'
            'phone'             => '1234567890',      // ถ้าใช้ emp_phone ให้เปลี่ยนเป็น 'emp_phone'
            'emp_position_id'   => '1',
            'emp_department_id' => '1',
            'emp_team_id'       => '1',
            'emp_password'      => Hash::make('Pass1234'),
            'emp_status'        => 'enabled',
            'emp_delete_status' => 'active',
        ]);
    }
}
