<?php

/**
 * ชื่อไฟล์: EventController.php
 * คำอธิบาย: Controller สำหรับจัดการข้อมูล Event ทั้งหมด
 *          เป็นตัวกลางในการรับ Request และส่งต่อให้ EventService
 * ผู้เขียน/แก้ไข: ChatGPT (ตามคำขอผู้ใช้)
 * วันที่แก้ไขล่าสุด: 19 กุมภาพันธ์ 2026
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Service\EventServices\EventService;
use App\Service\EventServices\ReplyFormService;
use App\Service\EventServices\CheckInService;

class EventController extends Controller
{
    protected EventService $eventService;
    protected ReplyFormService $replyFormService;
    protected CheckInService $checkInService;

    public function __construct(
        EventService $eventService,
        ReplyFormService $replyFormService,
        CheckInService $checkInService
    ) {
        $this->eventService = $eventService;
        $this->replyFormService = $replyFormService;
        $this->checkInService = $checkInService;
    }

    /**
     * ชื่อฟังก์ชัน: index
     * Http request: GET
     * คำอธิบาย: ดึงรายการ Events ทั้งหมด
     * Input: ไม่มี
     * Output: JSON รายการ Events
     * ผู้เขียน/แก้ไข: ChatGPT
     * วันที่แก้ไข: 19 กุมภาพันธ์ 2026
     */
    public function index()
    {
        return response()->json($this->eventService->index());
    }

    /**
     * ชื่อฟังก์ชัน: connectList
     * Http request: GET
     * คำอธิบาย: ดึงรายชื่อพนักงานที่เชื่อมกับ Event ตาม ID
     * Input: event_id
     * Output: รายชื่อพนักงาน + ข้อมูลเชื่อมต่อ
     */
    public function connectList($id)
    {
        return response()->json($this->eventService->connectList($id));
    }

    /**
     * ชื่อฟังก์ชัน: show
     * Http request: GET
     * คำอธิบาย: แสดงรายละเอียด Event พร้อมไฟล์แนบ
     * Input: event_id
     * Output: JSON รายละเอียด Event
     */
    public function show($id)
    {
        $event = $this->eventService->show($id);
        if (!$event) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json($event);
    }

    /**
     * ชื่อฟังก์ชัน: edit_pages
     * Http request: GET
     * คำอธิบาย: ดึงข้อมูลสำหรับหน้าแก้ไข Event
     * Input: event_id
     * Output: Event + Files + Guest IDs
     */
    public function edit_pages($id)
    {
        return response()->json($this->eventService->editPages($id));
    }

    /**
     * ชื่อฟังก์ชัน: store
     * Http request: POST
     * คำอธิบาย: สร้าง Event ใหม่
     * Input: Request (event_title, date, files, employees...)
     * Output: ผลการสร้าง Event
     */
    public function store(Request $request)
    {
        return response()->json($this->eventService->store($request));
    }

    /**
     * ชื่อฟังก์ชัน: update
     * Http request: POST / PUT
     * คำอธิบาย: อัปเดตข้อมูล Event
     * Input: Request (event fields + file operations)
     * Output: ข้อมูล Event ที่อัปเดตแล้ว
     */
    public function update(Request $request)
    {
        return response()->json($this->eventService->update($request));
    }

    /**
     * ชื่อฟังก์ชัน: Eventtable
     * Http request: GET
     * คำอธิบาย: ดึงข้อมูล Events สำหรับ DataTable พร้อม filter + sorting
     * Input: q, sortBy, sortDir
     * Output: JSON List of Events
     */
    public function Eventtable(Request $request)
    {
        return response()->json($this->eventService->eventTable($request));
    }

    /**
     * ชื่อฟังก์ชัน: permission
     * Http request: GET
     * คำอธิบาย: ดึงสิทธิ์การใช้งานของผู้ใช้
     * Input: ไม่มี
     * Output: emp_permission
     */
    public function permission()
    {
        return response()->json([
            'emp_permission' => $this->eventService->permission()
        ]);
    }

    /**
     * ชื่อฟังก์ชัน: deleted
     * Http request: DELETE
     * คำอธิบาย: ลบ Event (soft delete) และแจ้งอีเมล
     * Input: event_id
     * Output: message
     */
    public function deleted($id)
    {
        $result = $this->eventService->deleted($id);

        return response()->json([
            'message' => $result ? 'Event deleted' : 'Not found'
        ]);
    }

    /**
     * ชื่อฟังก์ชัน: getEventParticipants
     * Http request: GET
     * คำอธิบาย: ดึงสถิติจำนวนผู้เข้าร่วมของ Event
     * Input: event_id
     * Output: total, attending, not_attending, pending
     */
    public function getEventParticipants($eventId)
    {
        return response()->json($this->eventService->getEventParticipants($eventId));
    }

    /**
     * ชื่อฟังก์ชัน: eventInfo
     * Http request: GET
     * คำอธิบาย: ดึงข้อมูล metadata สำหรับหน้า Create Event
     * Input: ไม่มี
     * Output: categories, employees, positions, departments, teams
     */
    public function eventInfo()
    {
        return response()->json($this->eventService->eventInfo());
    }

    /**
     * ชื่อฟังก์ชัน: getParticipants
     * Http request: GET
     * คำอธิบาย: ดึงรายชื่อผู้เข้าร่วมตามสถานะ (accepted, denied, pending)
     * Input: event_id, status
     * Output: participants + statistics
     */
    public function getParticipants($id, Request $request)
    {
        return response()->json(
            $this->eventService->getParticipants($id, $request->get('status'))
        );
    }

    /**
     * ชื่อฟังก์ชัน: getAttendanceData
     * Http request: GET
     * คำอธิบาย: ดึงสถิติเช็กอินของ Event
     * Input: event_id
     * Output: actual_attendance, pending, declined, percentage
     */
    public function getAttendanceData($id)
    {
        return response()->json($this->eventService->getAttendanceData($id));
    }

    /**
     * ชื่อฟังก์ชัน: eventStatistics
     * Http request: POST
     * คำอธิบาย: ดึงสถิติรวมของหลาย Event
     * Input: event_ids[]
     * Output: สถิติโดยรวม (department, team, participants)
     */
    public function eventStatistics(Request $request)
    {
        return response()->json(
            $this->eventService->eventStatistics($request->input('event_ids'))
        );
    }
}
