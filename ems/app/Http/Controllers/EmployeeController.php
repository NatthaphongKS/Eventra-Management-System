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
    public function index() {
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
                'ems_position.ept_name as position_name',
                'ems_department.edm_name as department_name',
                'ems_team.etm_name as team_name'
            )
            ->where('ems_employees.emp_delete_status', 'active')
            ->get();
    }

    public function destroy($id) {
        $emp = Employee::findOrFail($id);
        $emp->emp_delete_status = 'inactive'; // Set the delete status to inactive
        $emp->save();
        return response()->json(['message' => 'Deleted successfully']);
    }
    // ดึงข้อมูลตำแหน่ง, แผนก, ทีม
    public function meta()
    {
        $positions = DB::table('ems_position')
            ->select('id', 'ept_name')
            ->where('ept_delete_status', 'active')
            ->orderBy('ept_name')
            ->get();
        $departments = DB::table('ems_department')
            ->select('id', 'edm_name')
            ->where('edm_delete_status', 'active')
            ->orderBy('edm_name')
            ->get();
        $teams = DB::table('ems_team')
            ->select('id', 'etm_name')
            ->where('etm_delete_status', 'active')
            ->orderBy('etm_name')
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
            'emp_id' =>'required',
            'emp_prefix' => 'required',
            'emp_firstname' => 'required',
            'emp_lastname' => 'required',
            'email' => 'required|email|unique:ems_employees,email',
            'phone' => 'required|unique:ems_employees,phone',
            'emp_position_id' => 'required|exists:ems_position,id',
            'emp_department_id' => 'required|exists:ems_department,id',
            'emp_team_id' => 'required|exists:ems_team,id',
            'emp_password' => 'required|min:6',
            'emp_status' => 'required',
        ]);

        $employee = Employee::create([
            'emp_id'=>$request->emp_id,
            'emp_prefix' => $request->emp_prefix,
            'emp_firstname' => $request->emp_firstname,
            'emp_lastname' => $request->emp_lastname,
            'email' => $request->email,
            'phone' => $request->phone,
            'emp_position_id' => $request->emp_position_id,
            'emp_department_id' => $request->emp_department_id,
            'emp_team_id' => $request->emp_team_id,
            'emp_password' => Hash::make($request->emp_password),
            'emp_status' => $request->emp_status,
        ]);

        return response()->json(['message' => 'Employee created', 'data' => $employee]);
    }
    public function saveDepartment(Request $request)
    {
        $request->validate([
            'edm_name' => 'required|unique:ems_department,edm_name',
        ]);

        $department = Department::create([
            'edm_name' => $request->edm_name,
            'edm_delete_status' => 'active',
        ]);

        return response()->json(['message' => 'Department created', 'data' => $department]);
    }
    public function savePosition(Request $request)
    {
        $request->validate([
            'ept_name' => 'required|unique:ems_position,ept_name',
        ]);

        $position = Position::create([
            'ept_name' => $request->ept_name,
            'ept_delete_status' => 'active',
        ]);

        return response()->json(['message' => 'Position created', 'data' => $position]);
    }
    public function saveTeam(Request $request)
    {
        $request->validate([
            'etm_name' => 'required|unique:ems_team,etm_name',
        ]);

        $team = Team::create([
            'etm_name' => $request->etm_name,
            'etm_delete_status' => 'active',
        ]);

        return response()->json(['message' => 'Team created', 'data' => $team]);
    }
}
