<?php

/**
 * ชื่อไฟล์: CheckInController.php
 * คำอธิบาย: Controller สำหรับจัดการ Request/Response การ Check-in พนักงานในแต่ละกิจกรรม
 * ผู้เขียน: Yothin S.
 * วันที่แก้ไข: 05/03/2026
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\EventServices\CheckInService;

/**
 * CheckInController จัดการ Request/Response สำหรับการ Check-in พนักงานในแต่ละกิจกรรม
 */
class CheckInController extends Controller
{
    protected CheckInService $checkInService;

    /**
     * สร้าง instance ของ CheckInService เพื่อใช้ใน controller
     *
     * @param CheckInService $checkInService
     */
    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    /**
     * ดึงข้อมูลพนักงานทั้งหมดที่เกี่ยวข้องกับกิจกรรม (Event) สำหรับการ Check-in
     *
     * @param int $eve_id รหัสกิจกรรม
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeForCheckin($eve_id)
    {
        try {
            // เรียก service เพื่อดึงข้อมูลพนักงานสำหรับ check-in
            $employeesData = $this->checkInService->getEmployeeForCheckin($eve_id);
            return response()->json($employeesData, 200);
        } catch (\Exception $e) {
            // กรณีเกิด error ระหว่างดึงข้อมูล
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * อัปเดตสถานะการ Check-in ของพนักงานแต่ละคน (toggle 0/1)
     *
     * @param int $eve_id รหัสกิจกรรม
     * @param int $emp_id รหัสพนักงาน
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEmployeeAttendance($eve_id, $emp_id)
    {
        try {
            // เรียก service เพื่ออัปเดตสถานะ check-in ของพนักงาน
            $result = $this->checkInService->updateEmployeeAttendance($eve_id, $emp_id);

            if ($result['created']) {
                // ถ้าไม่มี record เดิม จะสร้างใหม่
                return response()->json(['message' => 'Record created', 'data' => $result['data']], 201);
            }

            // ถ้ามี record เดิม จะอัปเดตสถานะ
            return response()->json(['message' => 'Record updated', 'data' => $result['data']], 200);
        } catch (\Exception $e) {
            // กรณีเกิด error ระหว่างอัปเดต
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * อัปเดตสถานะการ Check-in ของพนักงานหลายคนพร้อมกัน (toggle ทั้งหมด)
     *
     * @param Request $request ข้อมูล request ที่มี employeeIds
     * @param int $eve_id รหัสกิจกรรม
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateEmployeeAttendanceAll(Request $request, $eve_id)
    {
        try {
            // รับ employeeIds จาก request
            $employeeIds = $request->input('employeeIds');
            // เรียก service เพื่ออัปเดต check-in ของพนักงานทั้งหมด
            $this->checkInService->updateEmployeeAttendanceAll($employeeIds, $eve_id);

            return response()->json(['message' => 'All records updated', 'data' => $eve_id], 200);
        } catch (\Exception $e) {
            // กรณีเกิด error ระหว่างอัปเดต
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ดึงสถานะการ Invite ของพนักงานใน Event
     *
     * @param int $eve_id รหัสกิจกรรม
     * @param int $emp_id รหัสพนักงาน
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeInviteStatus($eve_id, $emp_id)
    {
        try {
            // service รับ ($empId, $eveId) — สลับ order
            $result = $this->checkInService->getEmployeeInviteStatus($emp_id, $eve_id);
            return response()->json(['message' => 'Success', 'data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * ดึงสถานะการ Check-in ของพนักงานใน Event
     *
     * @param int $eve_id รหัสกิจกรรม
     * @param int $emp_id รหัสพนักงาน
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeeCheckinStatus($eve_id, $emp_id)
    {
        try {
            //  service รับ ($empId, $eveId) — สลับ order
            $result = $this->checkInService->getEmployeeCheckinStatus($emp_id, $eve_id);
            return response()->json(['message' => 'Success', 'data' => $result], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
