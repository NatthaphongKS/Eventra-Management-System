<?php

/**
 * ชื่อไฟล์: EmployeesService.php
 * คำอธิบาย: Service สำหรับจัดการ Logic ของ Employee
 * ชื่อผู้เขียน/แก้ไข: Thanusin leenarat
 * วันที่จัดทำ/แก้ไข: 23 กุมภาพันธ์ 2569
 */

namespace App\Service\EmployeesServices;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Team;
use App\Models\Position;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class EmployeesService
{
    /**
     * ชื่อฟังก์ชัน: index
     * คำอธิบาย: ดึงข้อมูลพนักงานทั้งหมดที่มีสถานะ active หรือยังไม่ถูกลบ
     * Input: -
     * Output: JSON รายการพนักงานทั้งหมด
     */
    public function index()
    {
        $employees = Employee::with([
            'position:id,pst_name',
            'department:id,dpm_name',
            'team:id,tm_name',
            'company:id,com_name'
        ])
            ->active()
            ->orderByDesc('id')
            ->get([
                'id',
                'emp_company_id',
                'emp_id',
                'emp_prefix',
                'emp_firstname',
                'emp_lastname',
                'emp_nickname',
                'emp_email',
                'emp_phone',
                'emp_position_id',
                'emp_department_id',
                'emp_team_id',
                'emp_permission',
                'emp_delete_status',
                'emp_created_at',
            ]);

        $rows = $employees->map(function (Employee $e) {
            return [
                'id' => $e->id,
                'emp_company_id' => optional($e->company)->com_name,
                'emp_id' => $e->emp_id,
                'emp_prefix' => $e->emp_prefix,
                'emp_firstname' => $e->emp_firstname,
                'emp_lastname' => $e->emp_lastname,
                'emp_nickname' => $e->emp_nickname,
                'emp_email' => $e->emp_email,
                'emp_phone' => $e->emp_phone,
                'emp_position_id' => $e->emp_position_id,
                'emp_department_id' => $e->emp_department_id,
                'emp_team_id' => $e->emp_team_id,
                'emp_permission' => $e->emp_permission,
                'emp_delete_status' => $e->emp_delete_status,
                'emp_created_at' => $e->emp_created_at,
                'position_name' => optional($e->position)->pst_name,
                'department_name' => optional($e->department)->dpm_name,
                'team_name' => optional($e->team)->tm_name,
            ];
        });

        return response()->json($rows);
    }

    /**
     * ชื่อฟังก์ชัน: show
     * คำอธิบาย: ดึงข้อมูลพนักงานรายคน โดยรองรับทั้ง id และ emp_id
     * Input: $id (Primary ID หรือ emp_id)
     * Output: JSON รายละเอียดพนักงาน
     */
    public function show($id)
    {
        // ค้นหาด้วย id หรือ emp_id
        $e = Employee::with(['position:id,pst_name', 'department:id,dpm_name', 'team:id,tm_name'])
            ->where(function ($query) use ($id) {
                $query->where('id', $id)->orWhere('emp_id', $id);
            })
            ->first([
                'id',
                'emp_id',
                'emp_prefix',
                'emp_firstname',
                'emp_lastname',
                'emp_nickname',
                'emp_email',
                'emp_phone',
                'emp_position_id',
                'emp_department_id',
                'emp_team_id',
                'emp_permission',
                'emp_delete_status',
            ]);

        if (!$e) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $row = [
            'id' => $e->id,
            'emp_id' => $e->emp_id,
            'emp_prefix' => $e->emp_prefix,
            'emp_firstname' => $e->emp_firstname,
            'emp_lastname' => $e->emp_lastname,
            'emp_nickname' => $e->emp_nickname,
            'emp_email' => $e->emp_email,
            'emp_phone' => $e->emp_phone,
            'emp_position_id' => $e->emp_position_id,
            'emp_department_id' => $e->emp_department_id,
            'emp_team_id' => $e->emp_team_id,
            'emp_permission' => $e->emp_permission,
            'emp_delete_status' => $e->emp_delete_status,
            'position_name' => optional($e->position)->pst_name,
            'department_name' => optional($e->department)->dpm_name,
            'team_name' => optional($e->team)->tm_name,
        ];

        return response()->json($row);
    }

    /**
     * ชื่อฟังก์ชัน: meta
     * คำอธิบาย: ดึงข้อมูลสำหรับ dropdown เช่น prefix, company, department, team, position
     * Input: -
     * Output: JSON ข้อมูล metadata สำหรับใช้ในฟอร์ม
     */
    public function meta()
    {
        $prefixes = collect(Employee::PREFIXES)
            ->map(fn($label, $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values();

        $companies = Company::query()
            ->select('id', 'com_name')
            ->where('com_delete_status', 'active')
            ->orderBy('com_name')
            ->get();

        $positions = Position::query()
            ->select('id', 'pst_name', 'pst_team_id')
            ->where('pst_delete_status', 'active')
            ->orderBy('pst_name')
            ->get();

        $departments = Department::query()
            ->select('id', 'dpm_name')
            ->where('dpm_delete_status', 'active')
            ->orderBy('dpm_name')
            ->get();

        $teams = Team::query()
            ->select('id', 'tm_name', 'tm_department_id')
            ->where('tm_delete_status', 'active')
            ->orderBy('tm_name')
            ->get();

        return response()->json(compact(
            'prefixes',
            'companies',
            'positions',
            'departments',
            'teams'
        ));
    }

    /**
     * ชื่อฟังก์ชัน: store
     * คำอธิบาย: บันทึกพนักงานใหม่ หรือ Reactivate พนักงานที่ถูกลบ (inactive)
     * Input: Request $request
     * Output: JSON สถานะการสร้างหรือ reactivate
     */
    public function store(Request $request)
    {
        /*
        |----------------------------------------------------------------------
        | 1) Role & Permission
        |----------------------------------------------------------------------
        */
        $role = $request->emp_permission ?? 'employee';
        $isEmployee = $role === 'employee';

        /*
        |----------------------------------------------------------------------
        | 2) Normalize input
        |----------------------------------------------------------------------
        */
        $firstName = trim(mb_strtolower($request->emp_firstname));
        $lastName = trim(mb_strtolower($request->emp_lastname));
        $empId = trim($request->emp_id); // เช่น CN001

        /*
        |----------------------------------------------------------------------
        | 3) Check inactive duplicate (for reactivate)
        |----------------------------------------------------------------------
        */
        $existingInactiveEmp = Employee::where('emp_delete_status', 'inactive')
            ->where(function ($q) use ($request) {
                $q->where('emp_email', $request->emp_email)
                    ->orWhere('emp_phone', $request->emp_phone);
            })
            ->first();
        /*
        |----------------------------------------------------------------------
        | 4) Custom duplicate checks (ACTIVE only)
        |----------------------------------------------------------------------
        */

        $errors = [];

        /*
        | 4.1 Firstname + Lastname duplicate
        */
        $nameQuery = Employee::whereRaw('LOWER(TRIM(emp_firstname)) = ?', [$firstName])
            ->whereRaw('LOWER(TRIM(emp_lastname)) = ?', [$lastName])
            ->where('emp_delete_status', 'active');

        if ($existingInactiveEmp) {
            $nameQuery->where('id', '!=', $existingInactiveEmp->id);
        }

        if ($nameQuery->exists()) {
            $errors['firstName'] = 'First name and last name already exist.';
            $errors['lastName'] = 'First name and last name already exist.';
        }

        /*
        | 4.2 Phone duplicate
        */
        $phoneQuery = Employee::where('emp_phone', $request->emp_phone)
            ->where('emp_delete_status', 'active');

        if ($existingInactiveEmp) {
            $phoneQuery->where('id', '!=', $existingInactiveEmp->id);
        }

        if ($phoneQuery->exists()) {
            $errors['emp_phone'] = 'This Phone is already use';
        }

        /*
        | 4.3 Email duplicate
        */
        $emailQuery = Employee::where('emp_email', $request->emp_email)
            ->where('emp_delete_status', 'active');

        if ($existingInactiveEmp) {
            $emailQuery->where('id', '!=', $existingInactiveEmp->id);
        }

        if ($emailQuery->exists()) {
            $errors['emp_email'] = 'This Email is already use';
        }

        /*
        | 4.4 Employee ID duplicate (company + number)
        */
        $empIdQuery = Employee::where('emp_id', $empId)
            ->where('emp_delete_status', 'active');

        if ($existingInactiveEmp) {
            $empIdQuery->where('id', '!=', $existingInactiveEmp->id);
        }

        if ($empIdQuery->exists()) {
            $errors['employeeNumber'] = 'This Employee ID is already use';
        }

        /*
        | 4.5 Throw all errors at once
        */
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        /*
        |----------------------------------------------------------------------
        | A) Reactivate employee
        |----------------------------------------------------------------------
        */
        if ($existingInactiveEmp) {

            $request->validate([
                'emp_id' => ['required', 'regex:/^[A-Z]+[0-9]{3}$/'],
                'emp_prefix' => ['required', 'integer'],
                'emp_firstname' => ['required'],
                'emp_lastname' => ['required'],
                'emp_email' => ['required', 'email'],
                'emp_phone' => ['required', 'regex:/^[0-9]+$/', 'size:10'],
                'emp_position_id' => ['required', 'integer', 'exists:ems_position,id'],
                'emp_department_id' => ['required', 'integer', 'exists:ems_department,id'],
                'emp_team_id' => ['required', 'integer', 'exists:ems_team,id'],
                'emp_password' => ['nullable', 'min:8'],
                'emp_permission' => ['nullable', 'in:admin,hr,employee'],
            ]);

            try {
                $companyId = $request->filled('emp_company_id')
                    ? $request->emp_company_id
                    : (auth()->user()->emp_company_id ?? 1);

                $existingInactiveEmp->update([
                    'emp_company_id' => $companyId,
                    'emp_id' => $empId,
                    'emp_prefix' => $request->emp_prefix,
                    'emp_firstname' => $firstName,
                    'emp_lastname' => $lastName,
                    'emp_nickname' => $request->emp_nickname,
                    'emp_email' => $request->emp_email,
                    'emp_phone' => $request->emp_phone,
                    'emp_position_id' => $request->emp_position_id,
                    'emp_department_id' => $request->emp_department_id,
                    'emp_team_id' => $request->emp_team_id,
                    'emp_password' => $isEmployee ? null : Hash::make($request->emp_password),
                    'emp_permission' => $role,
                    'emp_delete_status' => 'active',
                    'emp_deleted_at' => null,
                    'emp_delete_by' => null,
                    'emp_created_at' => Carbon::now(),
                ]);

                return response()->json([
                    'message' => 'Employee reactivated successfully',
                    'data' => $existingInactiveEmp,
                ], 201);
            } catch (QueryException $e) {
                Log::error('EMP_REACTIVATE_FAIL', ['msg' => $e->getMessage()]);
                return response()->json([
                    'error' => 'DB_ERROR',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        /*
        |----------------------------------------------------------------------
        | B) Create new employee
        |----------------------------------------------------------------------
        */
        $request->validate([
            'emp_id' => ['required'],
            'emp_prefix' => ['required', 'integer'],
            'emp_firstname' => ['required'],
            'emp_lastname' => ['required'],
            'emp_email' => ['required', 'email'],
            'emp_phone' => ['required', 'regex:/^[0-9]+$/', 'size:10'],
            'emp_position_id' => ['required', 'integer', 'exists:ems_position,id'],
            'emp_department_id' => ['required', 'integer', 'exists:ems_department,id'],
            'emp_team_id' => ['required', 'integer', 'exists:ems_team,id'],
            'emp_password' => ['nullable', 'min:8'],
            'emp_permission' => ['nullable', 'in:admin,hr,employee'],
        ]);

        try {
            $employee = Employee::create([
                'emp_company_id' => $request->emp_company_id ?? (auth()->user()->emp_company_id ?? 1),
                'emp_id' => $empId,
                'emp_prefix' => $request->emp_prefix,
                'emp_firstname' => $firstName,
                'emp_lastname' => $lastName,
                'emp_nickname' => $request->emp_nickname,
                'emp_email' => $request->emp_email,
                'emp_phone' => $request->emp_phone,
                'emp_position_id' => $request->emp_position_id,
                'emp_department_id' => $request->emp_department_id,
                'emp_team_id' => $request->emp_team_id,
                'emp_password' => $isEmployee ? null : Hash::make($request->emp_password),
                'emp_permission' => $role,
                'emp_delete_status' => 'active',
                'emp_created_at' => Carbon::now(),
                'emp_create_by' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Employee created',
                'data' => $employee,
            ], 201);
        } catch (QueryException $e) {

            $sqlState = $e->errorInfo[0] ?? null;
            $errorCode = $e->errorInfo[1] ?? null;

            Log::error('EMP_CREATE_FAIL', [
                'sqlstate' => $sqlState,
                'code' => $errorCode,
                'msg' => $e->getMessage(),
            ]);

            // default message
            $userMessage = 'Unable to create employee. Please try again later.';

            // Duplicate key (เช่น emp_id, email, phone)
            if ($sqlState === '23000') {
                if (str_contains($e->getMessage(), 'emp_id')) {
                    $userMessage = 'This employee ID already exists.';
                } elseif (str_contains($e->getMessage(), 'emp_email')) {
                    $userMessage = 'This email is already in use.';
                } elseif (str_contains($e->getMessage(), 'emp_phone')) {
                    $userMessage = 'This phone number is already in use.';
                } else {
                    $userMessage = 'Duplicate data found. Please check the information.';
                }
            }
            return response()->json([
                'message' => $userMessage
            ], 422);
        }
    }

    /**
     * ชื่อฟังก์ชัน: update
     * คำอธิบาย: แก้ไขข้อมูลพนักงาน พร้อมตรวจสอบสิทธิ์ผู้ใช้งาน
     * Input: Request $request, $id
     * Output: JSON สถานะการอัปเดต
     */
    public function update(Request $request, $id)
    {
        // =========================
        // 1) หา user ที่ login
        // =========================
        $user = Auth::user();

        if (!$user || empty($user->emp_id)) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        $loginEmployee = Employee::where('emp_id', $user->emp_id)->first();

        if (!$loginEmployee) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        // =========================
        // 2) หา employee ที่ถูกแก้ไข
        // =========================
        $emp = Employee::where('id', $id)
            ->orWhere('emp_id', $id)
            ->first();

        if (!$emp) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // =========================
        // 3) HR แก้ Email / Password ไม่ได้
        // =========================
        if ($loginEmployee->emp_permission !== 'admin') {
            // HR + Employee แก้ email / password / role ไม่ได้
            $request->request->remove('emp_email');
            $request->request->remove('emp_password');
            $request->request->remove('password');
            $request->request->remove('emp_permission');
        }

        // =========================
        // 4) Validate
        // =========================
        $validated = $request->validate([
            'emp_id' => [
                'sometimes',
                'required',
                Rule::unique('ems_employees', 'emp_id')->ignore($emp->id)
            ],
            'emp_company_id' => ['sometimes', 'nullable'],
            'emp_prefix' => ['sometimes', 'required'],
            'emp_firstname' => ['sometimes', 'required'],
            'emp_lastname' => ['sometimes', 'required'],
            'emp_nickname' => ['sometimes', 'nullable', 'string', 'max:100'],
            'emp_email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('ems_employees', 'emp_email')->ignore($emp->id)
            ],
            'emp_phone' => [
                'sometimes',
                'nullable',
                'regex:/^[0-9]+$/',
                'min:10',
                'max:10',
                Rule::unique('ems_employees', 'emp_phone')->ignore($emp->id)
            ],
            'emp_position_id' => ['sometimes', 'nullable', 'exists:ems_position,id'],
            'emp_department_id' => ['sometimes', 'nullable', 'exists:ems_department,id'],
            'emp_team_id' => ['sometimes', 'nullable', 'exists:ems_team,id'],
            'emp_permission' => ['sometimes', 'nullable', 'string', 'max:50'],
            'emp_password' => ['sometimes', 'nullable', 'min:6'],
        ]);

        // =========================
        // 5) Update
        // =========================
        // dd($validated);
        $update = $validated;

        // CASE 1: เปลี่ยนเป็น employee → ล้าง password
        if ($request->emp_permission === 'employee') {
            $update['emp_password'] = null;
        }

        // CASE 2: ส่ง password มา และ role ไม่ใช่ employee → hash
        elseif (!empty($update['emp_password'])) {
            $update['emp_password'] = Hash::make($update['emp_password']);
        }

        // CASE 3: ไม่ส่ง password มา → ไม่แก้ password เดิม
        else {
            unset($update['emp_password']);
        }

        $emp->fill($update)->save();

        return response()->json([
            'message' => 'updated',
            'data' => $emp->fresh(),
        ]);
    }


    /**
     * ชื่อฟังก์ชัน: destroy
     * คำอธิบาย: ลบข้อมูลพนักงานแบบ Soft Delete (Standard Route)
     * Input: $id
     * Output: JSON สถานะการลบ
     */
    public function destroy($id)
    {
        $emp = Employee::where('id', $id)->orWhere('emp_id', $id)->first();

        if (!$emp) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $emp->emp_delete_status = 'inactive';
        $emp->save();

        return response()->json(['message' => 'Deleted successfully']);
    }

    /**
     * ชื่อฟังก์ชัน: softDelete
     * คำอธิบาย: ลบข้อมูลพนักงานแบบ Soft Delete พร้อมบันทึกผู้ลบและเวลา
     * Input: $id
     * Output: JSON สถานะการลบ
     */
    public function softDelete($id)
    {
        $user = Auth::user();

        if (empty($user->emp_id)) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        $loginEmployee = Employee::where('emp_id', $user->emp_id)->first();

        if (!$loginEmployee) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        if ($loginEmployee->emp_permission !== 'admin') {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        $emp = Employee::where('id', $id)
            ->orWhere('emp_id', $id)
            ->first();

        if (!$emp) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        $emp->emp_delete_status = 'inactive';
        $emp->emp_deleted_at = now();
        $emp->emp_delete_by = $loginEmployee->id;
        $emp->save();

        return response()->json([
            'message' => 'Employee soft deleted successfully'
        ]);
    }

    /**
     * ชื่อฟังก์ชัน: savePosition
     * คำอธิบาย: บันทึกหรือ Reactivate ตำแหน่งงาน (Position)
     * Input: Request $request
     * Output: JSON สถานะการสร้าง / existing / reactivated
     */
    public function savePosition(Request $request)
    {
        $request->validate([
            'pst_name' => ['required', 'string', 'max:255'],
            'pst_team_id' => ['required', 'integer'], // ถ้ามีตาราง teams แนะนำใช้ exists:teams,id
            // 'pst_team_id' => ['required','integer','exists:teams,id'],
        ]);

        $name = trim((string) $request->input('pst_name', ''));

        if ($name === '') {
            return response()->json(['message' => 'Position name is empty.'], 422);
        }

        $teamId = (int) $request->input('pst_team_id');

        $existing = Position::whereRaw(
            'LOWER(TRIM(pst_name)) = ? AND pst_team_id = ?',
            [mb_strtolower($name), $teamId]
        )->first();

        if ($existing) {
            if ($existing->pst_delete_status !== 'active') {
                $existing->pst_delete_status = 'active';
                $existing->save();

                return response()->json([
                    'id' => $existing->id,
                    'pst_name' => $existing->pst_name,
                    'status' => 'reactivated',
                ], 200);
            }

            return response()->json([
                'id' => $existing->id,
                'pst_name' => $existing->pst_name,
                'status' => 'existing',
            ], 200);
        }

        // ถ้า pst_create_by ห้ามเป็น null ต้องแน่ใจว่า login แล้ว
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $pos = Position::create([
            'pst_name' => $name,
            'pst_team_id' => $teamId,
            'pst_delete_status' => 'active',
        ]);

        return response()->json([
            'id' => $pos->id,
            'pst_name' => $pos->pst_name,
            'status' => 'created',
        ], 201);
    }

    /**
     * ชื่อฟังก์ชัน: saveTeam
     * คำอธิบาย: บันทึกหรือ Reactivate ทีม (Team)
     * Input: Request $request
     * Output: JSON สถานะการสร้าง / existing / reactivated
     */
    public function saveTeam(Request $request)
    {
        $request->validate([
            'tm_name' => ['required', 'string', 'max:255'],
            'tm_department_id' => ['required', 'integer', 'exists:ems_department,id'],
        ]);

        $name = trim((string) $request->input('tm_name', ''));
        if ($name === '') {
            return response()->json(['message' => 'Team name is empty.'], 422);
        }

        $departmentId = (int) $request->input('tm_department_id');

        // กันชื่อซ้ำภายใน department เดียวกัน
        $existing = Team::whereRaw(
            'LOWER(TRIM(tm_name)) = ? AND tm_department_id = ?',
            [mb_strtolower($name), $departmentId]
        )->first();

        if ($existing) {
            if ($existing->tm_delete_status !== 'active') {
                $existing->tm_delete_status = 'active';
                $existing->save();

                return response()->json([
                    'id' => $existing->id,
                    'tm_name' => $existing->tm_name,
                    'status' => 'reactivated',
                ], 200);
            }

            return response()->json([
                'id' => $existing->id,
                'tm_name' => $existing->tm_name,
                'status' => 'existing',
            ], 200);
        }

        $team = Team::create([
            'tm_name' => $name,
            'tm_department_id' => $departmentId,
            'tm_delete_status' => 'active',
        ]);

        return response()->json([
            'id' => $team->id,
            'tm_name' => $team->tm_name,
            'status' => 'created',
        ], 201);
    }

    /**
     * ชื่อฟังก์ชัน: checkDuplicate
     * คำอธิบาย: ตรวจสอบข้อมูลซ้ำ เช่น emp_id, emp_phone, emp_email
     * Input: Request $request
     * Output: JSON ระบุว่ามีข้อมูลซ้ำหรือไม่
     */
    public function checkDuplicate(Request $request)
    {
        $dupFields = [];

        if ($request->filled('emp_id')) {
            $exists = Employee::active()
                ->where('emp_id', $request->emp_id)
                ->exists();

            if ($exists) {
                $dupFields[] = 'emp_id';
            }
        }

        if ($request->filled('emp_phone')) {
            $exists = Employee::active()
                ->where('emp_phone', $request->emp_phone)
                ->exists();

            if ($exists) {
                $dupFields[] = 'emp_phone';
            }
        }

        if ($request->filled('emp_email')) {
            $exists = Employee::active()
                ->where('emp_email', $request->emp_email)
                ->exists();

            if ($exists) {
                $dupFields[] = 'emp_email';
            }
        }

        return response()->json([
            'duplicate' => !empty($dupFields),
            'fields' => $dupFields,
        ]);
    }
}
