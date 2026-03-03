<?php
/**
 * ชื่อไฟล์: CheckInService.php
 * คำอธิบาย: Service สำหรับจัดการข้อมูล Check-in ของพนักงานในแต่ละ Event
 * ผู้เขียน: Yothin S.
 * วันที่แก้ไข: 20/02/2026
 */

namespace App\Service\EventServices;

use App\Models\Connect;
use App\Models\Employee;
use App\Models\Event;

class CheckInService
{
    /**
     * เตรียมข้อมูล Connect และดึงข้อมูลพนักงานทั้งหมดสำหรับ Check-in
     */
    /**
     * เตรียมข้อมูล Connect และดึงข้อมูลพนักงานทั้งหมดสำหรับ Check-in
     * 1. สร้าง Connect record สำหรับพนักงานที่ยังไม่มีใน event นี้
     * 2. ดึงข้อมูลพนักงานพร้อมข้อมูลแผนก ตำแหน่ง ทีม บริษัท
     * 3. รวมข้อมูล event และสถานะ invite/check-in ของแต่ละคน
     *
     * @param int $eveId รหัสกิจกรรม
     * @return array รายชื่อพนักงานและข้อมูลที่เกี่ยวข้องกับ event
     */
    public function getEmployeeForCheckin($eveId): array
    {
        // ดึงพนักงานที่ยัง active
        $emps = Employee::where('emp_delete_status', 'active')->get();

        // สร้าง Connect record ถ้ายังไม่มีสำหรับ event นี้
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

        // ดึงข้อมูลพนักงานพร้อม relation
        $employees = Employee::with('department', 'position', 'team', 'company')
            ->where('emp_delete_status', 'active')
            ->orderBy('emp_id', 'asc')
            ->get();

        // ดึงข้อมูล event
        $event = Event::find($eveId);

        $employeesData = [];
        foreach ($employees as $employee) {
            // รวมข้อมูลพนักงานและสถานะต่าง ๆ
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

    /**
     * ดึงสถานะการ Invite ของพนักงานใน event
     *
     * @param int $empId รหัสพนักงาน
     * @param int $eveId รหัสกิจกรรม
     * @return string สถานะ invite (accepted, denied, invalid, not_invited)
     */
    public function getEmployeeInviteStatus($empId, $eveId): string
    {
        // ดึง connect record ของพนักงานกับ event
        $connect = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$connect) {
            throw new \RuntimeException("Cannot find connect record for empId={$empId}, eveId={$eveId}");
        }

        // คืนค่าสถานะ invite
        return $connect->con_answer; // 'accepted', 'denied', 'invalid', 'not_invited'
    }

    /**
     * ดึงสถานะการ Check-in ของพนักงาน
     */

    /**
     * ดึงสถานะการ Check-in ของพนักงานใน event
     *
     * @param int $empId รหัสพนักงาน
     * @param int $eveId รหัสกิจกรรม
     * @return int สถานะ check-in (0 หรือ 1)
     */
    public function getEmployeeCheckinStatus($empId, $eveId): int
    {
        // ดึง connect record ของพนักงานกับ event
        $connect = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$connect) {
            throw new \RuntimeException("Cannot find connect record for empId={$empId}, eveId={$eveId}");
        }

        // คืนค่าสถานะ check-in
        return $connect->con_checkin_status; // 0 หรือ 1
    }

    /**
     * Toggle Check-in status ของพนักงาน (0 <-> 1)
     */

    /**
     * Toggle (สลับ) สถานะ Check-in ของพนักงาน (0 <-> 1)
     *
     * @param int $eveId รหัสกิจกรรม
     * @param int $empId รหัสพนักงาน
     * @return array ข้อมูล record ที่ถูกสร้างหรืออัปเดต
     */
    public function updateEmployeeAttendance($eveId, $empId): array
    {
        // ดึง connect record ของพนักงานกับ event
        $record = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        if (!$record) {
            // ถ้ายังไม่มี record จะสร้างใหม่และ set check-in = 1
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

        // ถ้ามี record เดิม จะสลับสถานะ check-in
        $record->con_checkin_status = $record->con_checkin_status ? 0 : 1;
        $record->save();

        return ['created' => false, 'data' => $record];
    }

    /**
     * Toggle Check-in status สำหรับพนักงานหลายคนพร้อมกัน
     */

    /**
     * Toggle (สลับ) สถานะ Check-in สำหรับพนักงานหลายคนพร้อมกัน
     * ถ้ามีพนักงานที่ยังไม่ check-in จะ set ให้ทุกคน check-in (1)
     * ถ้าทุกคน check-in แล้ว จะ set ให้ทุกคนไม่ check-in (0)
     *
     * @param array $employeeIds รายชื่อรหัสพนักงาน
     * @param int $eveId รหัสกิจกรรม
     */
    public function updateEmployeeAttendanceAll(array $employeeIds, $eveId): void
    {
        // ตรวจสอบว่ามีพนักงานที่ยังไม่ check-in หรือไม่
        $hasUnchecked = Connect::whereIn('con_employee_id', $employeeIds)
            ->where('con_event_id', $eveId)
            ->where('con_checkin_status', 0)
            ->exists();

        // ถ้ามีคนยังไม่ check-in จะ set ให้ทุกคน check-in, ถ้าทุกคน check-in แล้วจะ set กลับเป็น 0
        $newStatus = $hasUnchecked ? 1 : 0;

        Connect::whereIn('con_employee_id', $employeeIds)
            ->where('con_event_id', $eveId)
            ->update(['con_checkin_status' => $newStatus]);
    }
}
