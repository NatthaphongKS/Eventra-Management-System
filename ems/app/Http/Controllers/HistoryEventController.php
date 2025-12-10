<?php

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
}
