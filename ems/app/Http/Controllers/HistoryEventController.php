<?php

/**
 * ชื่อไฟล์: HistoryEventController.php
 * คำอธิบาย: Controller สำหรับจัดการข้อมูลกิจกรรมที่ถูกลบ (Event History)
 * Http request: GET /history/events
 * Input: -
 * Output: JSON Array ของ Event ที่ถูกลบ พร้อมชื่อผู้สร้างและผู้ลบ
 * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
 * วันที่จัดทำ/แก้ไข: 2025-12-14
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\HistoryServices\HistoryEventService;

class HistoryEventController extends Controller
{
    private $historyEventService;

    /**
     * Constructor - ทำการ inject HistoryEventService
     *
     * @param HistoryEventService $historyEventService
     */
    public function __construct(HistoryEventService $historyEventService)
    {
        $this->historyEventService = $historyEventService;
    }
    /**
     * ชื่อฟังก์ชัน: eventInfo
     * Http request: GET
     * คำอธิบาย: ฟังก์ชันนี้ใช้สำหรับดึงข้อมูล Event ที่มีสถานะ deleted พร้อมชื่อผู้สร้างและผู้ลบ
     * Input: -
     * Output: ข้อมูล Event ที่ถูกลบทั้งหมด (JSON)
     * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
     * วันที่จัดทำ/แก้ไข: 2025-12-14
     */
    public function eventInfo()
    {
        $data = $this->historyEventService->getAllDeletedEvents();
        return response()->json($data);
    }

    /**
     * ชื่อฟังก์ชัน: show
     * Http request: GET
     * คำอธิบาย: ดึงข้อมูลรายละเอียดของ Event ที่ถูกลบไปแล้วตาม ID
     * Input: $id (Event ID)
     * Output: ข้อมูล Event รายตัว (JSON)
     * ชื่อผู้เขียน/แก้ไข: Mr.Suphanut Pangot
     * วันที่จัดทำ/แก้ไข: 2025-12-14
     */
    public function show($id)
    {
        $event = $this->historyEventService->getDeletedEventById($id);

        if (!$event) {
            return response()->json(['message' => 'Event not found or not deleted'], 404);
        }

        return response()->json($event);
    }
}