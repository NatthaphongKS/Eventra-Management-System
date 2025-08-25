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
            'cat_time_create' => now(),
        ]);
        // ส่งกลับ 201 + ข้อมูลที่สร้าง
        return response()->json($category, 201);
    }

public function destroy($id)
{
    $category = Category::findOrFail($id);

    // หาจำนวน inactive ที่ชื่อเริ่มต้นด้วย "<ชื่อ>_"
    $countInactive = Category::where('cat_name', 'LIKE', $category->cat_name.'%')
        ->where('cat_delete_status', 'inactive')
        ->count();

    $payload = ['cat_delete_status' => 'inactive'];

    if ($countInactive > 0) {
        // ต่อ suffix เป็น _01, _02, ... ตามจำนวนที่มีอยู่แล้ว
        $suffix = str_pad($countInactive, 2, '0', STR_PAD_LEFT);
        $payload['cat_name'] = $category->cat_name . '_' . $suffix;
    }

    $category->update($payload);

    return response()->json(['message' => 'ok']);
}
}
