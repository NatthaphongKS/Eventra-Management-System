<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConnectSeeder extends Seeder
{
    public function run(): void
    {
        // ลบของเก่า
        DB::table('ems_connect')->delete();

        DB::table('ems_connect')->insert([
            // ==================================================
            // EVENT 1: ประชุมสรุปผลโครงการ (DONE)
            // ==================================================
            [
                'con_event_id' => 1,
                'con_employee_id' => 1,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 1,
                'con_employee_id' => 2,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 1,
                'con_employee_id' => 3,
                'con_answer' => 'denied',
                'con_reason' => 'ติดงานด่วน',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 1,
                'con_employee_id' => 4,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 2: สัมนาความปลอดภัยไซเบอร์ (DONE)
            // ==================================================
            [
                'con_event_id' => 2,
                'con_employee_id' => 1,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 2,
                'con_employee_id' => 5,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 2,
                'con_employee_id' => 6,
                'con_answer' => 'invalid',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 2,
                'con_employee_id' => 7,
                'con_answer' => 'denied',
                'con_reason' => 'ไม่สะดวกเดินทาง',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 3: อบรมการใช้งานระบบ EMS (DONE)
            // ==================================================
            [
                'con_event_id' => 3,
                'con_employee_id' => 2,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 3,
                'con_employee_id' => 8,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 3,
                'con_employee_id' => 9,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 3,
                'con_employee_id' => 10,
                'con_answer' => 'denied',
                'con_reason' => 'ติดอบรมอื่น',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 4: กิจกรรมเดิน-วิ่งการกุศล (DONE)
            // ==================================================
            [
                'con_event_id' => 4,
                'con_employee_id' => 3,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 4,
                'con_employee_id' => 4,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 4,
                'con_employee_id' => 11,
                'con_answer' => 'denied',
                'con_reason' => 'ตื่นไม่ไหว 😴',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 4,
                'con_employee_id' => 12,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 5: นำเสนอ Demo (ONGOING)
            // ==================================================

            [
                'con_event_id' => 5,
                'con_employee_id' => 3, // โอม
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 4, // แป้ง
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 5, // ปอนด์
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 6, // ไอซ์
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 7, // คิว
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 8, // ภีม
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 9, // ซัน
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 10, // เป้ย
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 11, // มอส
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 12, // เอิร์ธ
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 13, // โย
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],


            // ==================================================
            // EVENT 6: ประชุมติดตามความคืบหน้าโปรเจค (ONGOING)
            // ==================================================
            [
                'con_event_id' => 6,
                'con_employee_id' => 1,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 6,
                'con_employee_id' => 4,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 6,
                'con_employee_id' => 7,
                'con_answer' => 'denied',
                'con_reason' => 'ติดงานนอกสถานที่',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 6,
                'con_employee_id' => 13,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 7: ประชุมทีม (UPCOMING)
            // ==================================================
            [
                'con_event_id' => 7,
                'con_employee_id' => 1,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 7,
                'con_employee_id' => 2,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 7,
                'con_employee_id' => 3,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 7,
                'con_employee_id' => 5,
                'con_answer' => 'denied',
                'con_reason' => 'มีนัดหมอ',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 8: อบรมการทดสอบซอฟต์แวร์ (QA) (UPCOMING)
            // ==================================================
            [
                'con_event_id' => 8,
                'con_employee_id' => 6,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 8,
                'con_employee_id' => 8,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 8,
                'con_employee_id' => 10,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 8,
                'con_employee_id' => 11,
                'con_answer' => 'denied',
                'con_reason' => 'ไม่ถนัด QA 😅',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 9: สัมนาแนวโน้มเทคโนโลยีปี 2026 (UPCOMING)
            // ==================================================
            [
                'con_event_id' => 9,
                'con_employee_id' => 2,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 9,
                'con_employee_id' => 4,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 9,
                'con_employee_id' => 9,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 9,
                'con_employee_id' => 12,
                'con_answer' => 'invalid',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 10: กิจกรรมปลูกต้นไม้วันสิ่งแวดล้อม (UPCOMING)
            // ==================================================
            [
                'con_event_id' => 10,
                'con_employee_id' => 3,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 10,
                'con_employee_id' => 5,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 10,
                'con_employee_id' => 6,
                'con_answer' => 'denied',
                'con_reason' => 'อยู่ต่างจังหวัด',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 10,
                'con_employee_id' => 13,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 11: Workshop การออกแบบ UI/UX (UPCOMING)
            // ==================================================
            [
                'con_event_id' => 11,
                'con_employee_id' => 7,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 11,
                'con_employee_id' => 8,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 11,
                'con_employee_id' => 9,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 11,
                'con_employee_id' => 10,
                'con_answer' => 'denied',
                'con_reason' => 'ไม่ว่างช่วงบ่าย',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],

            // ==================================================
            // EVENT 12: ประชุมคณะกรรมการบริหาร (UPCOMING)
            // ==================================================
            [
                'con_event_id' => 12,
                'con_employee_id' => 1,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 12,
                'con_employee_id' => 2,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 12,
                'con_employee_id' => 3,
                'con_answer' => 'accepted',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 12,
                'con_employee_id' => 4,
                'con_answer' => 'pending',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
        ]);
    }
}
