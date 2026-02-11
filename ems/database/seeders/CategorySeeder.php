<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // ลบของเก่า (ปลอดภัยกว่า truncate เพราะมี FK จาก ems_event)
        DB::table('ems_categories')->delete();

        DB::table('ems_categories')->insert([
            [
                'id' => 1,
                'cat_name' => 'ประชุม',
                'cat_delete_status' => 'active',
                'cat_created_at' => now(),
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'id' => 2,
                'cat_name' => 'สัมนา',
                'cat_delete_status' => 'active',
                'cat_created_at' => now(),
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'id' => 3,
                'cat_name' => 'อบรม',
                'cat_delete_status' => 'active',
                'cat_created_at' => now(),
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'id' => 4,
                'cat_name' => 'กิจกรรมพิเศษ',
                'cat_delete_status' => 'active',
                'cat_created_at' => now(),
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],

            [
                'id' => 5,
                'cat_name' => 'Workshop',
                'cat_delete_status' => 'active',
                'cat_created_at' => now(),
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'id' => 6,
                'cat_name' => 'CSR',
                'cat_delete_status' => 'active',
                'cat_created_at' => now(),
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],

            // ====== ตัวอย่าง inactive สำหรับ demo หน้า History ======
            [
                'id' => 7,
                'cat_name' => 'ตัวอย่าง inactive',
                'cat_delete_status' => 'inactive',
                'cat_created_at' => now(),
                'cat_created_by' => 1,
                'cat_deleted_at' => now(),
                'cat_deleted_by' => 1,
            ],
        ]);
    }
}
