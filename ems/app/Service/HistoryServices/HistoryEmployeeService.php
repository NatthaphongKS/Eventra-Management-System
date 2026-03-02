<?php

/**
 * ชื่อไฟล์: HistoryEmployeeService.php
 * คำอธิบาย: ทำหน้าที่จัดการ Business Logic ที่เกี่ยวข้องกับการเรียกดูประวัติของพนักงาน
 * ชื่อผู้เขียน: Mr.kidrakon rattanahiran
 * วันจัดที่ทำ: 2/23/2569
 */

namespace App\Service\HistoryServices;

use App\Models\Employee;

class HistoryEmployeeService
{
    /**
     * ดึงรายการพนักงานทั้งหมด (รวมถึงพนักงานที่ถูกลบแล้ว) พร้อมข้อมูลประกอบ
     * เช่น ตำแหน่ง แผนก ทีม และชื่อผู้สร้าง/ผู้ลบ
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllHistoryEmployees()
    {
        return Employee::with(['position', 'department', 'team', 'creator', 'deletedBy'])
            ->get()
            ->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'emp_id' => $employee->emp_id,
                    'emp_prefix' => $employee->emp_prefix,
                    'emp_firstname' => $employee->emp_firstname,
                    'emp_lastname' => $employee->emp_lastname,
                    'emp_nickname' => $employee->emp_nickname,
                    'emp_delete_status' => $employee->emp_delete_status,
                    'created_at' => $employee->emp_created_at,
                    'emp_deleted_at' => $employee->emp_deleted_at,
                    'position_name' => $employee->position?->pst_name ?? '-',
                    'department_name' => $employee->department?->dpm_name ?? '-',
                    'team_name' => $employee->team?->tm_name ?? '-',
                    'created_by_name' => $this->formatEmployeeName($employee->creator),
                    'deleted_by_name' => $this->formatEmployeeName($employee->deletedBy),
                ];
            })
            ->sortBy(function ($employee) {
                return $employee['emp_deleted_at'] === null ? 0 : 1;
            })
            ->sortByDesc('emp_deleted_at')
            ->sortBy('emp_id')
            ->values();
    }

    /**
     * จัดรูปแบบชื่อพนักงาน
     *
     * @param \App\Models\Employee|null $employee
     * @return string
     */
    private function formatEmployeeName($employee)
    {
        if (!$employee) {
            return '-';
        }

        $firstName = trim($employee->emp_firstname ?? '');
        if (!empty($firstName)) {
            return $firstName;
        }

        return $employee->emp_nickname ?? '-';
    }
}
