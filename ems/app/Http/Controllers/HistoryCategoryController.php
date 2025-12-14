<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HistoryCategoryController extends Controller
{
    public function index()
    {
        $rows = DB::table('ems_categories as c')
            ->leftJoin('ems_employees as cb', 'c.cat_created_by', '=', 'cb.id')
            ->leftJoin('ems_employees as db', 'c.cat_deleted_by', '=', 'db.id')
            ->select(
                'c.id',
                'c.cat_name',
                'c.cat_delete_status',
                'c.cat_created_at',
                'c.cat_deleted_at',
                DB::raw("COALESCE(NULLIF(TRIM(cb.emp_firstname),''), cb.emp_nickname, '-') as created_by_name"),
                DB::raw("COALESCE(NULLIF(TRIM(db.emp_firstname),''), db.emp_nickname, '-') as deleted_by_name")
            )
            ->orderByRaw("c.cat_deleted_at IS NULL ASC")
            ->orderByDesc('c.cat_deleted_at')
            ->orderByDesc('c.cat_created_at')
            ->get();

        return response()->json($rows);
    }
}
