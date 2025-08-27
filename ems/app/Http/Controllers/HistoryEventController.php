<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Category;

class HistoryEventController extends Controller
{
     public function eventInfo()
    {
        $employees = Employee::join('ems_position', 'ems_employees.emp_position_id', '=', 'ems_position.id')
            ->join('ems_department', 'ems_employees.emp_department_id', '=', 'ems_department.id')
            ->join('ems_team', 'ems_employees.emp_team_id', '=', 'ems_team.id')
            ->select(
                'ems_employees.*',
                'ems_position.pst_name as position_name',
                'ems_department.dpm_name as department_name',
                'ems_team.tm_name as team_name'
            )
            ->where('ems_employees.emp_delete_status', 'inactive')
            ->get();

        $categories  = Category::select('id','cat_name')->where('cat_delete_status','active')->orderBy('cat_name')->get();
        $positions   = Position::select('id','pst_name')->where('pst_delete_status','active')->orderBy('pst_name')->get();
        $departments = Department::select('id','dpm_name')->where('dpm_delete_status','active')->orderBy('dpm_name')->get();
        $teams       = Team::select('id','tm_name')->where('tm_delete_status','active')->orderBy('tm_name')->get();

        return response()->json(compact('categories','employees','positions','departments','teams'));
    }
}
