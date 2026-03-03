<?php

namespace App\Services;

use App\Models\Connect;
use App\Models\Employee;
use App\Models\Event;
use Illuminate\Support\Facades\Crypt;

/**
 * ชื่อไฟล์: ReplyService.php
 * คำอธิบาย: สำหรับเก็บ Service ที่จัดการตรรกะธุรกิจของการตอบกลับและเข้าร่วมกิจกรรม
 * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
 * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569
 */
class ReplyService
{
    /**
     * ชื่อฟังก์ชัน: decryptUrlData
     * Http request: -
     * คำอธิบาย: ถอดรหัส URL เพื่อดึงข้อมูลรหัสกิจกรรมและรหัสพนักงาน
     * Input: string $encryptURL
     * Output: array
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569
     */
    public function decryptUrlData(string $encryptURL): array
    {
        $decryptURL = Crypt::decryptString($encryptURL);
        return explode('/', $decryptURL);
    }

    /**
     * ชื่อฟังก์ชัน: getReplyDetails
     * Http request: -
     * คำอธิบาย: ดึงข้อมูลพนักงาน กิจกรรม และสถานะการเชื่อมต่อจากฐานข้อมูล
     * Input: int $eveId, int $empId
     * Output: array
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569
     */
    public function getReplyDetails(int $eveId, int $empId): array
    {
        $employee = Employee::query()
            ->select(
                'id',
                'emp_prefix',
                'emp_firstname',
                'emp_lastname',
                'emp_email',
                'emp_phone'
            )
            ->where('id', $empId)
            ->first();

        $event = Event::query()
            ->select(
                'id',
                'evn_title',
                'evn_date',
                'evn_timestart',
                'evn_timeend',
                'evn_location'
            )
            ->where('id', $eveId)
            ->first();

        $connect = Connect::query()
            ->where('con_event_id', $eveId)
            ->where('con_employee_id', $empId)
            ->first();

        return [
            'employee' => $employee,
            'event' => $event,
            'connect' => $connect,
        ];
    }

    /**
     * ชื่อฟังก์ชัน: storeReplyData
     * Http request: -
     * คำอธิบาย: บันทึกหรืออัปเดตข้อมูลการตอบกลับเข้าร่วมกิจกรรมของพนักงาน
     * Input: array $data
     * Output: bool
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 4 มีนาคม 2569
     */
    public function storeReplyData(array $data): bool
    {
        // เข้าร่วม → ล้างเหตุผล
        if ($data['attend'] === 'accept') {
            $data['reason'] = null;
        }

        // อัปเดตถ้ามีคู่นี้อยู่แล้ว
        $updated = Connect::where('con_event_id', $data['evnID'])
            ->where('con_employee_id', $data['empID'])
            ->update([
                'con_answer' => $data['attend'],
                'con_reason' => $data['reason'],
            ]);

        return $updated > 0;
    }
}