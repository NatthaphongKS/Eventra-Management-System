<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder{
    public function run(): void{
        DB::table('ems_event')->insert([
            [
                
                'evn_title' => 'ประชุมวางแผนงานประจำปี',
                'evn_category_id' => 1,
                'evn_description' => 'ประชุมทีมงานเพื่อวางแผนกิจกรรมตลอดปี',
                'evn_date' => '2025-10-25',
                'evn_timestart' => '09:00:00',
                'evn_timeend' => '11:00:00',
                'evn_duration' => 120,
                'evn_location' => 'ห้องประชุม A',
                'evn_file' => 'not_have',
                'evn_create_by' => 1,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'done',
            ],
            [
                'evn_title' => 'อบรมการใช้งานระบบ Eventra',
                'evn_category_id' => 3,
                'evn_description' => 'อบรมการใช้งานระบบ EMS ให้กับพนักงานใหม่',
                'evn_date' => '2025-10-28',
                'evn_timestart' => '13:00:00',
                'evn_timeend' => '16:00:00',
                'evn_duration' => 180,
                'evn_location' => 'ห้องคอมพิวเตอร์ 2',
                'evn_file' => 'have',
                'evn_create_by' => 1,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'upcoming',
            ],
            [
                'evn_title' => 'สัมนาความปลอดภัยไซเบอร์',
                'evn_category_id' => 2,
                'evn_description' => 'สัมนาให้ความรู้เกี่ยวกับการรักษาความปลอดภัยของข้อมูล',
                'evn_date' => '2025-11-02',
                'evn_timestart' => '09:30:00',
                'evn_timeend' => '12:00:00',
                'evn_duration' => 150,
                'evn_location' => 'อาคาร ICT ห้อง 404',
                'evn_file' => 'not_have',
                'evn_create_by' => 1,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'upcoming',
            ],
        ]);
    }
}
