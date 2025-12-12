<?php

namespace App\Http\Controllers;

use App\Models\Connect;
use App\Models\Employee;
use App\Models\Event;

/**
 * CheckInController เป็นการจัดการข้อมูลในส่วนของการ Check in พนักงานในแต่ละกิจกรรม
 */
class CheckInController extends Controller
{

    function getEmployeeForCheckin($eveId)
    {
        //ข้อมูลพนักงานทั้งหมด (Employees)
        $employees = Employee::with('department', 'position', 'team', 'company')
            ->where('emp_delete_status', 'active')
            ->get();
        $event = Event::find($eveId);


        foreach ($employees as $employee) {
            $employeesData[] = [
                'eveId' => $event->id,
                'eveTitle' => $event->evn_title,
                'empId' => $employee->id,
                'empFullId' => $employee->emp_id,
                'empCompanyId' => $employee->company->com_name,
                'empCheckinStatus' => $this->getCheckinStatus($employee->id, $eveId),
                'empFullname' => $employee->emp_prefix . $employee->emp_firstname . ' ' . $employee->emp_lastname,
                'empNickname' => $employee->emp_nickname,
                'empDepartment' => ($employee->department)->dpm_name,
                'empTeam' => ($employee->team)->tm_name,
                'empPosition' => ($employee->position)->pst_name,
                'empInviteStatus' => $this->getInviteStatus($employee->id, $eveId),
            ];
        }
        return $employeesData;
    }

    function getInviteStatus($empId, $eveId)
    {
        $connect = Connect::Where('con_employee_id', $empId)
            ->Where('con_event_id', $eveId)
            ->first();
        if ($connect) {
            return $connect->con_answer; // 'accept', 'denied', 'invalid'
        } else {
            return 'not_invited'; // กรณีพนักงานไม่ได้รับเชิญ
        }
    }

    function getCheckinStatus($empId, $eveId)
    {
        $connect = Connect::Where('con_employee_id', $empId)
            ->Where('con_event_id', $eveId)
            ->first();;
        if ($connect) {
            return $connect->con_checkin_status; // 0 หรือ 1
        } else {
            return 0; // กรณีพนักงานไม่ได้รับเชิญ
        }
    }



    public function updateEmployeeAttendance($empId, $eveId)
    {
        $record = Connect::where('con_employee_id', $empId)
            ->where('con_event_id', $eveId)
            ->first();

        // ถ้าไม่เจอให้สร้างใหม่
        if (!$record) {
            $record = new Connect();
            $record->con_employee_id = $empId;
            $record->con_event_id = $eveId;
            $record->con_checkin_status = 1; // หรือค่าเริ่มต้นที่ต้องการ
            $record->con_answer = 'invalid';
            $record->con_reason = null;
            $record->con_delete_status = 'active';
            $record->save();

            return response()->json([
                'message' => 'Record created',
                'data' => $record
            ], 201);
        } else {
            // ถ้ามีอยู่แล้ว ให้ toggle 0 <-> 1
            $record->con_checkin_status = $record->con_checkin_status ? 0 : 1;
            $record->save();
        }


        return response()->json([
            'message' => 'Record updated',
            'data' => $record
        ], 200);
    }

    public function updateEmployeeAttendanceAll($eveId)
    {
        $records = Connect::where('con_event_id', $eveId)->get();
        $checkIn = false;
        $nonCheckIn = false;
        foreach ($records as $record) {
            if ($record->con_checkin_status == 1) {
                $checkIn = true;
            } else if ($record->con_checkin_status == 0) {
                $nonCheckIn = true;
            }
            if ($checkIn && $nonCheckIn) {
                break;
            }
        }

        if (!$checkIn && $nonCheckIn) {
            // ถ้า con_checkin_status เป็น 0 ให้เปลี่ยนเป็น 1 ทั้งหมด
            foreach ($records as $record) {
                $record->con_checkin_status = 1;
                $record->save();
            }

            return response()->json([
                'message' => 'All records updated to checked in',
                'data' => $records
            ], 200);
        } else if ($checkIn && !$nonCheckIn) {
            // ถ้า con_checkin_status เป็น 1 ให้เปลี่ยนเป็น 0 ทั้งหมด
            foreach ($records as $record) {
                $record->con_checkin_status = 0;
                $record->save();
            }

            return response()->json([
                'message' => 'All records updated to not checked in',
                'data' => $records
            ], 200);
        } else {
            //ถถ้าอย่างอื่นให้ เปลี่ยนเป็น 1 ทั้งหมด
            foreach ($records as $record) {
                $record->con_checkin_status = 1;
                $record->save();
            }

            foreach ($records as $record) {
                // toggle con_checkin_status สำหรับแต่ละ record
                $record->con_checkin_status = $record->con_checkin_status ? 0 : 1;
                $record->save();
            }

            return response()->json([
                'message' => 'All records updated',
                'data' => $records
            ], 200);
        }
    }
}
