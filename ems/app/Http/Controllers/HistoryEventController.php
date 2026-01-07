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
            // ใช้ with() เพื่อดึงข้อมูลจาก Relation ที่ประกาศไว้ใน Model
            // ไม่ต้อง leftJoin เองแล้ว Laravel จะจัดการให้
            $events = Event::with(['creator', 'deletedBy']) 
                ->orderBy('evn_created_at', 'desc') // เรียงตามวันที่สร้างล่าสุด
                ->get();

            // จัด Format ข้อมูล
            $data = $events->map(function ($ev) {
                
                // ฟังก์ชันย่อยสำหรับจัดรูปแบบชื่อ (จะได้ไม่ต้องเขียนซ้ำ)
                $formatName = function ($employee) {
                    if (!$employee) return '-';
                    // ตาม Logic เดิม: ชื่อจริง + นามสกุล
                    return $employee->emp_firstname . ' ' . $employee->emp_lastname;
                };

                return [
                    'id' => $ev->id,
                    'evn_title'  => $ev->evn_title,
                    'evn_status' => $ev->evn_status, // ส่งสถานะไปด้วย
                    
                    // เนื่องจากใน Model มี casts เป็น datetime แล้ว สามารถ format วันที่ได้เลยถ้าต้องการ
                    // เช่น $ev->evn_created_at->format('Y-m-d H:i') 
                    'created_at' => $ev->evn_created_at, 
                    'deleted_at' => $ev->evn_deleted_at,

                    // เข้าถึงผ่าน Relation object
                    'created_by_name' => $formatName($ev->creator),
                    'deleted_by_name' => $formatName($ev->deletedBy),
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