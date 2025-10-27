<?php

namespace App\Http\Controllers;

use App\Models\Connect;

class CheckInController extends Controller
{

    function getEmployeeAttendancers($eveId)
    {
        $data = Connect::with( 'employee', 'event', 'employee.position', 'employee.department', 'employee.team' )
        ->where('con_event_id', $eveId)
        ->get();
        return $data;
    }


    public function updateEmployeeAttendance($conId)
    {
        // 1) หาเรคคอร์ดตามคู่ empId + eveId
        $record = Connect::where('id', $conId)
            ->first();

        // 2) ถ้าไม่เจอ ให้ตอบ 404 แทนที่จะพังเป็น 500
        if (!$record) {
            return response()->json([
                'message' => 'Record not found',
            ], 404);
        }

        // 3) toggle 0 <-> 1
        $record->con_checkin_status = $record->con_checkin_status ? 0 : 1;
        $record->save();

        return response()->json([
            'message' => 'Check-in status updated successfully',
            'new_status' => $record->con_checkin_status,
            'att_id' => $conId,
        ]);
    }
}
