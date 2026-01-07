<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class EmployeeController extends Controller
{
    /**
     * แสดงรายการพนักงาน (หน้า Table)
     */
    public function index()
    {
        $employees = Employee::with([
            'position:id,pst_name',
            'department:id,dpm_name',
            'team:id,tm_name',
            'company:id,com_name'
        ])
            ->where(
                fn($q) => $q
                    ->where('emp_delete_status', 'active')
                    ->orWhereNull('emp_delete_status')
                    ->orWhere('emp_delete_status', '')
            )
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
                'emp_create_at',
            ]);

        $rows = $employees->map(function (Employee $e) {
            return [
                'id' => $e->id,
                'emp_company_id' => $e->company->com_name,
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
                'emp_create_at' => $e->emp_create_at,
                'position_name' => optional($e->position)->pst_name,
                'department_name' => optional($e->department)->dpm_name,
                'team_name' => optional($e->team)->tm_name,
            ];
        });

        return response()->json($rows);
    }

    /**
     * อ่านข้อมูลพนักงานรายคน (ใช้สำหรับหน้าแก้ไข)
     * รองรับทั้ง ID (1,2,3) และ emp_id (Test001)
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
     * ดึงเมทาดาต้าสำหรับ dropdown
     */
    public function meta()
    {
        $prefixes = collect();

        $columns = DB::select("
            SELECT COLUMN_TYPE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'ems_employees'
            AND COLUMN_NAME = 'emp_prefix'
        ");

        if (!empty($columns)) {
            preg_match("/^enum\((.*)\)$/", $columns[0]->COLUMN_TYPE, $matches);

            if (!empty($matches[1])) {
                $prefixes = collect(
                    str_getcsv($matches[1], ',', "'")
                )->values()->map(fn($p, $i) => [
                        'value' => $i + 1,
                        'label' => $p,
                    ]);
            }
        }


        $companies = DB::table('ems_company')
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
     * บันทึกพนักงานใหม่
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
                    'emp_delete_at' => null,
                    'emp_delete_by' => null,
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
                'emp_create_at' => Carbon::now(),
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
     * ลบแบบ soft (Standard)
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
     * ลบแบบ soft (Custom Route)
     */
    public function softDelete($id)
    {
        $user = Auth::user();

        // 🔒 ต้องมี emp_id เท่านั้น
        if (empty($user->emp_id)) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        // 🔑 employee ของคนที่ login
        $loginEmployee = Employee::where('emp_id', $user->emp_id)->first();

        if (!$loginEmployee) {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        // ✅ เช็ค permission ตาม requirement
        if ($loginEmployee->emp_permission !== 'enabled') {
            return response()->json(['message' => 'Permission denied'], 403);
        }

        // 👤 employee ที่จะถูกลบ
        $emp = Employee::where('id', $id)
            ->orWhere('emp_id', $id)
            ->first();

        if (!$emp) {
            return response()->json(['message' => 'Employee not found'], 404);
        }

        // 🗑️ soft delete
        $emp->emp_delete_status = 'inactive';
        $emp->emp_delete_at = now();
        $emp->emp_delete_by = $loginEmployee->id;
        $emp->save();

        return response()->json([
            'message' => 'Employee soft deleted successfully'
        ]);
    }

    // --- ส่วน Import / Helper เดิม ไม่ได้แก้ไข logic ---

    public function importBulk(Request $request)
    {
        $rows = $request->input('rows', []);

        if (!is_array($rows) || count($rows) === 0) {
            return response()->json(['message' => 'No rows provided.'], 422);
        }

        $cleanStr = function ($v) {
            $v = is_string($v) ? trim($v) : '';
            return preg_replace('/\s+/', ' ', $v) ?? '';
        };

        $ensureDepartment = function ($name) {
            $name = trim($name);
            if ($name === '')
                return null;
            $dep = Department::whereRaw('LOWER(TRIM(dpm_name)) = ?', [mb_strtolower($name)])->first();
            if ($dep) {
                if ($dep->dpm_delete_status !== 'active') {
                    $dep->dpm_delete_status = 'active';
                    $dep->save();
                }
                return $dep;
            }
            return Department::create([
                'dpm_name' => $name,
                'dpm_delete_status' => 'active',
                'dpm_create_at' => Carbon::now(),
                'dpm_create_by' => Auth::id(),
            ]);
        };

        $ensurePosition = function ($name) {
            $name = trim($name);
            if ($name === '')
                return null;
            $pos = Position::whereRaw('LOWER(TRIM(pst_name)) = ?', [mb_strtolower($name)])->first();
            if ($pos) {
                if ($pos->pst_delete_status !== 'active') {
                    $pos->pst_delete_status = 'active';
                    $pos->save();
                }
                return $pos;
            }
            return Position::create([
                'pst_name' => $name,
                'pst_delete_status' => 'active',
                'pst_create_at' => Carbon::now(),
                'pst_create_by' => Auth::id(),
            ]);
        };

        $ensureTeam = function ($name) {
            $name = trim($name);
            if ($name === '')
                return null;
            $team = Team::whereRaw('LOWER(TRIM(tm_name)) = ?', [mb_strtolower($name)])->first();
            if ($team) {
                if ($team->tm_delete_status !== 'active') {
                    $team->tm_delete_status = 'active';
                    $team->save();
                }
                return $team;
            }
            return Team::create([
                'tm_name' => $name,
                'tm_delete_status' => 'active',
                'tm_create_at' => Carbon::now(),
                'tm_create_by' => Auth::id(),
            ]);
        };

        $prefixMap = ['นาย' => 1, 'นาง' => 2, 'นางสาว' => 3];
        $splitName = function ($fullName) use ($prefixMap) {
            $fullName = trim($fullName ?? '');
            $parts = preg_split('/\s+/', $fullName);
            $parts = $parts ?: [];
            $emp_prefix = null;
            $emp_firstname = '';
            $emp_lastname = '';

            if (count($parts) >= 3) {
                $maybePrefix = $parts[0];
                $emp_prefix = $prefixMap[$maybePrefix] ?? null;
                $emp_firstname = $parts[1] ?? '';
                $emp_lastname = implode(' ', array_slice($parts, 2));
            } elseif (count($parts) === 2) {
                $emp_firstname = $parts[0] ?? '';
                $emp_lastname = $parts[1] ?? '';
            } elseif (count($parts) === 1) {
                $emp_firstname = $parts[0] ?? '';
            }
            return [$emp_prefix ?? 1, $emp_firstname, $emp_lastname];
        };

        $normPhone = function ($p) {
            if ($p === null)
                return '';
            if (is_numeric($p))
                return str_pad((string) intval($p), 10, '0', STR_PAD_LEFT);
            return preg_replace('/\D+/', '', (string) $p) ?? '';
        };

        $created = [];
        $failed = [];

        foreach ($rows as $row) {
            $emp_id = $cleanStr($row['employeeId'] ?? '');
            $fullName = $cleanStr($row['name'] ?? '');
            $nickname = $cleanStr($row['nickname'] ?? '');
            $phone = $normPhone($row['phone'] ?? '');
            $email = $cleanStr($row['email'] ?? '');
            $departmentName = $cleanStr($row['department'] ?? '');
            $teamName = $cleanStr($row['team'] ?? '');
            $positionName = $cleanStr($row['position'] ?? '');

            [$emp_prefix, $emp_firstname, $emp_lastname] = $splitName($fullName);

            $department = $ensureDepartment($departmentName);
            $position = $ensurePosition($positionName);
            $team = $ensureTeam($teamName);

            if (!$department || !$team || !$position) {
                $failed[] = ['emp_id' => $emp_id, 'reason' => 'Cannot create/find Department / Team / Position'];
                continue;
            }

            $dup = Employee::where(function ($q) use ($emp_id, $email, $phone) {
                $q->where('emp_id', $emp_id)->orWhere('emp_email', $email)->orWhere('emp_phone', $phone);
            })->first(['id']);

            if ($dup) {
                $failed[] = ['emp_id' => $emp_id, 'reason' => 'Duplicate emp_id / email / phone'];
                continue;
            }

            try {
                $emp = Employee::create([
                    'emp_company_id' => auth()->user()->emp_company_id ?? 1,
                    'emp_id' => $emp_id,
                    'emp_prefix' => $emp_prefix,
                    'emp_firstname' => $emp_firstname,
                    'emp_lastname' => $emp_lastname,
                    'emp_nickname' => $nickname,
                    'emp_email' => $email,
                    'emp_phone' => $phone,
                    'emp_position_id' => $position->id,
                    'emp_department_id' => $department->id,
                    'emp_team_id' => $team->id,
                    'emp_password' => Hash::make('Password123'),
                    'emp_permission' => 2,
                    'emp_delete_status' => 'active',
                    'emp_create_at' => Carbon::now(),
                    'emp_create_by' => Auth::id(),
                ]);

                $created[] = ['id' => $emp->id, 'emp_id' => $emp->emp_id];

            } catch (\Throwable $ex) {
                Log::error('EMP_BULK_IMPORT_FAIL', ['error' => $ex->getMessage()]);
                $failed[] = ['emp_id' => $emp_id, 'reason' => 'DB error: ' . $ex->getMessage()];
            }
        }

        return response()->json([
            'message' => count($created) && count($failed) ? 'partial' : (count($created) ? 'success' : 'failed'),
            'created' => $created,
            'failed' => $failed,
        ], 200);
    }

    public function saveDepartment(Request $request)
    {
        $request->validate([
            'dpm_name' => ['required', 'string', 'max:255'],
        ]);

        $rawName = $request->input('dpm_name', '');
        $name = trim($rawName);

        if ($name === '') {
            return response()->json([
                'message' => 'Department name is empty.',
            ], 422);
        }

        $existing = Department::whereRaw('LOWER(TRIM(dpm_name)) = ?', [mb_strtolower($name)])->first();

        if ($existing) {
            if ($existing->dpm_delete_status !== 'active') {
                $existing->dpm_delete_status = 'active';
                $existing->save();

                return response()->json([
                    'id' => $existing->id,
                    'dpm_name' => $existing->dpm_name,
                    'status' => 'reactivated',
                ], 200);
            }
            return response()->json([
                'id' => $existing->id,
                'dpm_name' => $existing->dpm_name,
                'status' => 'existing',
            ], 200);
        }

        $dep = Department::create([
            'dpm_name' => $name,
            'dpm_delete_status' => 'active',
            'dpm_create_at' => Carbon::now(),
            'dpm_create_by' => Auth::id(),
        ]);

        return response()->json([
            'id' => $dep->id,
            'dpm_name' => $dep->dpm_name,
            'status' => 'created',
        ], 201);
    }

    public function savePosition(Request $request)
    {
        $request->validate([
            'pst_name' => ['required', 'string', 'max:255'],
        ]);

        $rawName = $request->input('pst_name', '');
        $name = trim($rawName);

        if ($name === '') {
            return response()->json([
                'message' => 'Position name is empty.',
            ], 422);
        }

        $existing = Position::whereRaw('LOWER(TRIM(pst_name)) = ?', [mb_strtolower($name)])->first();

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

        $pos = Position::create([
            'pst_name' => $name,
            'pst_delete_status' => 'active',
            'pst_create_at' => Carbon::now(),
            'pst_create_by' => Auth::id(),
        ]);

        return response()->json([
            'id' => $pos->id,
            'pst_name' => $pos->pst_name,
            'status' => 'created',
        ], 201);
    }

    public function saveTeam(Request $request)
    {
        $request->validate([
            'tm_name' => ['required', 'string', 'max:255'],
        ]);

        $rawName = $request->input('tm_name', '');
        $name = trim($rawName);

        if ($name === '') {
            return response()->json([
                'message' => 'Team name is empty.',
            ], 422);
        }

        $existing = Team::whereRaw('LOWER(TRIM(tm_name)) = ?', [mb_strtolower($name)])->first();

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
            'tm_delete_status' => 'active',
            'tm_create_at' => Carbon::now(),
            'tm_create_by' => Auth::id(),
        ]);

        return response()->json([
            'id' => $team->id,
            'tm_name' => $team->tm_name,
            'status' => 'created',
        ], 201);
    }

    public function checkDuplicate(Request $request)
    {
        $emp_id = $request->input('emp_id');
        $emp_phone = $request->input('emp_phone');
        $emp_email = $request->input('emp_email');

        $dupFields = [];

        if (!empty($emp_id)) {
            $exists = Employee::where('emp_id', $emp_id)
                ->where(function ($q) {
                    $q->whereNull('emp_delete_status')
                        ->orWhere('emp_delete_status', '')
                        ->orWhere('emp_delete_status', 'active');
                })
                ->exists();

            if ($exists) {
                $dupFields[] = 'emp_id';
            }
        }

        if (!empty($emp_phone)) {
            $exists = Employee::where('emp_phone', $emp_phone)
                ->where(function ($q) {
                    $q->whereNull('emp_delete_status')
                        ->orWhere('emp_delete_status', '')
                        ->orWhere('emp_delete_status', 'active');
                })
                ->exists();

            if ($exists) {
                $dupFields[] = 'emp_phone';
            }
        }

        if (!empty($emp_email)) {
            $exists = Employee::where('emp_email', $emp_email)
                ->where(function ($q) {
                    $q->whereNull('emp_delete_status')
                        ->orWhere('emp_delete_status', '')
                        ->orWhere('emp_delete_status', 'active');
                })
                ->exists();

            if ($exists) {
                $dupFields[] = 'emp_email';
            }
        }

        if (count($dupFields) > 0) {
            return response()->json([
                'duplicate' => true,
                'fields' => $dupFields,
            ], 200);
        }

        return response()->json([
            'duplicate' => false,
            'fields' => [],
        ], 200);
    }
}
