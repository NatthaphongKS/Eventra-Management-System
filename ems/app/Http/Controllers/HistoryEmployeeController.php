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
            ->select(
                'e.*',
                'p.pst_name as position_name',
                'd.dpm_name as department_name',
                't.tm_name as team_name'
            )
            ->where('e.emp_delete_status', 'inactive')
            ->orderBy('e.emp_id')
            ->get();

        return response()->json($rows);
    }
}
