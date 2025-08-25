<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class HistoryEmployeeController extends Controller
{
    public function index()
    {
        $status = 'inactive'; 
        
        return DB::table('ems_employees')
            ->join('ems_position', 'ems_employees.emp_position_id', '=', 'ems_position.id')
            ->join('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
            ->join('ems_team', 'ems_employees.emp_team_id', '=', 'ems_team.id')
            ->select(
                'ems_employees.*',
                'ems_position.pst_name as position_name',
                'ems_department.dpm_name as department_name',
                'ems_team.tm_name as team_name'
            )
            ->where('ems_employees.emp_delete_status', $status)
            ->get();
    }
}
