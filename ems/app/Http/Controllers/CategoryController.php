<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
  public function index()
{
    $rows = Category::query()
        ->leftJoin('ems_employees as e', 'e.id', '=', 'ems_categories.cat_created_by')
        ->where('ems_categories.cat_delete_status', 'active')
        ->orderBy('ems_categories.cat_name', 'asc')
        ->orderBy('ems_categories.cat_created_at', 'desc')
        ->get([
            'ems_categories.id',
            'ems_categories.cat_name',
            'ems_categories.cat_delete_status',
            'ems_categories.cat_created_by',
            'ems_categories.cat_created_at as cat_create_at',
            DB::raw("TRIM(CONCAT_WS(' ', e.emp_firstname)) as created_by_name"),
            'e.emp_nickname as created_by_nickname',
        ]);

    // ส่งชื่อที่อ่านง่ายให้ FE (fallback เป็นชื่อเล่นถ้าชื่อเต็มว่าง)
    $data = $rows->map(function ($r) {
        return [
            'id'              => $r->id,
            'cat_name'        => $r->cat_name,
            'created_by'      => $r->cat_created_by,                   // id เดิม
            'created_by_name' => $r->created_by_name ?: $r->created_by_nickname,
            'cat_created_at'   => $r->cat_create_at,                    // FE จะ format เอง
        ];
    });

    return response()->json(['data' => $data], 200);
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
