<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Models\Position;


class EmployeeController extends Controller
{
    /**
     * แสดงรายการพนักงาน (หน้า Table)
     * - ใช้ Eloquent + with() เพื่อ eager load ความสัมพันธ์ (position/department/team)
     * - กรองเฉพาะพนักงานที่ยังไม่ถูกลบ: emp_delete_status = 'active' หรือค่าว่าง/NULL
     * - คืนข้อมูลเป็น array แบน โดย map ชื่อทีม/ตำแหน่ง/แผนกเป็น *_name
     */
    public function index()
    {
        $employees = Employee::with([
                'position:id,pst_name',
                'department:id,dpm_name',
                'team:id,tm_name',
            ])
            ->where(fn ($q) => $q
                ->where('emp_delete_status', 'active')
                ->orWhereNull('emp_delete_status')
                ->orWhere('emp_delete_status', '')
            )
            ->orderByDesc('id')
            ->get([
                'id',
                'emp_id','emp_prefix','emp_firstname','emp_lastname',
                'emp_nickname','emp_email','emp_phone',
                'emp_position_id','emp_department_id','emp_team_id',
                'emp_permission','emp_delete_status',
                'emp_create_at', // ถ้ามีคอลัมน์นี้จริง
            ]);

        // แปลงเป็นโครงสร้างเดิม (มี *_name แทนที่จะเป็น nested relation)
        $rows = $employees->map(function (Employee $e) {
            return [
                'id'                 => $e->id,
                'emp_id'             => $e->emp_id,
                'emp_prefix'         => $e->emp_prefix,
                'emp_firstname'      => $e->emp_firstname,
                'emp_lastname'       => $e->emp_lastname,
                'emp_nickname'       => $e->emp_nickname,
                'emp_email'          => $e->emp_email,
                'emp_phone'          => $e->emp_phone,
                'emp_position_id'    => $e->emp_position_id,
                'emp_department_id'  => $e->emp_department_id,
                'emp_team_id'        => $e->emp_team_id,
                'emp_permission'     => $e->emp_permission,
                'emp_delete_status'  => $e->emp_delete_status,
                'emp_create_at'      => $e->emp_create_at,
                'position_name'      => optional($e->position)->pst_name,
                'department_name'    => optional($e->department)->dpm_name,
                'team_name'          => optional($e->team)->tm_name,
            ];
        });

        return response()->json($rows);
    }

    /**
     * อ่านข้อมูลพนักงานรายคน (ใช้สำหรับหน้าแก้ไข)
     * - with() โหลดความสัมพันธ์เพื่อเอาชื่อมาแสดง
     * - คืนรูปแบบแบนเหมือน index
     */
    public function show($id)
    {
        $e = Employee::with(['position:id,pst_name','department:id,dpm_name','team:id,tm_name'])
            ->findOrFail($id, [
                'id',
                'emp_id','emp_prefix','emp_firstname','emp_lastname',
                'emp_nickname','emp_email','emp_phone',
                'emp_position_id','emp_department_id','emp_team_id',
                'emp_permission','emp_delete_status',
            ]);

        $row = [
            'id'                 => $e->id,
            'emp_id'             => $e->emp_id,
            'emp_prefix'         => $e->emp_prefix,
            'emp_firstname'      => $e->emp_firstname,
            'emp_lastname'       => $e->emp_lastname,
            'emp_nickname'       => $e->emp_nickname,
            'emp_email'          => $e->emp_email,
            'emp_phone'          => $e->emp_phone,
            'emp_position_id'    => $e->emp_position_id,
            'emp_department_id'  => $e->emp_department_id,
            'emp_team_id'        => $e->emp_team_id,
            'emp_permission'     => $e->emp_permission,
            'emp_delete_status'  => $e->emp_delete_status,
            'position_name'      => optional($e->position)->pst_name,
            'department_name'    => optional($e->department)->dpm_name,
            'team_name'          => optional($e->team)->tm_name,
        ];

        return response()->json($row);
    }

    /**
     * ดึงเมทาดาต้าสำหรับ dropdown (ตำแหน่ง/แผนก/ทีม)
     * - ใช้โมเดลโดยตรง + where active
     */
    public function meta()
    {
        $positions = Position::query()
            ->select('id','pst_name')
            ->where('pst_delete_status','active')
            ->orderBy('pst_name')
            ->get();

        $departments = Department::query()
            ->select('id','dpm_name')
            ->where('dpm_delete_status','active')
            ->orderBy('dpm_name')
            ->get();

        $teams = Team::query()
            ->select('id','tm_name')
            ->where('tm_delete_status','active')
            ->orderBy('tm_name')
            ->get();

        return response()->json(compact('positions','departments','teams'));
    }

