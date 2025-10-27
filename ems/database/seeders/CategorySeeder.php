<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder{
    public function run(): void{
        DB::table('ems_categories')->insert([
            [
                'cat_name' => 'ประชุม',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-08-27',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'cat_name' => 'สัมนา',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-08-27',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'cat_name' => 'อบรม',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-08-27',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'cat_name' => 'ทำงาน',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-10-23',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
            [
                'cat_name' => 'ทำอาหาร',
                'cat_delete_status' => 'active',
                'cat_created_at' => '2025-10-23',
                'cat_created_by' => 1,
                'cat_deleted_at' => null,
                'cat_deleted_by' => null,
            ],
        ]);
    }
}
