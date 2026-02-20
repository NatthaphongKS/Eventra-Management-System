<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\EventServices\CheckInService;

/**
 * CheckInController จัดการ Request/Response สำหรับการ Check-in พนักงานในแต่ละกิจกรรม
 */
class CheckInController extends Controller
{
    protected CheckInService $checkInService;

    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    public function getEmployeeForCheckin($eveId)
    {
        try {
            $employeesData = $this->checkInService->getEmployeeForCheckin($eveId);
            return response()->json($employeesData, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateEmployeeAttendance($eveId, $empId)
    {
        try {
            $result = $this->checkInService->updateEmployeeAttendance($eveId, $empId);

            if ($result['created']) {
                return response()->json(['message' => 'Record created', 'data' => $result['data']], 201);
            }

            return response()->json(['message' => 'Record updated', 'data' => $result['data']], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function updateEmployeeAttendanceAll(Request $request, $eveId)
    {
        try {
            $employeeIds = $request->input('employeeIds');
            $this->checkInService->updateEmployeeAttendanceAll($employeeIds, $eveId);

            return response()->json(['message' => 'All records updated', 'data' => $eveId], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
