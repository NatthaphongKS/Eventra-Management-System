<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    // GET /api/categories
    public function index()
    {
        $rows = DB::table('ems_categories')
            ->select('id', 'cat_name', 'cat_delete_status', 'created_at', 'updated_at')
            ->where('cat_delete_status', 'active')
            ->orderBy('cat_name')
            ->get();

        return response()->json($rows);
    }

    // POST /api/categories
    public function store(Request $request)
    {
        $data = $request->validate([
            'cat_name' => [
                'required', 'string', 'max:255',
                Rule::unique('ems_categories', 'cat_name')->where(function ($q) {
                    return $q->where('cat_delete_status', 'active');
                })
            ],
        ]);

        $id = DB::table('ems_categories')->insertGetId([
            'cat_name'          => $data['cat_name'],
            'cat_delete_status' => 'active',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        $row = DB::table('ems_categories')->where('id', $id)->first();
        return response()->json($row, 201);
    }

    // DELETE /api/categories/{id}
    public function destroy($id)
    {
        $updated = DB::table('ems_categories')
            ->where('id', $id)
            ->where('cat_delete_status', 'active')
            ->update([
                'cat_delete_status' => 'deleted',
                'updated_at' => now(),
            ]);

        if (!$updated) {
            return response()->json(['message' => 'ไม่พบข้อมูลหรือถูกลบไปแล้ว'], 404);
        }

        return response()->json(['message' => 'deleted']);
    }
}
