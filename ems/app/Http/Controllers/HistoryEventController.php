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
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Category;
use App\Models\Event;

class HistoryEventController extends Controller
{
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
        // ดึงเฉพาะ Event ที่มีสถานะ deleted
        $events = Event::select([
                'ems_event.id',
                'ems_event.evn_title',
                'ems_event.evn_created_at',
                'ems_event.evn_deleted_at',
                // เลือกชื่อคนสร้าง (Alias: creator)
                'creator.emp_firstname as creator_first',
                'creator.emp_lastname as creator_last',
                'creator.emp_nickname as creator_nick',
                // เลือกชื่อคนลบ (Alias: deleter)
                'deleter.emp_firstname as deleter_first',
                'deleter.emp_lastname as deleter_last',
                'deleter.emp_nickname as deleter_nick',
            ])
            // Join เอาคนสร้าง
            ->leftJoin('ems_employees as creator', 'ems_event.evn_create_by', '=', 'creator.id')
            // Join เอาคนลบ
            ->leftJoin('ems_employees as deleter', 'ems_event.evn_deleted_by', '=', 'deleter.id')
            ->where('ems_event.evn_status', 'deleted')
            ->orderBy('ems_event.evn_deleted_at', 'desc') // เรียงตามวันที่ลบล่าสุด
            ->get();

        // จัด Format ข้อมูลส่งกลับไปให้ Vue
        $data = $events->map(function ($ev) {
            return [
                'id' => $ev->id,
                'evn_title' => $ev->evn_title,
                'created_at' => $ev->evn_created_at,
                'deleted_at' => $ev->evn_deleted_at,
                // รวมชื่อ: ถ้ามีชื่อเล่นให้แสดงชื่อเล่น ถ้าไม่มีใช้ชื่อจริง
                'created_by_name' => $ev->creator_first ? ($ev->creator_first . ' ' . $ev->creator_last) : '-',
                'deleted_by_name' => $ev->deleter_first ? ($ev->deleter_first . ' ' . $ev->deleter_last) : '-',
            ];
        });

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
        // ใช้ ->withTrashed() เพื่อให้หาเจอแม้จะถูก Soft Delete ไปแล้ว
        // หรือถ้าใช้ evn_status = 'deleted' ก็ใช้ where ปกติได้เลย
        $event = Event::where('id', $id)
            ->where('evn_status', 'deleted') // เช็คว่าเป็นตัวที่ถูกลบจริง
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found or not deleted'], 404);
        }

        // ดึงไฟล์แนบ หรือ ความสัมพันธ์อื่นๆ เพิ่มเติมถ้าจำเป็น
        // $event->load('attachments'); 

        return response()->json($event);
    }
}