    /**
     * บันทึกพนักงานใหม่
     * - validate ฟิลด์ที่จำเป็น
     * - hash รหัสผ่านก่อนเก็บ
     * - map emp_status -> emp_permission
     */
    public function store(Request $request)
    {
        $request->validate([
            'emp_id'            => ['required'],
            'emp_prefix'        => ['required'],
            'emp_firstname'     => ['required'],
            'emp_lastname'      => ['required'],
            'emp_email'         => ['required','email','unique:ems_employees,emp_email'],
            'emp_phone'         => ['required','regex:/^[0-9]+$/','min:10','max:10','unique:ems_employees,emp_phone'],
            'emp_position_id'   => ['required','exists:ems_position,id'],
            'emp_department_id' => ['required','exists:ems_department,id'],
            'emp_team_id'       => ['required','exists:ems_team,id'],
            'emp_password'      => ['required','min:6'],
            'emp_status'        => ['required'], // จะ map เป็น emp_permission
        ]);

        $employee = Employee::create([
            'emp_id'            => $request->emp_id,
            'emp_prefix'        => $request->emp_prefix,
            'emp_firstname'     => $request->emp_firstname,
            'emp_lastname'      => $request->emp_lastname,
            'emp_email'         => $request->emp_email,
            'emp_phone'         => $request->emp_phone,
            'emp_position_id'   => $request->emp_position_id,
            'emp_department_id' => $request->emp_department_id,
            'emp_team_id'       => $request->emp_team_id,
            'emp_password'      => Hash::make($request->emp_password),
            'emp_permission'    => $request->emp_status,
            'emp_delete_status' => 'active',
            // ถ้ามีคอลัมน์ผู้สร้าง:
            'emp_create_by'   => auth()->id(),
            'emp_create_at'   => now(),
        ]);

            return response()->json(['message' => 'Employee created', 'data' => $employee], 201);
    }




    /**
     * อัปเดตข้อมูลพนักงาน
     * - validate แบบบางฟิลด์ (sometimes)
     * - บังคับ unique ของ email/phone โดย ignore แถวเดิม
     * - แปลง emp_status -> emp_permission ถ้าส่งมา
     * - hash รหัสผ่านเมื่อส่งมาใหม่
     */
    public function update(Request $request, $id)
    {
        $emp = Employee::findOrFail($id);

        $validated = $request->validate([
            'emp_id'            => ['sometimes','required'],
            'emp_prefix'        => ['sometimes','required'],
            'emp_firstname'     => ['sometimes','required'],
            'emp_lastname'      => ['sometimes','required'],
            'emp_nickname'      => ['sometimes','nullable','string','max:100'],
            'emp_email'         => ['sometimes','nullable','email',
                Rule::unique('ems_employees','emp_email')->ignore($emp->id)],
            'emp_phone'         => ['sometimes','nullable','regex:/^[0-9]+$/','min:10','max:10',
                Rule::unique('ems_employees','emp_phone')->ignore($emp->id)],
            'emp_position_id'   => ['sometimes','nullable','exists:ems_position,id'],
            'emp_department_id' => ['sometimes','nullable','exists:ems_department,id'],
            'emp_team_id'       => ['sometimes','nullable','exists:ems_team,id'],
            'emp_permission'    => ['sometimes','nullable','string','max:50'],
            'emp_status'        => ['sometimes','nullable','string','max:50'], // alias
            'emp_password'      => ['sometimes','nullable','min:6'],
        ]);

        $update = collect($validated)->except('emp_status')->toArray();

        if ($request->filled('emp_status')) {
            $update['emp_permission'] = $request->emp_status;
        }

        if (!empty($update['emp_password'] ?? null)) {
            $update['emp_password'] = Hash::make($update['emp_password']);
        } else {
            unset($update['emp_password']);
        }

        $emp->fill($update)->save();

        return response()->json([
            'message' => 'updated',
            'data' => $emp->fresh(),
        ]);
    }

    /**
     * ลบแบบ soft (ปรับสถานะเป็น inactive)
     */
    public function destroy($id)
    {
        $emp = Employee::findOrFail($id);
        $emp->emp_delete_status = 'inactive';
        $emp->save();

        return response()->json(['message' => 'Deleted successfully']);
    }

}