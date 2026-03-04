<?php

/**
 * ชื่อไฟล์: HistoryCategoryService.php
 * คำอธิบาย: ทำหน้าที่รวบรวมและจัดการตรรกะ(Business Logic) สำหรับการดึงข้อมูลประวัติหมวดหมู่
 * ชื่อผู้เขียน: Mr.kidrakon rattanahiran
 * วันจัดที่ทำ: 2/23/2569
 */


namespace App\Service\HistoryServices;

use App\Models\Category;

class HistoryCategoryService
{
    /**
     * ดึงข้อมูลหมวดหมู่ทั้งหมด รวมถึงรายการที่ถูกลบ พร้อมข้อมูลเมตาอื่นๆ
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllHistoryCategories()
    {
        return Category::with(['creator', 'deleter'])
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'cat_name' => $category->cat_name,
                    'cat_delete_status' => $category->cat_delete_status,
                    'cat_created_at' => $category->cat_created_at,
                    'cat_deleted_at' => $category->cat_deleted_at,
                    'created_by_name' => $this->formatEmployeeName($category->creator),
                    'deleted_by_name' => $this->formatEmployeeName($category->deleter),
                ];
            })
            ->sortBy(function ($category) {
                return $category['cat_deleted_at'] === null ? 0 : 1;
            })
            ->sortByDesc('cat_deleted_at')
            ->sortByDesc('cat_created_at')
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
