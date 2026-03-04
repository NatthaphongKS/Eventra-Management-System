<?php

/**
 * ชื่อไฟล์: HistoryCategoryController.php
 * คำอธิบาย: Controller สำหรับจัดการการเรียกดูข้อมูลประวัติหมวดหมู่ผ่านชั้น Service
 * Input: Request จาก route ฝั่ง API เพื่อเรียกข้อมูลประวัติหมวดหมู่
 * Output: JSON ข้อมูลรายการประวัติหมวดหมู่
 * ชื่อผู้เขียน/แก้ไข: kidrakon rattanahiran
 * วันที่จัดทำ/แก้ไข: 2026-03-02
 */

namespace App\Http\Controllers;

use App\Service\HistoryServices\HistoryCategoryService;
use Illuminate\Http\JsonResponse;

class HistoryCategoryController extends Controller
{
    /**
     * @var HistoryCategoryService
     */
    private $historyCategoryService;

    public function __construct(HistoryCategoryService $historyCategoryService)
    {
        $this->historyCategoryService = $historyCategoryService;
    }

    public function index(): JsonResponse
    {
        $rows = $this->historyCategoryService->getAllHistoryCategories();

        return response()->json($rows);
    }
}
