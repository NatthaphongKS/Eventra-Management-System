<?php

/**
 * ชื่อไฟล์: EventController.php
 * คำอธิบาย: Controller สำหรับจัดการข้อมูล Event ทั้งหมด
 *          เป็นตัวกลางในการรับ Request และส่งต่อให้ EventService
 * ผู้เขียน/แก้ไข: Raveroj sonthi
 * วันที่แก้ไขล่าสุด: 20 กุมภาพันธ์ 2026
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\EventServices\EventService;
use App\Service\EventServices\ReplyFormService;


class EventController extends Controller
{
    private EventService $eventService;
    private ReplyFormService $replyFormService;


    public function __construct(
        EventService $eventService,
        ReplyFormService $replyFormService,

    ) {
        $this->eventService = $eventService;
        $this->replyFormService = $replyFormService;

    }

    /* ============================================================
       1) index (รายการ Event ทั้งหมด)
    ============================================================ */
    public function index()
    {
        return response()->json($this->eventService->index());
    }

    /* ============================================================
       2) connectList ดึงรายชื่อพนักงานที่เชื่อมกับ Event ตาม ID
    ============================================================ */
    public function connectList($id)
    {
        return response()->json($this->eventService->connectList($id));
    }

    /* ============================================================
       3) show แสดงรายละเอียด Event พร้อมไฟล์แนบ
    ============================================================ */

    public function show($id)
    {
        $event = $this->eventService->show($id);
        if (!$event) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($event);
    }

     /* ============================================================
       4) editPages ดึงข้อมูลสำหรับหน้าแก้ไข Event
    ============================================================ */
    public function editPages($id) // ผิดต้องกลับมาแก้ เป็น editPages
    {
        return response()->json($this->eventService->editPages($id));
    }

    /* ============================================================
       5) store สร้าง Event ใหม่
    ============================================================ */

    public function store(Request $request)
    {
        return response()->json($this->eventService->store($request));
    }

    /* ============================================================
       6) update (อัปเดต Event)
    ============================================================ */
    public function update(Request $request)
    {
        return response()->json($this->eventService->update($request));
    }
    /* ============================================================
       7) eventTable ดึงข้อมูล Events สำหรับ DataTable พร้อม filter + sorting
    ============================================================ */

    public function eventTable(Request $request)
    {
        return response()->json($this->eventService->eventTable($request));
    }

    /* ============================================================
       8) permission ดึงสิทธิ์การใช้งานของผู้ใช้
    ============================================================ */
    public function permission()
    {
        return response()->json([
            'emp_permission' => $this->eventService->permission()
        ]);
    }
    /* ============================================================
       9) deleted (ลบ Event แบบ soft delete)
    ============================================================ */

    public function deleted($id)
    {
        $result = $this->eventService->deleted($id);

        return response()->json([
            'message' => $result ? 'Event deleted' : 'Not found'
        ]);
    }

    /* ============================================================
       10) getEventParticipants ดึงสถิติจำนวนผู้เข้าร่วมของ Event
    ============================================================ */

    public function getEventParticipants($eventId)
    {
        return response()->json($this->eventService->getEventParticipants($eventId));
    }
    /* ============================================================
       11) eventInfo ดึงข้อมูล
    ============================================================ */

    public function eventInfo()
    {
        return response()->json($this->eventService->eventInfo());
    }

    /* ============================================================
       12) getParticipants ดึงรายชื่อผู้เข้าร่วมตามสถานะ (accepted, denied, pending)
    ============================================================ */
    public function getParticipants($id, Request $request)
    {
        return response()->json(
            $this->eventService->getParticipants($id, $request->get('status'))
        );
    }

   /* ============================================================
       13) getAttendanceData (ดึงสถิติเช็กอิน)
    ============================================================ */
    public function getAttendanceData($id)
    {
        return response()->json($this->eventService->getAttendanceData($id));
    }

    /* ============================================================
       14) eventStatistics ดึงสถิติรวมของหลาย Event
    ============================================================ */
    public function eventStatistics(Request $request)
    {
        return response()->json(
            $this->eventService->eventStatistics($request->input('event_ids'))
        );
    }
}
