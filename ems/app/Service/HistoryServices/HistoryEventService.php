<?php

/**
 * ชื่อไฟล์: HistoryEventService.php
 * คำอธิบาย: Service สำหรับจัดการ business logic เกี่ยวกับกิจกรรมที่ถูกลบ
 * ชื่อผู้เขียน: Mr.Suphanut Pangot
 * วันที่จัดทำ: 2025-12-14
 */

namespace App\Service\HistoryServices;

use App\Models\Event;

class HistoryEventService
{
    /**
     * ดึงข้อมูล Event ที่ถูกลบทั้งหมด พร้อมชื่อผู้สร้างและผู้ลบ
     *
     * @return array ข้อมูล Event ที่ถูกลบพร้อมชื่อผู้สร้างและผู้ลบ
     */
    public function getAllDeletedEvents()
    {
        // ดึงข้อมูล Event ที่มีสถานะ deleted โดยใช้ with() เพื่อดึง relation
        $events = Event::with(['creator', 'deletedBy'])
            ->orderBy('evn_created_at', 'desc')
            ->get();

        // จัด Format ข้อมูล
        return $events->map(function ($event) {
            return $this->formatEventData($event);
        })->toArray();
    }

    /**
     * ดึงข้อมูล Event ที่ถูกลบตาม ID
     *
     * @param int $id Event ID
     * @return array|null ข้อมูล Event หรือ null ถ้าไม่พบ
     */
    public function getDeletedEventById($id)
    {
        $event = Event::where('id', $id)
            ->where('evn_status', 'deleted')
            ->with(['creator', 'deletedBy'])
            ->first();

        if (!$event) {
            return null;
        }

        return $this->formatEventData($event);
    }

    /**
     * จัดรูปแบบข้อมูล Event สำหรับ response
     *
     * @param Event $event
     * @return array ข้อมูล Event ที่จัดรูปแบบแล้ว
     */
    private function formatEventData(Event $event)
    {
        return [
            'id' => $event->id,
            'evn_title' => $event->evn_title,
            'evn_status' => $event->evn_status,
            'created_at' => $event->evn_created_at,
            'deleted_at' => $event->evn_deleted_at,
            'created_by_name' => $this->formatEmployeeName($event->creator),
            'deleted_by_name' => $this->formatEmployeeName($event->deletedBy),
        ];
    }

    /**
     * จัดรูปแบบชื่อพนักงาน
     *
     * @param \App\Models\Employee|null $employee
     * @return string ชื่อเต็ม หรือ '-' ถ้าไม่มีข้อมูล
     */
    private function formatEmployeeName($employee)
    {
        if (!$employee) {
            return '-';
        }

        return $employee->emp_firstname . ' ' . $employee->emp_lastname;
    }
}
