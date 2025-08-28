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
        $inactive-> cat_delete_status = 'active';
        $inactive-> cat_deleted_at = null;
        $inactive-> cat_deleted_by = null;
        $inactive->save();
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
        'cat_created_by'   => auth()->id(),
        'cat_create_at'     => now(),     // ← เก็บเวลาตอนสร้าง
    ]);

    return response()->json($category, 201);
}

public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category-> cat_delete_status = 'inactive';
        $category-> cat_deleted_at = now();
        $category-> cat_deleted_by = auth()->id();
        $category->save();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
