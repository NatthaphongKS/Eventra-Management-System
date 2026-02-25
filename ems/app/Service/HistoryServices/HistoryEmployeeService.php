<?php

/**
 * ชื่อไฟล์: HistoryEmployeeService.php
 * คำอธิบาย: ทำหน้าที่จัดการ Business Logic ที่เกี่ยวข้องกับการเรียกดูประวัติของพนักงาน
 * ชื่อผู้เขียน: Mr.kidrakon rattanahiran
 * วันจัดที่ทำ: 2/23/2569
 */

namespace App\Service\HistoryServices;

use Illuminate\Support\Facades\DB;

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
        return DB::table('ems_employees as e')
            ->join('ems_position as p', 'e.emp_position_id', '=', 'p.id')
            ->join('ems_department as d', 'e.emp_department_id', '=', 'd.id')
            ->join('ems_team as t', 'e.emp_team_id', '=', 't.id')
            ->leftJoin('ems_employees as cb', 'e.emp_create_by', '=', 'cb.id')
            ->leftJoin('ems_employees as db', 'e.emp_delete_by', '=', 'db.id')
            ->select(
                'e.id',
                'e.emp_id',
                'e.emp_prefix',
                'e.emp_firstname',
                'e.emp_lastname',
                'e.emp_nickname',
                'e.emp_delete_status',
                'e.emp_created_at as created_at',
                'e.emp_deleted_at',
                'p.pst_name as position_name',
                'd.dpm_name as department_name',
                't.tm_name as team_name',
                DB::raw("COALESCE(NULLIF(TRIM(cb.emp_firstname),'') , cb.emp_nickname, '-') as created_by_name"),
                DB::raw("COALESCE(NULLIF(TRIM(db.emp_firstname),'') , db.emp_nickname, '-') as deleted_by_name")
            )
            // show all employees; don't filter by deletion status
            ->orderByRaw('e.emp_deleted_at IS NULL ASC')
            ->orderByDesc('e.emp_deleted_at')
            ->orderBy('e.emp_id')
            ->get();
    }
}
