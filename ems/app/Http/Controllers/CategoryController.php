<?php
namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
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
                'ems_categories.cat_created_at as cat_created_at',
                DB::raw("TRIM(CONCAT_WS(' ', e.emp_firstname)) as created_by_name"),
                'e.emp_nickname as created_by_nickname',
            ]);

        $data = $rows->map(function ($r) {
            return [
                'id'               => $r->id,
                'cat_name'         => $r->cat_name,
                'created_by'       => $r->cat_created_by,
                'created_by_name'  => $r->created_by_name ?: $r->created_by_nickname,
                'cat_created_at'   => $r->cat_created_at,
            ];
        });

        return response()->json(['data' => $data], 200);
    }
  public function details()
{
    $rows = Category::query()
        ->leftJoin('ems_employees as e', 'e.id', '=', 'ems_categories.cat_created_by')
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
        // ✅ กันชื่อซ้ำเฉพาะรายการที่ active
        $validated = $request->validate([
            'cat_name' => [
                'required', 'string', 'max:255',
                Rule::unique('ems_categories', 'cat_name')
                    ->where(fn ($q) => $q->where('cat_delete_status', 'active')),
            ],
        ]);

        $name = trim($validated['cat_name']);

        // ถ้าเคยมีชื่อเดียวกันแต่ inactive → reactivate
        $inactive = Category::where('cat_name', $name)
            ->where('cat_delete_status', 'inactive')
            ->first();

        if ($inactive) {
            $inactive->cat_delete_status = 'active';
            $inactive->cat_deleted_at = null;
            $inactive->cat_deleted_by = null;
            // อัปเดตเวลาสร้างให้ปัจจุบันถ้าต้องการ
            $inactive->cat_created_at = $inactive->cat_created_at ?? now();
            $inactive->save();

            return response()->json([
                'id'              => $inactive->id,
                'cat_name'        => $inactive->cat_name,
                'created_by_name' => null,
                'cat_created_at'  => $inactive->cat_created_at,
            ], 200);
        }

        // สร้างใหม่
        $category = Category::create([
            'cat_name'          => $name,
            'cat_delete_status' => 'active',
            'cat_created_by'    => Auth::id(),
            'cat_created_at'    => now(),   // ✅ ใช้ชื่อฟิลด์สอดคล้องกัน
        ]);

        return response()->json([
            'id'              => $category->id,
            'cat_name'        => $category->cat_name,
            'created_by_name' => null,
            'cat_created_at'  => $category->cat_created_at,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        // ✅ กันชื่อซ้ำ (ignore แถวตัวเอง) & trim
        $validated = $request->validate([
            'cat_name' => [
                'required', 'string', 'max:255',
                Rule::unique('ems_categories', 'cat_name')
                    ->where(fn ($q) => $q->where('cat_delete_status', 'active'))
                    ->ignore($id),
            ],
        ]);

        $category = Category::findOrFail($id);
        $category->cat_name = trim($validated['cat_name']);
        $category->save();

        return response()->json([
            'id'              => $category->id,
            'cat_name'        => $category->cat_name,
            'created_by_name' => null,
            'cat_created_at'  => $category->cat_created_at,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->cat_delete_status = 'inactive';
        $category->cat_deleted_at = now();
        $category->cat_deleted_by = Auth::id();
        $category->save();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
