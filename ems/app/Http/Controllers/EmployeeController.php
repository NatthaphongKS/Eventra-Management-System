<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * ดึงรายการพนักงานทั้งหมดสำหรับตารางหน้า Employee
     * - leftJoin กับ position/department/team เพื่อเอาชื่อมาแสดง
     * - เงื่อนไขสถานะ: แสดง active หรือค่า NULL/ว่าง (กันกรณีคอลัมน์ยังไม่ได้ตั้งค่า)
     * - **ถ้าตารางคุณมี created_at จริง ๆ** ให้เพิ่มใน select ได้ตามคอมเมนต์ด้านล่าง
     */
    public function index()
    {
        $query = DB::table('ems_employees as e')
            ->leftJoin('ems_position as p',   'e.emp_position_id',   '=', 'p.id')
            ->leftJoin('ems_department as d', 'e.emp_department_id', '=', 'd.id')
            ->leftJoin('ems_team as t',       'e.emp_team_id',       '=', 't.id')
            ->select(
                'e.id',
                'e.emp_id','e.emp_prefix','e.emp_firstname','e.emp_lastname',
                'e.emp_nickname','e.emp_email','e.emp_phone',
                'e.emp_position_id','e.emp_department_id','e.emp_team_id',
                'e.emp_permission','e.emp_delete_status',
                'e.emp_create_at', // <-- ถ้าตารางมีคอลัมน์นี้อยู่จริง ให้เอา comment ออก
                'p.pst_name  as position_name',
                'd.dpm_name  as department_name',
                't.tm_name   as team_name',


            )
            ->where(function ($q) {
                $q->where('e.emp_delete_status', 'active')
                  ->orWhereNull('e.emp_delete_status')
                  ->orWhere('e.emp_delete_status', '');
            })
            ->orderBy('e.id', 'desc');

        return $query->get();
    }

    /**
     * อ่านข้อมูลพนักงานรายคน (ใช้หน้าแก้ไข)
     */
    public function show($id)
    {
        $emp = DB::table('ems_employees as e')
            ->leftJoin('ems_position as p',   'e.emp_position_id',   '=', 'p.id')
            ->leftJoin('ems_department as d', 'e.emp_department_id', '=', 'd.id')
            ->leftJoin('ems_team as t',       'e.emp_team_id',       '=', 't.id')
            ->where('e.id', $id)
            ->select(
                'e.id',
                'e.emp_id','e.emp_prefix','e.emp_firstname','e.emp_lastname',
                'e.emp_nickname','e.emp_email','e.emp_phone',
                'e.emp_position_id','e.emp_department_id','e.emp_team_id',
                'e.emp_permission','e.emp_delete_status',
                'p.pst_name as position_name',
                'd.dpm_name as department_name',
                't.tm_name  as team_name'
            )
            ->first();

        abort_if(!$emp, 404, 'Employee not found');

        return response()->json($emp);
    }

    /**
     * เมทาดาต้า dropdown (ตำแหน่ง/แผนก/ทีม)
     */
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
            'positions'   => $positions,
            'departments' => $departments,
            'teams'       => $teams,
        ]);
    }

    /**
     * บันทึกพนักงานใหม่
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
            'emp_status'        => ['required'], // map -> emp_permission
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
        ]);

        return response()->json([
            'message' => 'Employee created',
            'data'    => $employee,
        ], 201);
    }

    /**
     * อัปเดตข้อมูลพนักงาน
     * - ส่งเฉพาะฟิลด์ที่ต้องการแก้ (sometimes/nullable)
     * - email/phone ต้อง unique โดย ignore แถวเดิม
     * - ถ้าส่ง emp_password เข้ามา จะถูก hash ก่อนเก็บ
     * - รองรับ alias emp_status -> emp_permission
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
                Rule::unique('ems_employees', 'emp_email')->ignore($emp->id)],
            'emp_phone'         => ['sometimes','nullable','regex:/^[0-9]+$/','min:10','max:10',
                Rule::unique('ems_employees', 'emp_phone')->ignore($emp->id)],
            'emp_position_id'   => ['sometimes','nullable','exists:ems_position,id'],
            'emp_department_id' => ['sometimes','nullable','exists:ems_department,id'],
            'emp_team_id'       => ['sometimes','nullable','exists:ems_team,id'],
            'emp_permission'    => ['sometimes','nullable','string','max:50'],
            'emp_status'        => ['sometimes','nullable','string','max:50'], // alias
            'emp_password'      => ['sometimes','nullable','min:6'],
        ]);

        // เตรียมข้อมูลอัปเดต (ตัด emp_status ออกก่อน map)
        $update = collect($validated)->except(['emp_status'])->toArray();

        // map emp_status -> emp_permission ถ้าส่งมา
        if ($request->filled('emp_status')) {
            $update['emp_permission'] = $request->emp_status;
        }

        // ถ้าส่งรหัสผ่านมา → hash ก่อนเก็บ
        if (array_key_exists('emp_password', $update) && !empty($update['emp_password'])) {
            $update['emp_password'] = Hash::make($update['emp_password']);
        } else {
            unset($update['emp_password']);
        }

        $emp->fill($update)->save();

        return response()->json([
            'message' => 'updated',
            'data'    => $emp->fresh(),
        ]);
    }

    /**
     * ลบแบบ soft delete (ตั้งสถานะ inactive)
     */
    public function destroy($id)
    {
        $emp = Employee::findOrFail($id);
        $emp->emp_delete_status = 'inactive';
        $emp->save();

        return response()->json(['message' => 'Deleted successfully']);
    }
}
