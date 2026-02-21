<?php

/**
 * ชื่อไฟล์: CheckInService.php
 * คำอธิบาย: ไฟล์นี้เก็บ (Business Logic) สำหรับจัดการข้อมูล Event ส่วน เช็คชื่อผู้เข้าร่วม
 * ชื่อผู้เขียน/แก้ไข: รวีโรจน์ สนธิ
 * วันที่จัดทำ/แก้ไข: 21 กุมภาพันธ์ 2569
 */


namespace App\Service\EventServices;

use App\Models\Connect;
use Illuminate\Support\Facades\DB;

/* ============================================================
       1) บันทึกคนเข้าร่วม
    ============================================================ */
class CheckInService
{
    public function checkIn($eventId, $employeeId)
    {
        return DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where('con_employee_id', $employeeId)
            ->update(['con_checkin_status' => 'checked_in']);
    }
}
