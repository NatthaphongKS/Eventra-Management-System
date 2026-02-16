<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Category;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ===== Seeder ย่อย (สร้างข้อมูลพื้นฐานก่อน) =====
        $this->call([
            DepartmentSeeder::class,
            TeamSeeder::class,
            PositionSeeder::class,
            CompanySeeder::class,
            EmployeeSeeder::class,
            CategorySeeder::class,
            EventsSeeder::class,     // ← ถ้าไฟล์ชื่อ "EventsSeeder.php"
            ConnectSeeder::class,
            EventFileSeeder::class,
        ]);

        // ===== Company (สำรองไว้กรณีไม่มี Seeder แยก) =====
        Company::firstOrCreate(['com_name' => 'CN']);
        Company::firstOrCreate(['com_name' => 'CNI']);
        Company::firstOrCreate(['com_name' => 'CNT']);
        Company::firstOrCreate(['com_name' => 'WA']);

        // ===== Employees (สำรองไว้กรณีไม่มี Seeder แยก) =====
        Employee::firstOrCreate(
            ['emp_email' => 'admin@admin.com'],
            [
                'emp_id' => 'CN0000',
                'emp_company_id' => 1,
                'emp_prefix' => 'นาย',
                'emp_firstname' => 'แอดมิน',
                'emp_lastname' => 'แอดมิน',
                'emp_nickname' => 'Admin',
                'emp_phone' => '1234567890',
                'emp_position_id' => 1,
                'emp_department_id' => 1,
                'emp_team_id' => 1,
                'emp_password' => Hash::make('Pass1234'),
                'emp_permission' => 'admin',
                'emp_delete_status' => 'active',
                'emp_created_at' => now(),
            ]
        );

        Employee::firstOrCreate(
            ['emp_email' => '66160083@go.buu.ac.th'],
            [
                'emp_id' => 'CN0001',
                'emp_company_id' => 1,
                'emp_prefix' => 'นาย',
                'emp_firstname' => 'ชิตดนัย',
                'emp_lastname' => 'หล่อสุด',
                'emp_nickname' => 'ปอนด์',
                'emp_phone' => '1234567809',
                'emp_position_id' => 2,
                'emp_department_id' => 2,
                'emp_team_id' => 2,
                'emp_password' => Hash::make('Pass1234'),
                'emp_permission' => 'disabled',
                'emp_delete_status' => 'active',
                'emp_create_by' => 1,
                'emp_created_at' => now(),
            ]
        );

        Employee::firstOrCreate(
            ['emp_email' => '66160101@go.buu.ac.th'],
            [
                'emp_id' => 'CN0002',
                'emp_company_id' => 1,
                'emp_prefix' => 'นาย',
                'emp_firstname' => 'ณัฐพงศ์',
                'emp_lastname' => 'คงคำมา',
                'emp_nickname' => 'โอมซ์',
                'emp_phone' => '1234567098',
                'emp_position_id' => 3,
                'emp_department_id' => 2,
                'emp_team_id' => 3,
                'emp_password' => Hash::make('Pass1234'),
                'emp_permission' => 'disabled',
                'emp_delete_status' => 'active',
                'emp_create_by' => 1,
                'emp_created_at' => now(),
            ]
        );

        // ===== Categories (สำรองไว้กรณีไม่มี Seeder แยก) =====
        Category::firstOrCreate(['cat_name' => 'ประชุม'], [
            'cat_delete_status' => 'active',
            'cat_created_by' => 1,
        ]);
        Category::firstOrCreate(['cat_name' => 'สัมนา'], [
            'cat_delete_status' => 'active',
            'cat_created_by' => 1,
        ]);
        Category::firstOrCreate(['cat_name' => 'อบรม'], [
            'cat_delete_status' => 'active',
            'cat_created_by' => 1,
        ]);
    }
}
