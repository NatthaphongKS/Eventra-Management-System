<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ems_company')->insert([
            [
                'com_name' => 'CN',
                'com_delete_status' => 'active',
            ],
            [
                'com_name' => 'CNI',
                'com_delete_status' => 'active',
            ],
            [
                'com_name' => 'CNT',
                'com_delete_status' => 'active',
            ],
            [
                'com_name' => 'WA',
                'com_delete_status' => 'active',
            ],
        ]);
    }
}
