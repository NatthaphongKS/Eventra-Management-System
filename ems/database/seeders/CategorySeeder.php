<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder{
    public function run(): void{
        DB::table('ems_categories')->insert([
            [
                'id' => 1,
                'cat_name' => 'ประชุม',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-08-27',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'id' => 2,
                'cat_name' => 'สัมนา',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-08-27',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'id' => 3,
                'cat_name' => 'อบรม',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-08-27',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'id' => 4,
                'cat_name' => 'กิจกรรมพิเศษ',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-08-27',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
        ]);
    }
}
