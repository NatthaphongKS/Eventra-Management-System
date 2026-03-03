<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CheckInService;
use Exception;

/**
 * ชื่อไฟล์: CheckInController.php
 * คำอธิบาย: ควบคุมการรับ/ส่งคำขอเกี่ยวกับการ Check-in พนักงาน
 * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
 * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
 */
class CheckInController extends Controller
{
    protected $checkInService;

    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    /**
     * ชื่อฟังก์ชัน: getEmployeeForCheckin
     * Http request: get
     * คำอธิบาย: ดึงรายชื่อพนักงานทั้งหมดเพื่อเตรียมการ Check-in
     * Input: $eveId
     * Output: JSON
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
     */
    public function getEmployeeForCheckin($eveId)
    {
        try {
            $data = $this->checkInService->getEmployeeCheckInData($eveId);
            return response()->json($data, 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: updateEmployeeAttendance
     * Http request: post/patch
     * คำอธิบาย: อัปเดตสถานะการเข้างานของพนักงานรายบุคคล
     * Input: $eveId, $empId
     * Output: JSON
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
     */
    public function updateEmployeeAttendance($eveId, $empId)
    {
        try {
            $record = $this->checkInService->toggleAttendance($eveId, $empId);
            return response()->json([
                'message' => 'Attendance updated successfully',
                'data' => $record
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ชื่อฟังก์ชัน: updateEmployeeAttendanceAll
     * Http request: post
     * คำอธิบาย: อัปเดตสถานะการเข้างานของพนักงานกลุ่มที่เลือก
     * Input: Request $request, $eveId
     * Output: JSON
     * ชื่อผู้เขียน/แก้ไข: ชิตดนัย
     * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2569
     */
    public function updateEmployeeAttendanceAll(Request $request, $eveId)
    {
        $employeeIds = $request->input('employeeIds');

        if (empty($employeeIds)) {
            return response()->json(['error' => 'No employees selected'], 400);
        }

        try {
            $newStatus = $this->checkInService->updateBulkAttendance($eveId, $employeeIds);
            return response()->json([
                'message' => 'Bulk attendance updated',
                'status' => $newStatus
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}