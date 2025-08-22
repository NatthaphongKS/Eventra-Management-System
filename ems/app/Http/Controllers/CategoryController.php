<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        // ดึงเฉพาะ active ถ้าต้องการ
        return Category::where('cat_delete_status', 'active')
                       ->orderBy('cat_name')
                       ->get();
    }

    public function store(Request $request)
    {
        // ✅ validate key cat_name ให้ตรง migration
        $validated = $request->validate([
            'cat_name' => [
                'required','string','max:255',
                Rule::unique('ems_categories','cat_name')
                    ->where('cat_delete_status','active'), // กันซ้ำเฉพาะ active
            ],
        ]);

        $category = Category::create([
            'cat_name' => $validated['cat_name'],
            'cat_delete_status' => 'active',
        ]);

        // ส่งกลับ 201 + ข้อมูลที่สร้าง
        return response()->json($category, 201);
    }

    public function destroy($id)
    {
        // soft delete -> เปลี่ยนสถานะเป็น inactive
        $category = Category::findOrFail($id);
        $category->update(['cat_delete_status' => 'inactive']);

        return response()->json(['message' => 'ok']);
    }
}