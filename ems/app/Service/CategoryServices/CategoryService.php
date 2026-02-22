<?php

/**
 * ชื่อไฟล์: CategoryService.php
 * คำอธิบาย: ไฟล์นี้เก็บ (Business Logic) สำหรับจัดการข้อมูล Category (หมวดหมู่)
 * ชื่อผู้เขียน/แก้ไข: katcharuek sriphirom
 * วันที่จัดทำ/แก้ไข: 22 กุมภาพันธ์ 2026
 */

namespace App\Service\CategoryServices;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    /* ============================================================
       1) getCategories (ดึงข้อมูลรายการหมวดหมู่)
    ============================================================ */
    public function getCategories(bool $onlyActive = true)
    {
        $query = Category::query()
            ->leftJoin('ems_employees as e', 'e.id', '=', 'ems_categories.cat_created_by')
            ->orderBy('ems_categories.cat_name', 'asc')
            ->orderBy('ems_categories.cat_created_at', 'desc');

        if ($onlyActive) {
            $query->where('ems_categories.cat_delete_status', 'active');
        }

        $rows = $query->get([
            'ems_categories.id',
            'ems_categories.cat_name',
            'ems_categories.cat_delete_status',
            'ems_categories.cat_created_by',
            'ems_categories.cat_created_at as cat_created_at',
            DB::raw("TRIM(e.emp_firstname) as created_by_name"),
        ]);

        return $rows->map(function ($r) {
            return [
                'id'              => $r->id,
                'cat_name'        => $r->cat_name,
                'created_by'      => $r->cat_created_by,
                // ✅ ใช้ firstname เท่านั้น
                'created_by_name' => $r->created_by_name ?: '-',
                'cat_created_at'  => $r->cat_created_at,
            ];
        });
    }

    /* ============================================================
       2) storeCategory (สร้างหมวดหมู่ใหม่ หรือเปิดใช้งานอันเก่า)
    ============================================================ */
    public function storeCategory(string $name, $userId)
    {
        // ถ้าเคยมีชื่อเดียวกันแต่ inactive → reactivate
        $inactive = Category::where('cat_name', $name)
            ->where('cat_delete_status', 'inactive')
            ->first();

        if ($inactive) {
            $inactive->cat_delete_status = 'active';
            $inactive->cat_deleted_at = null;
            $inactive->cat_deleted_by = null;
            $inactive->cat_created_at = $inactive->cat_created_at ?? now();
            $inactive->save();

            return [
                'data' => $this->formatSingleCategory($inactive),
                'status' => 200
            ];
        }

        // สร้างใหม่
        $category = Category::create([
            'cat_name'          => $name,
            'cat_delete_status' => 'active',
            'cat_created_by'    => $userId,
            'cat_created_at'    => now(),
        ]);

        return [
            'data' => $this->formatSingleCategory($category),
            'status' => 201
        ];
    }

    /* ============================================================
       3) updateCategory (อัปเดตชื่อหมวดหมู่)
    ============================================================ */
    public function updateCategory($id, string $name)
    {
        $category = Category::findOrFail($id);
        $category->cat_name = $name;
        $category->save();

        return $this->formatSingleCategory($category);
    }

    /* ============================================================
       4) deleteCategory (Soft Delete หมวดหมู่)
    ============================================================ */
    public function deleteCategory($id, $userId)
    {
        $category = Category::findOrFail($id);
        $category->cat_delete_status = 'inactive';
        $category->cat_deleted_at = now();
        $category->cat_deleted_by = $userId;
        $category->save();

        return true;
    }

    /* ============================================================
       5) formatSingleCategory (ตัวช่วยดึงชื่อคนสร้างและจัด Format)
    ============================================================ */
    private function formatSingleCategory($category)
    {
        $createdByName = DB::table('ems_employees')
            ->where('id', $category->cat_created_by)
            ->value('emp_firstname');

        return [
            'id'              => $category->id,
            'cat_name'        => $category->cat_name,
            'created_by_name' => $createdByName ? trim($createdByName) : '-',
            'cat_created_at'  => $category->cat_created_at,
        ];
    }
}
