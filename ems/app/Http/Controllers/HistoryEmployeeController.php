<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class HistoryEmployeeController extends Controller
{
    public function index()
    {
        $rows = DB::table('ems_employees as e')
            ->join('ems_position as p', 'e.emp_position_id', '=', 'p.id')
            ->join('ems_department as d', 'e.emp_department_id', '=', 'd.id')
            ->join('ems_team as t', 'e.emp_team_id', '=', 't.id')
            ->leftJoin('ems_employees as cb', 'e.emp_created_by', '=', 'cb.id')
            ->leftJoin('ems_employees as db', 'e.emp_delete_by', '=', 'db.id')
            ->select(
                'e.id',
                'e.emp_id',
                'e.emp_prefix',
                'e.emp_firstname',
                'e.emp_lastname',
                'e.emp_nickname',
                'e.emp_delete_status',
                'e.created_at',
                'e.emp_delete_at',
                'p.pst_name as position_name',
                'd.dpm_name as department_name',
                't.tm_name as team_name',
                DB::raw("COALESCE(NULLIF(TRIM(cb.emp_firstname),''), cb.emp_nickname, '-') as created_by_name"),
                DB::raw("COALESCE(NULLIF(TRIM(db.emp_firstname),''), db.emp_nickname, '-') as deleted_by_name")
            )
            ->where('e.emp_delete_status', 'inactive')
            ->orderByRaw("e.emp_delete_at IS NULL ASC")
            ->orderByDesc('e.emp_delete_at')
            ->orderBy('e.emp_id')
            ->get();

        return response()->json($rows);
    }
}
