<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConnectSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ems_connect')->insert([
            [
                'con_event_id' => 1,
                'con_employee_id' => 1,
                'con_answer' => 'invalid',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 1,
                'con_employee_id' => 2,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 2,
                'con_employee_id' => 3,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 3,
                'con_employee_id' => 1,
                'con_answer' => 'denied',
                'con_reason' => 'ขี้เกียจไป',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            // เพิ่มเติม 16 รายการใหม่
            [
                'con_event_id' => 4,
                'con_employee_id' => 4,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 4,
                'con_employee_id' => 5,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 4,
                'con_employee_id' => 6,
                'con_answer' => 'denied',
                'con_reason' => 'ติดงานนอกสถานที่',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 7,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 5,
                'con_employee_id' => 8,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 6,
                'con_employee_id' => 9,
                'con_answer' => 'denied',
                'con_reason' => 'ป่วย ลาหยุด',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 6,
                'con_employee_id' => 10,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 7,
                'con_employee_id' => 11,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 7,
                'con_employee_id' => 12,
                'con_answer' => 'denied',
                'con_reason' => 'มีอบรมพร้อมกัน',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 8,
                'con_employee_id' => 13,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 1,
                'con_employee_id' => 12,
                'con_answer' => 'invalid',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 9,
                'con_employee_id' => 3,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 10,
                'con_employee_id' => 6,
                'con_answer' => 'denied',
                'con_reason' => 'ลาพักร้อน',
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
            [
                'con_event_id' => 11,
                'con_employee_id' => 2,
                'con_answer' => 'accept',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 1,
            ],
            [
                'con_event_id' => 12,
                'con_employee_id' => 4,
                'con_answer' => 'invalid',
                'con_reason' => null,
                'con_delete_status' => 'active',
                'con_checkin_status' => 0,
            ],
        ]);
    }
}
