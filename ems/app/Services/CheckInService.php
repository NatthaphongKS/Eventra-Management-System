<?php

namespace App\Services;

use App\Models\Connect;
use App\Models\Employee;
use App\Models\Event;

class CheckInService
{
    /**
     * สร้าง Connect records สำหรับพนักงานที่ยังไม่มีข้อมูลใน event นั้น
     *
     * @param int $eveId
     * @return void
     */
    public function ensureConnectRecordsExist(int $eveId): void
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
    }

    /**
     * ดึงข้อมูลพนักงานทั้งหมดพร้อมสถานะ checkin สำหรับ event ที่กำหนด
     *
     * @param int $eveId
     * @return array
     */
    public function getEmployeesForCheckin(int $eveId): array
    {
        $employees = Employee::with('department', 'position', 'team', 'company')
            ->where('emp_delete_status', 'active')
            ->orderBy('emp_id', 'asc')
            ->get();

        $event = Event::find($eveId);
        $employeesData = [];

        foreach ($employees as $employee) {
            $employeesData[] = [
                'empId' => $employee->id,
                'empFullId' => $employee->emp_id,
                'empCompanyId' => $employee->company->com_name,
                'empFullname' => $employee->emp_prefix . $employee->emp_firstname . ' ' . $employee->emp_lastname,
                'empNickname' => $employee->emp_nickname,
                'empTeam' => ($employee->team)->tm_name,
                'empDepartment' => ($employee->department)->dpm_name,
                'empPosition' => ($employee->position)->pst_name,
                'eveId' => $event->id,
                'eveTitle' => $event->evn_title,
                'empInviteStatus' => $this->getInviteStatus($employee->id, $eveId),
                'empCheckinStatus' => $this->getCheckinStatus($employee->id, $eveId),
            ];
        }

        return $employeesData;
    }

    /**
     * ดึงสถานะการตอบรับของพนักงานใน event
     *
     * @param int $empId
     * @param int $eveId
     * @return string|null
     */
    public function getInviteStatus(int $empId, int $eveId): ?string
    {
        $connect = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        return $connect?->con_answer;
    }

    /**
     * ดึงสถานะ checkin ของพนักงานใน event
     *
     * @param int $empId
     * @param int $eveId
     * @return int|null
     */
    public function getCheckinStatus(int $empId, int $eveId): ?int
    {
        $connect = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        return $connect?->con_checkin_status;
    }

    /**
     * Toggle checkin สำหรับพนักงานคนเดียว (ถ้าไม่มี record จะสร้างใหม่)
     *
     * @param int $eveId
     * @param int $empId
     * @return array ['created' => bool, 'record' => Connect]
     */
    public function toggleAttendance(int $eveId, int $empId): array
    {
        $record = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$record) {
            $record = new Connect();
            $record->con_employee_id = $empId;
            $record->con_event_id = $eveId;
            $record->con_checkin_status = 1;
            $record->con_answer = 'not_invited';
            $record->con_reason = null;
            $record->con_delete_status = 'active';
            $record->save();

            return ['created' => true, 'record' => $record];
        }

        $record->con_checkin_status = $record->con_checkin_status ? 0 : 1;
        $record->save();

        return ['created' => false, 'record' => $record];
    }

    /**
     * Toggle checkin สำหรับพนักงานหลายคน
     * ถ้ามีคนที่ยังไม่ checkin → checkin ทั้งหมด, ถ้า checkin หมดแล้ว → ยกเลิกทั้งหมด
     *
     * @param array $employeeIds
     * @param int $eveId
     * @return void
     */
    public function toggleAttendanceAll(array $employeeIds, int $eveId): void
    {
        $hasUnchecked = false;

        foreach ($employeeIds as $empId) {
            $record = Connect::where('con_employee_id', $empId)
                ->where('con_event_id', $eveId)
                ->first();

            if ($record && $record->con_checkin_status == 0) {
                $hasUnchecked = true;
                break;
            }
        }

        $newStatus = $hasUnchecked ? 1 : 0;

        foreach ($employeeIds as $empId) {
            $record = Connect::where('con_employee_id', $empId)
                ->where('con_event_id', $eveId)
                ->first();

            if ($record) {
                $record->con_checkin_status = $newStatus;
                $record->save();
            }
        }
    }
}
