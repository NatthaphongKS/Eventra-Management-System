<?php

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
