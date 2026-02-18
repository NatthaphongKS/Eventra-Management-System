<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CheckInService;

/**
 * CheckInController เป็นการจัดการข้อมูลในส่วนของการ Check in พนักงานในแต่ละกิจกรรม
 */
class CheckInController extends Controller
{
    /**
     * Service สำหรับจัดการธุรกิจ logic ของการเช็กอิน
     */
    protected CheckInService $checkInService;

    /**
     * Inject CheckInService เพื่อใช้งานในเมธอดต่าง ๆ ของ controller
     */
    public function __construct(CheckInService $checkInService)
    {
        $this->checkInService = $checkInService;
    }

    /**
     * ดึงรายชื่อพนักงานสำหรับหน้าเช็กอินของกิจกรรม
     *
     * ขั้นตอน:
     * 1) ตรวจสอบ/สร้าง connect records ให้ครบสำหรับ event นี้
     * 2) คืนข้อมูลพนักงานที่ใช้สำหรับเช็กอิน
     */
    public function getEmployeeForCheckin($eveId)
    {
        try {
            // เตรียมข้อมูลความสัมพันธ์พนักงานกับกิจกรรม (connect) ให้พร้อมก่อนดึงรายการ
            $this->checkInService->ensureConnectRecordsExist($eveId);
        } catch (\Exception $e) {
            // หากเกิดข้อผิดพลาดภายใน service ให้ส่ง 500 พร้อมข้อความ error
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }

        // ส่งรายการพนักงานที่สามารถเช็กอินได้ของกิจกรรมนี้
        return $this->checkInService->getEmployeesForCheckin($eveId);
    }

    /**
     * อัปเดตสถานะการเข้าร่วมของพนักงานรายบุคคลในกิจกรรม
     *
     * - ถ้ายังไม่มี record จะถูกสร้างใหม่ (201)
     * - ถ้ามี record อยู่แล้วจะเป็นการอัปเดต (200)
     */
    public function updateEmployeeAttendance($eveId, $empId)
    {
        // Toggle attendance ของพนักงานคนเดียวใน event ที่ระบุ
        $result = $this->checkInService->toggleAttendance($eveId, $empId);

        // กำหนด status code/message ตามผลว่ามีการสร้างข้อมูลใหม่หรืออัปเดตข้อมูลเดิม
        $statusCode = $result['created'] ? 201 : 200;
        $message = $result['created'] ? 'Record created' : 'Record updated';

        // ส่งผลลัพธ์ record ล่าสุดกลับไปให้ client
        return response()->json([
            'message' => $message,
            'data' => $result['record'],
        ], $statusCode);
    }

    /**
     * อัปเดตสถานะการเข้าร่วมของพนักงานหลายคนในกิจกรรมเดียวกัน
     */
    public function updateEmployeeAttendanceAll(Request $request, $eveId)
    {
        // รับรายการรหัสพนักงานจาก request body (เช่น [1,2,3])
        $employeeIds = $request->input('employeeIds');

        // อัปเดต attendance แบบกลุ่มตาม employeeIds ที่ส่งมา
        $this->checkInService->toggleAttendanceAll($employeeIds, $eveId);

        // ตอบกลับว่าอัปเดตเสร็จสิ้น
        return response()->json([
            'message' => 'All records updated',
            'data' => $eveId,
        ], 200);
    }
}
