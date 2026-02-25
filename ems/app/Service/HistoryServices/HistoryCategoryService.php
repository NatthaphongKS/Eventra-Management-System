<?php

/**
 * ชื่อไฟล์: HistoryCategoryService.php
 * คำอธิบาย: ทำหน้าที่รวบรวมและจัดการตรรกะ(Business Logic) สำหรับการดึงข้อมูลประวัติหมวดหมู่
 * ชื่อผู้เขียน: Mr.kidrakon rattanahiran
 * วันจัดที่ทำ: 2/23/2569
 */


namespace App\Service\HistoryServices;

use Illuminate\Support\Facades\DB;

class HistoryCategoryService
{
    /**
     * ดึงข้อมูลหมวดหมู่ทั้งหมด รวมถึงรายการที่ถูกลบ พร้อมข้อมูลเมตาอื่นๆ
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllHistoryCategories()
    {
        return DB::table('ems_categories as c')
            ->leftJoin('ems_employees as cb', 'c.cat_created_by', '=', 'cb.id')
            ->leftJoin('ems_employees as db', 'c.cat_deleted_by', '=', 'db.id')
            ->select(
                'c.id',
                'c.cat_name',
                'c.cat_delete_status',
                'c.cat_created_at',
                'c.cat_deleted_at',
                DB::raw("COALESCE(NULLIF(TRIM(cb.emp_firstname),'') , cb.emp_nickname, '-') as created_by_name"),
                DB::raw("COALESCE(NULLIF(TRIM(db.emp_firstname),'') , db.emp_nickname, '-') as deleted_by_name")
            )
            ->orderByRaw('c.cat_deleted_at IS NULL ASC')
            ->orderByDesc('c.cat_deleted_at')
            ->orderByDesc('c.cat_created_at')
            ->get();
    }
}
