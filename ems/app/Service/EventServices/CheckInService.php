<?php

namespace App\Services;

use App\Models\Connect;
use App\Models\Employee;
use App\Models\Event;
use Exception;

/**
 * ชื่อไฟล์: CheckInService.php
 * คำอธิบาย: สำหรับจัดการตรรกะทางธุรกิจ (Business Logic) เกี่ยวกับการ Check-in พนักงาน
 * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
 * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
 */
class CheckInService
{
    /**
     * ชื่อฟังก์ชัน: getEmployeeCheckInData
     * คำอธิบาย: ดึงข้อมูลพนักงานและเตรียมสถานะการ Check-in สำหรับกิจกรรม
     * Input: $eveId (int)
     * Output: array
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
     */
    public function getEmployeeCheckInData($eveId)
    {
        // ดึงข้อมูลพนักงานที่มีสถานะ active
        $activeEmployees = Employee::where('emp_delete_status', 'active')->get();

        foreach ($activeEmployees as $emp) {
            // ตรวจสอบหรือสร้างข้อมูลการเชื่อมต่อกิจกรรม
            Connect::firstOrCreate(
                [
                    'con_employee_id' => $emp->id,
                    'con_event_id'    => $eveId,
                ],
                [
                    'con_checkin_status' => 0,
                    'con_answer'         => 'not_invited',
                    'con_reason'         => null,
                    'con_delete_status'  => 'active',
                ]
            );
        }

        $employees = Employee::with(['department', 'position', 'team', 'company'])
            ->where('emp_delete_status', 'active')
            ->orderBy('emp_id', 'asc')
            ->get();

        $event = Event::findOrFail($eveId);

        return $employees->map(function ($employee) use ($event) {
            $connect = Connect::where('con_employee_id', $employee->id)
                ->where('con_event_id', $event->id)
                ->first();

            return [
                'empId'            => $employee->id,
                'empFullId'        => $employee->emp_id,
                'empCompanyId'     => $employee->company->com_name ?? '-',
                'empFullname'      => $employee->emp_prefix . $employee->emp_firstname . ' ' . $employee->emp_lastname,
                'empNickname'      => $employee->emp_nickname,
                'empTeam'          => $employee->team->tm_name ?? '-',
                'empDepartment'    => $employee->department->dpm_name ?? '-',
                'empPosition'      => $employee->position->pst_name ?? '-',
                'eveId'            => $event->id,
                'eveTitle'         => $event->evn_title,
                'empInviteStatus'  => $connect ? $connect->con_answer : 'not_invited',
                'empCheckinStatus' => $connect ? $connect->con_checkin_status : 0,
            ];
        })->toArray();
    }

    /**
     * ชื่อฟังก์ชัน: toggleAttendance
     * คำอธิบาย: สลับสถานะการเข้างาน (0 หรือ 1) รายบุคคล
     * Input: $eveId (int), $empId (int)
     * Output: Connect Model
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
     */
    public function toggleAttendance($eveId, $empId)
    {
        $record = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$record) {
            return Connect::create([
                'con_employee_id'    => $empId,
                'con_event_id'       => $eveId,
                'con_checkin_status' => 1,
                'con_answer'         => 'not_invited',
                'con_delete_status'  => 'active',
            ]);
        }

        // ใช้ space รอบ binary operators
        $record->con_checkin_status = $record->con_checkin_status ? 0 : 1;
        $record->save();

        return $record;
    }

    /**
     * ชื่อฟังก์ชัน: updateBulkAttendance
     * คำอธิบาย: อัปเดตสถานะการเข้างานพร้อมกันหลายรายการ
     * Input: $eveId (int), $employeeIds (array)
     * Output: int (new status)
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
     */
    public function updateBulkAttendance($eveId, array $employeeIds)
    {
        $hasUnchecked = Connect::where('con_event_id', $eveId)
            ->whereIn('con_employee_id', $employeeIds)
            ->where('con_checkin_status', 0)
            ->exists();

        $newStatus = $hasUnchecked ? 1 : 0;

        Connect::where('con_event_id', $eveId)
            ->whereIn('con_employee_id', $employeeIds)
            ->update(['con_checkin_status' => $newStatus]);

        return $newStatus;
    }
}