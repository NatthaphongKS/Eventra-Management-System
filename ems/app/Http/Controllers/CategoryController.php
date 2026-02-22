<?php

/**
 * ชื่อไฟล์: CategoryController.php
 * คำอธิบาย: Controller สำหรับจัดการข้อมูล Category ทั้งหมด
 * เป็นตัวกลางในการรับ Request, Validate และส่งต่อให้ CategoryService
 * ผู้เขียน/แก้ไข: katcharuek sriphirom
 * วันที่แก้ไขล่าสุด: 22 กุมภาพันธ์ 2026
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\Service\CategoryServices\CategoryService;

class CategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /* ============================================================
       1) index (รายการ Category สถานะ Active)
    ============================================================ */
    public function index()
    {
        // ส่ง true เพื่อเอาเฉพาะ Active
        return response()->json(['data' => $this->categoryService->getCategories(true)], 200);
    }

    /* ============================================================
       2) details (รายการ Category ทั้งหมด)
    ============================================================ */
    public function details()
    {
        // ส่ง false เพื่อเอาทั้งหมด
        return response()->json(['data' => $this->categoryService->getCategories(false)], 200);
    }

    /* ============================================================
       3) store (เพิ่มหมวดหมู่ใหม่)
    ============================================================ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cat_name' => [
                'required', 'string', 'max:255',
                Rule::unique('ems_categories', 'cat_name')
                    ->where(fn ($q) => $q->where('cat_delete_status', 'active')),
            ],
        ]);

        $result = $this->categoryService->storeCategory(
            trim($validated['cat_name']),
            Auth::id()
        );

        return response()->json($result['data'], $result['status']);
    }

    /* ============================================================
       4) update (แก้ไขชื่อหมวดหมู่)
    ============================================================ */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'cat_name' => [
                'required', 'string', 'max:255',
                Rule::unique('ems_categories', 'cat_name')
                    ->where(fn ($q) => $q->where('cat_delete_status', 'active'))
                    ->ignore($id),
            ],
        ]);

        $data = $this->categoryService->updateCategory($id, trim($validated['cat_name']));

        return response()->json($data, 200);
    }

    /* ============================================================
       5) destroy (ลบหมวดหมู่)
    ============================================================ */
    public function destroy($id)
    {
        $this->categoryService->deleteCategory($id, Auth::id());

        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
