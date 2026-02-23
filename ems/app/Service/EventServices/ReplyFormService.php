<?php

/**
 * ชื่อไฟล์: ReplyFormService.php
 * คำอธิบาย: ไฟล์นี้เก็บ (Business Logic) สำหรับจัดการข้อมูล Event ส่วนการตอบกลับลิ้งเข้าร่วมกิจกรรม
 * ชื่อผู้เขียน/แก้ไข: รวีโรจน์ สนธิ
 * วันที่จัดทำ/แก้ไข: 21 กุมภาพันธ์ 2569
 */

namespace App\Service\EventServices;

use App\Models\Connect;
use Illuminate\Support\Facades\DB;

/* ============================================================
      1) เก็บข้อมูลารตอบกลับลง DB
   ============================================================ */
class ReplyFormService
{
    public function reply($eventId, $employeeId, $answer, $reason = null)
    {
        // เปลี่ยนมาใช้ Model Connect
        return Connect::query()
            ->where('con_event_id', $eventId)
            ->where('con_employee_id', $employeeId)
            ->update([
                'con_answer' => $answer,
                'con_reason' => $reason
            ]);
    }
}
