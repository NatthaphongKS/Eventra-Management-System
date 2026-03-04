<?php

/**
 * ชื่อไฟล์: HistoryEmployeeController.php
 * คำอธิบาย: Controller สำหรับจัดการการเรียกดูข้อมูลประวัติพนักงาน (รวมข้อมูลที่ถูกลบ)
 * Input: Request จาก route ฝั่ง API เพื่อเรียกข้อมูลประวัติพนักงาน
 * Output: JSON ข้อมูลรายการประวัติพนักงาน
 * ชื่อผู้เขียน/แก้ไข: Kidrakon Rattanahiran
 * วันที่จัดทำ/แก้ไข: 2026-03-02
 */

namespace App\Http\Controllers;

use App\Service\HistoryServices\HistoryEmployeeService;
use Illuminate\Http\JsonResponse;

class HistoryEmployeeController extends Controller
{
    /**
     * แสดงรายการพนักงานทั้งหมด (รวมถึงข้อมูลที่ถูกลบแล้ว)
     * พร้อมข้อมูลประกอบที่เกี่ยวข้อง โดยตรรกะทางธุรกิจทั้งหมดได้ถูกย้ายไปไว้ในชั้น Service แล้ว
     *
     * @param HistoryEmployeeService $historyEmployeeService
     * @return JsonResponse
     */
    public function index(HistoryEmployeeService $historyEmployeeService): JsonResponse
    {
        $rows = $historyEmployeeService->getAllHistoryEmployees();

        return response()->json($rows);
    }
}
