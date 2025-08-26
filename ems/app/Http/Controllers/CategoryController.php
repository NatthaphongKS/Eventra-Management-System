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
    $validated = $request->validate([
        'cat_name' => ['required','string','max:255'],
    ]);

    $name = $validated['cat_name'];

    // ถ้ามี inactive ชื่อเดียวกัน → Reactivate และอัปเดตเวลาใหม่
    $inactive = Category::where('cat_name', $name)
        ->where('cat_delete_status', 'inactive')
        ->first();

    if ($inactive) {
        $inactive->update([
            'cat_delete_status' => 'active',
            'cat_create_at'     => now(),   // ← อัปเดตทุกครั้งที่ Reactivate
        ]);
        return response()->json($inactive, 200);
    }

    // กันซ้ำใน active
    $existsActive = Category::where('cat_name', $name)
        ->where('cat_delete_status', 'active')
        ->exists();

    if ($existsActive) {
        return response()->json([
            'message' => 'ชื่อหมวดหมู่ซ้ำ (active อยู่แล้ว)',
        ], 422);
    }

    // สร้างใหม่ + เวลา
    $category = Category::create([
        'cat_name'          => $name,
        'cat_delete_status' => 'active',
        'cat_create_at'     => now(),     // ← เก็บเวลาตอนสร้าง
    ]);

    return response()->json($category, 201);
}

public function destroy($id)
{
    $category = Category::findOrFail($id);

    $countInactive = Category::where('cat_name', 'LIKE', $category->cat_name.'%')
        ->where('cat_delete_status', 'inactive')
        ->count();

    $payload = ['cat_delete_status' => 'inactive'];

    if ($countInactive > 0) {
        $suffix = str_pad($countInactive, 2, '0', STR_PAD_LEFT);
        $payload['cat_name'] = $category->cat_name . '_' . $suffix;
    }

    $category->update($payload);

    return response()->json(['message' => 'ok']);
}
}
