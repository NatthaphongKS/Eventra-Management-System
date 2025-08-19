<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Position;
use App\Models\Department;
use App\Models\Team;
use App\Models\Employee; // Assuming you have an Employee model
class EmployeeController extends Controller
{
    public function index()
    {
        // $position = Position::where('ept_delete_status', 'active')->get();
        // $department = Department::where('edm_delete_status', 'active')->get();
        // $team = Team::where('etm_delete_status', 'active')->get();
        // $employees = Employee::where('emp_delete_status', 'active')
        //     ->get();
        // return response()->json([
        //     'employees' => $employees,
        //     'positions' => $position,
        //     'departments' => $department,
        //     'teams' => $team,
        // ]);
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
            ->where('ems_employees.emp_delete_status', 'active')
            ->get();
    }

    public function destroy($id)
    {
        $emp = Employee::findOrFail($id);
        $emp->emp_delete_status = 'inactive'; // Set the delete status to inactive
        $emp->save();
        return response()->json(['message' => 'Deleted successfully']);
    }
    // ดึงข้อมูลตำแหน่ง, แผนก, ทีม
    public function meta()
    {
        $positions = DB::table('ems_position')
            ->select('id', 'pst_name')
            ->where('pst_delete_status', 'active')
            ->orderBy('pst_name')
            ->get();
        $departments = DB::table('ems_department')
            ->select('id', 'dpm_name')
            ->where('dpm_delete_status', 'active')
            ->orderBy('dpm_name')
            ->get();
        $teams = DB::table('ems_team')
            ->select('id', 'tm_name')
            ->where('tm_delete_status', 'active')
            ->orderBy('tm_name')
            ->get();
        return response()->json([
            'positions' => $positions,
            'departments' => $departments,
            'teams' => $teams,
        ]);
    }



    // รับข้อมูลและบันทึกลง DB
    public function store(Request $request)
    {
        $request->validate([
            'emp_id' => 'required',
            'emp_prefix' => 'required',
            'emp_firstname' => 'required',
            'emp_lastname' => 'required',
            'emp_email' => 'required|email|unique:ems_employees,emp_email',
            'emp_phone' => [
                'required',
                'unique:ems_employees,emp_phone',
                'regex:/^[0-9]+$/', // รับเฉพาะตัวเลข
                'min:10',
                'max:10'
            ],
            'emp_position_id' => 'required|exists:ems_position,id',
            'emp_department_id' => 'required|exists:ems_department,id',
            'emp_team_id' => 'required|exists:ems_team,id',
            'emp_password' => 'required|min:6',
            'emp_status' => 'required',
        ]);

        $employee = Employee::create([
            'emp_id' => $request->emp_id,
            'emp_prefix' => $request->emp_prefix,
            'emp_firstname' => $request->emp_firstname,
            'emp_lastname' => $request->emp_lastname,
            'emp_email' => $request->emp_email,
            'emp_phone' => $request->emp_phone,
            'emp_position_id' => $request->emp_position_id,
            'emp_department_id' => $request->emp_department_id,
            'emp_team_id' => $request->emp_team_id,
            'emp_password' => Hash::make($request->emp_password),
            'emp_permission' => $request->emp_status,
        ]);

        return response()->json(['message' => 'Employee created', 'data' => $employee]);
    }
}
