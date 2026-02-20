<?php

namespace App\Service\EventServices;

use App\Models\Connect;
use App\Models\Employee;
use App\Models\Event;

class CheckInService
{
    /**
     * เตรียมข้อมูล Connect และดึงข้อมูลพนักงานทั้งหมดสำหรับ Check-in
     */
    public function getEmployeeForCheckin($eveId): array
    {
        $emps = Employee::where('emp_delete_status', 'active')->get();

        foreach ($emps as $emp) {
            Connect::firstOrCreate(
                [
                    'con_employee_id' => $emp->id,
                    'con_event_id'    => $eveId,
                ],
                [
                    'con_checkin_status' => 0,
                    'con_answer'         => 'not_invite',
                    'con_reason'         => null,
                    'con_delete_status'  => 'active',
                ]
            );
        }

        $employees = Employee::with('department', 'position', 'team', 'company')
            ->where('emp_delete_status', 'active')
            ->orderBy('emp_id', 'asc')
            ->get();

        $event = Event::find($eveId);

        $employeesData = [];
        foreach ($employees as $employee) {
            $employeesData[] = [
                'empId'           => $employee->id,
                'empFullId'       => $employee->emp_id,
                'empCompanyId'    => $employee->company->com_name,
                'empFullname'     => $employee->emp_prefix . $employee->emp_firstname . ' ' . $employee->emp_lastname,
                'empNickname'     => $employee->emp_nickname,
                'empTeam'         => $employee->team->tm_name,
                'empDepartment'   => $employee->department->dpm_name,
                'empPosition'     => $employee->position->pst_name,
                'eveId'           => $event->id,
                'eveTitle'        => $event->evn_title,
                'empInviteStatus' => $this->getEmployeeInviteStatus($employee->id, $eveId),
                'empCheckinStatus'=> $this->getEmployeeCheckinStatus($employee->id, $eveId),
            ];
        }

        return $employeesData;
    }

    /**
     * ดึงสถานะการ Invite ของพนักงาน
     */
    public function getEmployeeInviteStatus($empId, $eveId): string
    {
        $connect = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$connect) {
            throw new \RuntimeException("Cannot find connect record for empId={$empId}, eveId={$eveId}");
        }

        return $connect->con_answer; // 'accepted', 'denied', 'invalid', 'not_invited'
    }

    /**
     * ดึงสถานะการ Check-in ของพนักงาน
     */
    public function getEmployeeCheckinStatus($empId, $eveId): int
    {
        $connect = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$connect) {
            throw new \RuntimeException("Cannot find connect record for empId={$empId}, eveId={$eveId}");
        }

        return $connect->con_checkin_status; // 0 หรือ 1
    }

    /**
     * Toggle Check-in status ของพนักงาน (0 <-> 1)
     */
    public function updateEmployeeAttendance($eveId, $empId): array
    {
        $record = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$record) {
            $record = new Connect();
            $record->con_employee_id   = $empId;
            $record->con_event_id      = $eveId;
            $record->con_checkin_status = 1;
            $record->con_answer        = 'not_invited';
            $record->con_reason        = null;
            $record->con_delete_status = 'active';
            $record->save();

            return ['created' => true, 'data' => $record];
        }

        $record->con_checkin_status = $record->con_checkin_status ? 0 : 1;
        $record->save();

        return ['created' => false, 'data' => $record];
    }

    /**
     * Toggle Check-in status สำหรับพนักงานหลายคนพร้อมกัน
     */
    public function updateEmployeeAttendanceAll(array $employeeIds, $eveId): void
    {
        // ตรวจสอบว่ามีรายการที่ยังไม่ Check-in อยู่หรือไม่
        $hasUnchecked = Connect::whereIn('con_employee_id', $employeeIds)
            ->where('con_event_id', $eveId)
            ->where('con_checkin_status', 0)
            ->exists();

        $newStatus = $hasUnchecked ? 1 : 0;

        Connect::whereIn('con_employee_id', $employeeIds)
            ->where('con_event_id', $eveId)
            ->update(['con_checkin_status' => $newStatus]);
    }
}
