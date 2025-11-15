<?php

// app/Http/Controllers/FilterController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class FilterController extends Controller
{
    public function categories()
    {
        // ตารางตามรูป: ems_categories (id, cat_name, cat_delete_status ...)
        $rows = DB::table('ems_categories')
            ->where('cat_delete_status', 'active')
            ->orderBy('cat_name')
            ->get(['id', 'cat_name as name']);

        return response()->json($rows);
    }

    public function statuses()
    {
        // ดึงสถานะที่มีอยู่จริงจากตารางอีเวนต์ (เช่น: upcoming, ongoing, done)
        $rows = DB::table('ems_event')
            ->whereNull('evn_deleted_at')
            ->distinct()
            ->orderBy('evn_status')
            ->pluck('evn_status');

        return response()->json($rows); // ["done","ongoing","upcoming"]
    }
}
