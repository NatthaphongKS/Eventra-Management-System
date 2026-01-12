<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $emps = Employee::where('emp_delete_status', 'active')->get();

        try {
            foreach ($emps as $emp) {
                // หาว่ามีข้อมูลหรือยัง ถ้าไม่มีให้สร้างใหม่ (Create) ด้วยค่าที่กำหนด
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
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }

        //ข้อมูลพนักงานทั้งหมด (Employees)
        $employees = Employee::with('department', 'position', 'team', 'company')
            ->where('emp_delete_status', 'active')
            ->orderBy('emp_id', 'asc')
            ->get();
        $event = Event::find($eveId);


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
                'empInviteStatus' => $this->getEmployeeInviteStatus($employee->id, $eveId),
                'empCheckinStatus' => $this->getEmployeeCheckinStatus($employee->id, $eveId),
            ];
        }
        return $employeesData;
    }

    function getEmployeeInviteStatus($empId, $eveId)
    {
        $connect = Connect::Where('con_employee_id', $empId)
            ->Where('con_event_id', $eveId)
            ->first();
        if ($connect) {
            return $connect->con_answer; // 'accepted', 'denied', 'invalid','not_invited'
        } else {
           dd("can not find connect");
        }
    }

    function getEmployeeCheckinStatus($empId, $eveId)
    {
        $connect = Connect::Where('con_employee_id', $empId)
            ->Where('con_event_id', $eveId)
            ->first();;
        if ($connect) {
            return $connect->con_checkin_status; // 0 หรือ 1
        } else {
            return response()->json(['error' => 'Error : getEmployeeCheckinStatus funtion failed', 500 ]);
        }
    }

    public function updateEmployeeAttendance($eveId, $empId)
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
            $record->con_answer = 'not_invited';
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

    public function updateEmployeeAttendanceAll(Request $request, $eveId)
    {
        $notCheckin = true;
        $employeeIds = $request->input('employeeIds');
        foreach ($employeeIds as $empId) {
            $record = Connect::where('con_employee_id', $empId)->where('con_event_id', $eveId)->first();
            if ($record && $record->con_checkin_status == 0) {
                $notCheckin = false;
            }
        }

        if (!$notCheckin) {
            // ถ้ามีอย่างน้อยหนึ่งรายการที่ con_checkin_status เป็น 0 ให้ตั้งค่าเป็น 1 ทั้งหมด
            foreach ($employeeIds as $empId) {
                $record = Connect::where('con_employee_id', $empId)->where('con_event_id', $eveId)->first();
                if ($record) {
                    $record->con_checkin_status = 1;
                    $record->save();
                }
            }
        } else {
            // ถ้าทุกรายการเป็น 1 ให้ตั้งค่าเป็น 0 ทั้งหมด
            foreach ($employeeIds as $empId) {
                $record = Connect::where('con_employee_id', $empId)->where('con_event_id', $eveId)->first();
                if ($record) {
                    $record->con_checkin_status = 0;
                    $record->save();
                }
            }
        }

        return response()->json([
            'message' => 'All records updated',
            'data' => $eveId
        ], 200);
    }
}
