<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    private const STATUS_ACTIVE  = 'active';
    private const STATUS_DELETED = 'deleted';

    public function index()
    {
        return Category::active()->orderBy('id')->get();
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'cat_name' => [
                    'required','string','max:255',
                    Rule::unique('ems_categories', 'cat_name')
                        ->where(fn($q) => $q->where('cat_delete_status', self::STATUS_ACTIVE))
                ],
            ]);

            $category = Category::create([
                'cat_name'          => trim($data['cat_name']),
                'cat_delete_status' => self::STATUS_ACTIVE,
            ]);

            return response()->json($category, 201);

        } catch (\Throwable $e) {
            Log::error('Create category failed', ['msg' => $e->getMessage()]);
            return response()->json([
                'message' => 'create_failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['message' => 'not_found'], 404);
        }
        if ($category->cat_delete_status === self::STATUS_DELETED) {
            return response()->json(['message' => 'already_deleted'], 200);
        }

        $category->update(['cat_delete_status' => self::STATUS_DELETED]);
        return response()->json(['message' => 'ok']);
    }

    // (แถม) กู้คืนหมวดหมู่ที่เคยลบแบบ soft
    public function restore(int $id)
    {
        $category = Category::where('id', $id)
            ->where('cat_delete_status', self::STATUS_DELETED)
            ->first();

        if (!$category) {
            return response()->json(['message' => 'not_found_or_not_deleted'], 404);
        }

        $category->update(['cat_delete_status' => self::STATUS_ACTIVE]);
        return response()->json(['message' => 'ok']);
    }
}
