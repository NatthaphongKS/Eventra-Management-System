<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventsSeeder extends Seeder
{
    public function run(): void
    {
        // ลบของเก่า
        DB::table('ems_event')->delete();

        DB::table('ems_event')->insert([
            // ==================================================
            // DONE (กิจกรรมจบไปแล้ว)
            // ==================================================
            [
                'evn_title' => 'ประชุมสรุปผลโครงการ',
                'evn_category_id' => 1, // ประชุม
                'evn_description' => 'สรุปผลการดำเนินงานและปัญหาที่พบ',
                'evn_date' => now()->subDays(7)->toDateString(),
                'evn_timestart' => '09:00:00',
                'evn_timeend' => '10:30:00',
                'evn_duration' => 90,
                'evn_location' => 'ห้องประชุม A',
                'evn_file' => 'not_have',
                'evn_create_by' => 1,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'done',
            ],
            [
                'evn_title' => 'สัมนาความปลอดภัยไซเบอร์',
                'evn_category_id' => 2, // สัมนา
                'evn_description' => 'ให้ความรู้เรื่องภัยคุกคามไซเบอร์และการป้องกันข้อมูล',
                'evn_date' => now()->subDays(5)->toDateString(),
                'evn_timestart' => '13:00:00',
                'evn_timeend' => '15:30:00',
                'evn_duration' => 150,
                'evn_location' => 'อาคาร IF ห้อง 4T04',
                'evn_file' => 'have',
                'evn_create_by' => 2,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'done',
            ],
            [
                'evn_title' => 'อบรมการใช้งานระบบ EMS',
                'evn_category_id' => 3, // อบรม
                'evn_description' => 'อบรมพนักงานใหม่เกี่ยวกับระบบ Eventra Management System',
                'evn_date' => now()->subDays(3)->toDateString(),
                'evn_timestart' => '09:00:00',
                'evn_timeend' => '12:00:00',
                'evn_duration' => 180,
                'evn_location' => 'ห้องคอมพิวเตอร์ 2',
                'evn_file' => 'have',
                'evn_create_by' => 1,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'done',
            ],
            [
                'evn_title' => 'กิจกรรมเดิน-วิ่งการกุศล',
                'evn_category_id' => 4, // กิจกรรมพิเศษ
                'evn_description' => 'กิจกรรมเพื่อสุขภาพและระดมทุนช่วยเหลือผู้ด้อยโอกาส',
                'evn_date' => now()->subDays(2)->toDateString(),
                'evn_timestart' => '06:00:00',
                'evn_timeend' => '09:00:00',
                'evn_duration' => 180,
                'evn_location' => 'บางแสน ชลบุรี',
                'evn_file' => 'not_have',
                'evn_create_by' => 3,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'done',
            ],

            // ==================================================
            // ONGOING (กำลังดำเนินการ) -> วันนี้
            // ==================================================
            [
                'evn_title' => 'นำเสนอ Demo',
                'evn_category_id' => 4, // กิจกรรมพิเศษ
                'evn_description' => 'นำเสนอ Demo Cycle 3',
                'evn_date' => now()->toDateString(),
                'evn_timestart' => now()->subMinutes(20)->format('H:i:s'),
                'evn_timeend' => now()->addMinutes(90)->format('H:i:s'),
                'evn_duration' => 110,
                'evn_location' => 'ตึก IF-6T05',
                'evn_file' => 'have',
                'evn_create_by' => 2,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'ongoing',
            ],
            [
                'evn_title' => 'ประชุมติดตามความคืบหน้าโปรเจค',
                'evn_category_id' => 1, // ประชุม
                'evn_description' => 'ติดตาม progress งานและแก้ปัญหาที่เจอระหว่างพัฒนา',
                'evn_date' => now()->toDateString(),
                'evn_timestart' => now()->subMinutes(10)->format('H:i:s'),
                'evn_timeend' => now()->addMinutes(50)->format('H:i:s'),
                'evn_duration' => 60,
                'evn_location' => 'ห้องประชุม B',
                'evn_file' => 'not_have',
                'evn_create_by' => 1,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'ongoing',
            ],

            // ==================================================
            // UPCOMING (กำลังจะเกิดขึ้น) -> อนาคต
            // ==================================================
            [
                'evn_title' => 'ประชุมทีม',
                'evn_category_id' => 1, // ประชุม
                'evn_description' => 'ประชุมทีมครั้งที่ 25',
                'evn_date' => now()->addDays(1)->toDateString(),
                'evn_timestart' => '10:00:00',
                'evn_timeend' => '12:00:00',
                'evn_duration' => 120,
                'evn_location' => 'ห้องประชุม CN154',
                'evn_file' => 'not_have',
                'evn_create_by' => 3,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'upcoming',
            ],
            [
                'evn_title' => 'อบรมการทดสอบซอฟต์แวร์ (QA)',
                'evn_category_id' => 3, // อบรม
                'evn_description' => 'อบรมพื้นฐานการทำ Test case, Bug report และการใช้งานเครื่องมือ QA',
                'evn_date' => now()->addDays(2)->toDateString(),
                'evn_timestart' => '09:00:00',
                'evn_timeend' => '12:00:00',
                'evn_duration' => 180,
                'evn_location' => 'ห้องอบรม 101',
                'evn_file' => 'have',
                'evn_create_by' => 2,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'upcoming',
            ],
            [
                'evn_title' => 'สัมนาแนวโน้มเทคโนโลยีปี 2026',
                'evn_category_id' => 2, // สัมนา
                'evn_description' => 'อัปเดตเทคโนโลยีและนวัตกรรมที่กำลังมาแรงในปี 2026',
                'evn_date' => now()->addDays(3)->toDateString(),
                'evn_timestart' => '13:30:00',
                'evn_timeend' => '16:00:00',
                'evn_duration' => 150,
                'evn_location' => 'ห้องสัมมนาใหญ่ อาคารนวัตกรรม',
                'evn_file' => 'have',
                'evn_create_by' => 1,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'upcoming',
            ],
            [
                'evn_title' => 'กิจกรรมปลูกต้นไม้วันสิ่งแวดล้อม',
                'evn_category_id' => 4, // กิจกรรมพิเศษ
                'evn_description' => 'ร่วมกันปลูกต้นไม้เพิ่มพื้นที่สีเขียวและสร้างจิตสำนึกด้านสิ่งแวดล้อม',
                'evn_date' => now()->addDays(5)->toDateString(),
                'evn_timestart' => '07:30:00',
                'evn_timeend' => '10:30:00',
                'evn_duration' => 180,
                'evn_location' => 'สวนสาธารณะบางแสน',
                'evn_file' => 'not_have',
                'evn_create_by' => 4,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'upcoming',
            ],
            [
                'evn_title' => 'Workshop การออกแบบ UI/UX',
                'evn_category_id' => 3, // อบรม
                'evn_description' => 'เวิร์กช็อปออกแบบ UI/UX เบื้องต้น พร้อมทำ prototype',
                'evn_date' => now()->addDays(6)->toDateString(),
                'evn_timestart' => '10:00:00',
                'evn_timeend' => '15:00:00',
                'evn_duration' => 300,
                'evn_location' => 'ห้องคอมพิวเตอร์ 3',
                'evn_file' => 'have',
                'evn_create_by' => 2,
                'evn_created_at' => now(),
                'evn_deleted_at' => null,
                'evn_deleted_by' => null,
                'evn_status' => 'upcoming',
            ],
            [
                'evn_title' => 'ประชุมคณะกรรมการบริหาร',
                'evn_category_id' => 1, // ประชุม
                'evn_description' => 'ประชุมสรุปผลการดำเนินงานและแผนงานไตรมาสถัดไป',
                'evn_date' => now()->addDays(7)->toDateString(),
                'evn_timestart' => '14:00:00',
                'evn_timeend' => '16:00:00',
                'evn_duration' => 120,
                'evn_location' => 'ห้องประชุมใหญ่ ชั้น 5',
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
