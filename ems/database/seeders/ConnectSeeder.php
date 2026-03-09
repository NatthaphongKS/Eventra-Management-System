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

        $connections = [
            // ==================================================
            // EVENT 1: ประชุมสรุปผลโครงการ (DONE)
            // ==================================================
            ['con_event_id' => 1, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 1, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 1, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 1, 'con_employee_id' => 4, 'con_answer' => 'denied', 'con_reason' => 'ติดงานด่วน', 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 1, 'con_employee_id' => 5, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],

            // ==================================================
            // EVENT 2: สัมนาความปลอดภัยไซเบอร์ (DONE)
            // ==================================================
            ['con_event_id' => 2, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 2, 'con_employee_id' => 8, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 2, 'con_employee_id' => 9, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 2, 'con_employee_id' => 12, 'con_answer' => 'denied', 'con_reason' => 'มีการประชุมอื่น', 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 2, 'con_employee_id' => 17, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],

            // ==================================================
            // EVENT 3: อบรมการใช้งานระบบ EMS (DONE)
            // ==================================================
            ['con_event_id' => 3, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 3, 'con_employee_id' => 6, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 3, 'con_employee_id' => 7, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 3, 'con_employee_id' => 10, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 3, 'con_employee_id' => 14, 'con_answer' => 'pending', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 4: กิจกรรมเดิน-วิ่งการกุศล (DONE)
            // ==================================================
            ['con_event_id' => 4, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 4, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 4, 'con_employee_id' => 5, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 4, 'con_employee_id' => 11, 'con_answer' => 'denied', 'con_reason' => 'ตื่นไม่ไหว 😴', 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 4, 'con_employee_id' => 13, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],

            // ==================================================
            // EVENT 5: นำเสนอ Demo (ONGOING)
            // ==================================================
            ['con_event_id' => 5, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 5, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 5, 'con_employee_id' => 5, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 5, 'con_employee_id' => 6, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 5, 'con_employee_id' => 7, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 5, 'con_employee_id' => 8, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 5, 'con_employee_id' => 9, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 5, 'con_employee_id' => 15, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 6: ประชุมติดตามความคืบหน้าโปรเจค (ONGOING)
            // ==================================================
            ['con_event_id' => 6, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 6, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 6, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 1],
            ['con_event_id' => 6, 'con_employee_id' => 5, 'con_answer' => 'denied', 'con_reason' => 'ติดงานนอกสถานที่', 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 6, 'con_employee_id' => 6, 'con_answer' => 'pending', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 7: ประชุมทีม (UPCOMING)
            // ==================================================
            ['con_event_id' => 7, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 7, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 7, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 7, 'con_employee_id' => 5, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 8: อบรมการทดสอบซอฟต์แวร์ QA (UPCOMING)
            // ==================================================
            ['con_event_id' => 8, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 8, 'con_employee_id' => 13, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 8, 'con_employee_id' => 14, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 8, 'con_employee_id' => 16, 'con_answer' => 'pending', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 9: สัมนาแนวโน้มเทคโนโลยี 2026 (UPCOMING)
            // ==================================================
            ['con_event_id' => 9, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 9, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 9, 'con_employee_id' => 5, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 9, 'con_employee_id' => 8, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 9, 'con_employee_id' => 17, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 10: ปลูกต้นไม้วันสิ่งแวดล้อม (UPCOMING)
            // ==================================================
            ['con_event_id' => 10, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 10, 'con_employee_id' => 4, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 10, 'con_employee_id' => 11, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 10, 'con_employee_id' => 16, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 11: Workshop UI/UX Design (UPCOMING)
            // ==================================================
            ['con_event_id' => 11, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 11, 'con_employee_id' => 10, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 11, 'con_employee_id' => 11, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 11, 'con_employee_id' => 5, 'con_answer' => 'acceptable', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 12: ประชุมคณะกรรมการบริหาร (UPCOMING)
            // ==================================================
            ['con_event_id' => 12, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 12, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 12, 'con_employee_id' => 14, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 12, 'con_employee_id' => 17, 'con_answer' => 'pending', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 13: Cloud Infrastructure Webinar (UPCOMING)
            // ==================================================
            ['con_event_id' => 13, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 13, 'con_employee_id' => 17, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 13, 'con_employee_id' => 18, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 13, 'con_employee_id' => 19, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 13, 'con_employee_id' => 8, 'con_answer' => 'denied', 'con_reason' => 'มีการประชุมด่วน', 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 14: Data Analytics Workshop (UPCOMING)
            // ==================================================
            ['con_event_id' => 14, 'con_employee_id' => 20, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 14, 'con_employee_id' => 21, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 14, 'con_employee_id' => 22, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 14, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 14, 'con_employee_id' => 1, 'con_answer' => 'pending', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 15: Customer Success Kickoff (UPCOMING)
            // ==================================================
            ['con_event_id' => 15, 'con_employee_id' => 23, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 15, 'con_employee_id' => 24, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 15, 'con_employee_id' => 25, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 15, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 16: Marketing Campaign Planning (UPCOMING)
            // ==================================================
            ['con_event_id' => 16, 'con_employee_id' => 26, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 16, 'con_employee_id' => 27, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 16, 'con_employee_id' => 28, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 16, 'con_employee_id' => 2, 'con_answer' => 'pending', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 17: Company Townhall Meeting (UPCOMING)
            // ==================================================
            ['con_event_id' => 17, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 5, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 8, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 10, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 15, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 20, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 25, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 17, 'con_employee_id' => 31, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 18: Legal Compliance Training (UPCOMING)
            // ==================================================
            ['con_event_id' => 18, 'con_employee_id' => 31, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 18, 'con_employee_id' => 32, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 18, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 18, 'con_employee_id' => 29, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 18, 'con_employee_id' => 30, 'con_answer' => 'pending', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 19: Team Building Activity (UPCOMING)
            // ==================================================
            ['con_event_id' => 19, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 19, 'con_employee_id' => 2, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 19, 'con_employee_id' => 5, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 19, 'con_employee_id' => 10, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 19, 'con_employee_id' => 14, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 19, 'con_employee_id' => 20, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 19, 'con_employee_id' => 25, 'con_answer' => 'denied', 'con_reason' => 'มีการประชุมสำคัญ', 'con_delete_status' => 'active', 'con_checkin_status' => 0],

            // ==================================================
            // EVENT 20: Product Development Roadmap (UPCOMING)
            // ==================================================
            ['con_event_id' => 20, 'con_employee_id' => 1, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 20, 'con_employee_id' => 15, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 20, 'con_employee_id' => 3, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 20, 'con_employee_id' => 8, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
            ['con_event_id' => 20, 'con_employee_id' => 13, 'con_answer' => 'accepted', 'con_reason' => null, 'con_delete_status' => 'active', 'con_checkin_status' => 0],
        ];

        DB::table('ems_connect')->insert($connections);
    }
}
