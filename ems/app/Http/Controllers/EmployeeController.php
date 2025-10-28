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
use Carbon\Carbon;


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
            ->where(
                fn($q) => $q
                    ->where('emp_delete_status', 'active')
                    ->orWhereNull('emp_delete_status')
                    ->orWhere('emp_delete_status', '')
            )
            ->orderByDesc('id')
            ->get([
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
                'emp_create_at', // ถ้ามีคอลัมน์นี้จริง
            ]);

        // แปลงเป็นโครงสร้างเดิม (มี *_name แทนที่จะเป็น nested relation)
        $rows = $employees->map(function (Employee $e) {
            return [
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
     * - with() โหลดความสัมพันธ์เพื่อเอาชื่อมาแสดง
     * - คืนรูปแบบแบนเหมือน index
     */
    public function show($id)
    {
        $e = Employee::with(['position:id,pst_name', 'department:id,dpm_name', 'team:id,tm_name'])
            ->findOrFail($id, [
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
     * ดึงเมทาดาต้าสำหรับ dropdown (ตำแหน่ง/แผนก/ทีม)
     * - ใช้โมเดลโดยตรง + where active
     */
    public function meta()
    {
        $positions = Position::query()
            ->select('id', 'pst_name')
            ->where('pst_delete_status', 'active')
            ->orderBy('pst_name')
            ->get();

        $departments = Department::query()
            ->select('id', 'dpm_name')
            ->where('dpm_delete_status', 'active')
            ->orderBy('dpm_name')
            ->get();

        $teams = Team::query()
            ->select('id', 'tm_name')
            ->where('tm_delete_status', 'active')
            ->orderBy('tm_name')
            ->get();

        return response()->json(compact('positions', 'departments', 'teams'));
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
            'emp_id' => ['required', 'unique:ems_employees,emp_id'],
            'emp_prefix' => ['required', 'integer'],
            'emp_firstname' => ['required'],
            'emp_lastname' => ['required'],
            'emp_email' => ['required', 'email', 'unique:ems_employees,emp_email'],
            'emp_phone' => ['required', 'regex:/^[0-9]+$/', 'size:10', 'unique:ems_employees,emp_phone'],
            'emp_position_id' => ['required', 'integer', 'exists:ems_position,id'],
            'emp_department_id' => ['required', 'integer', 'exists:ems_department,id'],
            'emp_team_id' => ['required', 'integer', 'exists:ems_team,id'],
            'emp_password' => ['required', 'min:6'],
            'emp_status' => ['required', 'integer'],
        ]);

        try {
            $employee = Employee::create([
                'emp_company_id' => $request->emp_company_id ?? (auth()->user()->emp_company_id ?? 1),
                'emp_id' => $request->emp_id,
                'emp_prefix' => $request->emp_prefix,
                'emp_firstname' => $request->emp_firstname,
                'emp_lastname' => $request->emp_lastname,
                'emp_nickname' => $request->emp_nickname,
                'emp_email' => $request->emp_email,
                'emp_phone' => $request->emp_phone,
                'emp_position_id' => $request->emp_position_id,
                'emp_department_id' => $request->emp_department_id,
                'emp_team_id' => $request->emp_team_id,
                'emp_password' => Hash::make($request->emp_password),
                'emp_permission' => $request->emp_status,
                'emp_delete_status' => 'active',
                'emp_create_at' => Carbon::now(),
                'emp_create_by' => Auth::id(),
            ]);

            return response()->json(['message' => 'Employee created', 'data' => $employee], 201);

        } catch (QueryException $e) {
            Log::error('EMP_CREATE_FAIL', [
                'sqlstate' => $e->errorInfo[0] ?? null,
                'code' => $e->errorInfo[1] ?? null,
                'msg' => $e->getMessage()
            ]);
            return response()->json(['error' => 'DB_ERROR', 'message' => $e->getMessage()], 500);
        }
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
            'emp_id' => ['sometimes', 'required'],
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
            'emp_status' => ['sometimes', 'nullable', 'string', 'max:50'], // alias
            'emp_password' => ['sometimes', 'nullable', 'min:6'],
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
    public function softDelete($id)
{
    $emp = Employee::find($id);

    if (!$emp) {
        return response()->json(['message' => 'Employee not found'], 404);
    }

    $emp->emp_delete_status = 'inactive';
    $emp->emp_delete_at = now();
    $emp->emp_delete_by = Auth::id();
    $emp->save();

    return response()->json(['message' => 'Employee soft deleted successfully']);
}

    public function importBulk(Request $request)
    {
        /**
         * Expect:
         * rows: [
         *   {
         *     employeeId: "CN0001",
         *     name: "นาย สมปอง แซ่บสุด",
         *     nickname: "มด",
         *     phone: "0918231678",
         *     department: "Product Development",
         *     team: "Mobile",
         *     position: "Mobile",
         *     email: "user@example.com",
         *     dateAdd: "20/08/2025"
         *   },
         *   ...
         * ]
         */
        $rows = $request->input('rows', []);

        if (!is_array($rows) || count($rows) === 0) {
            return response()->json([
                'message' => 'No rows provided.'
            ], 422);
        }

        // helper: ทำให้ string สะอาด (trim + collapse space)
        $cleanStr = function ($v) {
            $v = is_string($v) ? trim($v) : '';
            // ตัด space ซ้ำซ้อน เช่น "  Product   Development "
            $v = preg_replace('/\s+/', ' ', $v);
            return $v ?? '';
        };

        // helper: หา/สร้าง Department (เหมือน saveDepartment ที่เราปรับ)
        // helper: หา/สร้าง Department
        $ensureDepartment = function ($name) {
            $name = trim($name);
            if ($name === '') {
                return null;
            }

            $dep = Department::whereRaw('LOWER(TRIM(dpm_name)) = ?', [mb_strtolower($name)])
                ->first();

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

        // helper: หา/สร้าง Position
        $ensurePosition = function ($name) {
            $name = trim($name);
            if ($name === '') {
                return null;
            }

            $pos = Position::whereRaw('LOWER(TRIM(pst_name)) = ?', [mb_strtolower($name)])
                ->first();

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

        // ✅ helper: หา/สร้าง Team — ไม่สน department อีกต่อไป
        $ensureTeam = function ($name) {
            $name = trim($name);
            if ($name === '') {
                return null;
            }

            $team = Team::whereRaw('LOWER(TRIM(tm_name)) = ?', [mb_strtolower($name)])
                ->first();

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

        // helper: หา/สร้าง Position (เหมือน savePosition)
        $ensurePosition = function ($name) {
            $name = trim($name);
            if ($name === '') {
                return null;
            }

            $pos = Position::whereRaw('LOWER(TRIM(pst_name)) = ?', [mb_strtolower($name)])
                ->first();

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

        // helper: หา/สร้าง Team (เหมือน saveTeam)
        $ensureTeam = function ($name, $depId) {
            $name = trim($name);
            if ($name === '' || !$depId) {
                return null;
            }

            $team = Team::whereRaw('LOWER(TRIM(tm_name)) = ?', [mb_strtolower($name)])
                ->where('tm_department_id', $depId)
                ->first();

            if ($team) {
                if ($team->tm_delete_status !== 'active') {
                    $team->tm_delete_status = 'active';
                    $team->save();
                }
                return $team;
            }

            return Team::create([
                'tm_name' => $name,
                'tm_department_id' => $depId,
                'tm_delete_status' => 'active',
                'tm_create_at' => Carbon::now(),
                'tm_create_by' => Auth::id(),
            ]);
        };

        // helper: แยก prefix / firstname / lastname
        $prefixMap = [
            'นาย' => 1,
            'นาง' => 2,
            'นางสาว' => 3,
        ];
        $splitName = function ($fullName) use ($prefixMap) {
            $fullName = trim($fullName ?? '');
            $parts = preg_split('/\s+/', $fullName);
            $parts = $parts ?: [];

            $emp_prefix = null;
            $emp_firstname = '';
            $emp_lastname = '';

            if (count($parts) >= 3) {
                // [prefix, first, last...]
                $maybePrefix = $parts[0];
                $emp_prefix = $prefixMap[$maybePrefix] ?? null;

                $emp_firstname = $parts[1] ?? '';
                $emp_lastname = implode(' ', array_slice($parts, 2));
            } elseif (count($parts) === 2) {
                // ไม่มี prefix
                $emp_firstname = $parts[0] ?? '';
                $emp_lastname = $parts[1] ?? '';
                $emp_prefix = null;
            } elseif (count($parts) === 1) {
                $emp_firstname = $parts[0] ?? '';
                $emp_lastname = '';
                $emp_prefix = null;
            }

            // fallback prefix ถ้าว่าง ให้ 1 (นาย) เพื่อไม่ปล่อย null ถ้าระบบต้องการ int
            if (!$emp_prefix) {
                $emp_prefix = 1;
            }

            return [$emp_prefix, $emp_firstname, $emp_lastname];
        };

        // helper: normalise เบอร์
        $normPhone = function ($p) {
            if ($p === null)
                return '';
            if (is_numeric($p)) {
                return str_pad((string) intval($p), 10, '0', STR_PAD_LEFT);
            }
            // keep only digits
            $digits = preg_replace('/\D+/', '', (string) $p);
            return $digits ?? '';
        };

        // เริ่ม loop insert
        $created = [];
        $failed = [];

        foreach ($rows as $row) {
            // อ่านค่าจาก row ที่ frontend ส่งมา
            $emp_id = $cleanStr($row['employeeId'] ?? '');
            $fullName = $cleanStr($row['name'] ?? '');
            $nickname = $cleanStr($row['nickname'] ?? '');
            $phone = $normPhone($row['phone'] ?? '');
            $email = $cleanStr($row['email'] ?? '');
            $departmentName = $cleanStr($row['department'] ?? '');
            $teamName = $cleanStr($row['team'] ?? '');
            $positionName = $cleanStr($row['position'] ?? '');

            // 1) แยกชื่อเป็น prefix/firstname/lastname
            [$emp_prefix, $emp_firstname, $emp_lastname] = $splitName($fullName);

            $department = $ensureDepartment($departmentName);
            $position = $ensurePosition($positionName);
            $team = $ensureTeam($teamName); // ไม่ต้องพึ่ง department

            if (!$department || !$team || !$position) {
                $failed[] = [
                    'emp_id' => $emp_id,
                    'reason' => 'Cannot create/find Department / Team / Position',
                ];
                continue;
            }

            // 5) กัน duplicate emp_id / email / phone
            $dup = Employee::where(function ($q) use ($emp_id, $email, $phone) {
                $q->where('emp_id', $emp_id)
                    ->orWhere('emp_email', $email)
                    ->orWhere('emp_phone', $phone);
            })->first(['id']);

            if ($dup) {
                $failed[] = [
                    'emp_id' => $emp_id,
                    'reason' => 'Duplicate emp_id / email / phone',
                ];
                continue;
            }

            // 6) password default + permission default
            $plainPassword = 'Password123';
            $hashedPassword = Hash::make($plainPassword);
            $emp_permission = 2; // สมมติ default "Human Resources"

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
                    'emp_password' => $hashedPassword,
                    'emp_permission' => $emp_permission,
                    'emp_delete_status' => 'active',
                    'emp_create_at' => Carbon::now(),
                    'emp_create_by' => Auth::id(),
                ]);

                $created[] = [
                    'id' => $emp->id,
                    'emp_id' => $emp->emp_id,
                    'email' => $emp->emp_email,
                    'phone' => $emp->emp_phone,
                ];
            } catch (\Throwable $ex) {
                Log::error('EMP_BULK_IMPORT_FAIL', [
                    'error' => $ex->getMessage(),
                    'emp_id' => $emp_id,
                ]);

                $failed[] = [
                    'emp_id' => $emp_id,
                    'reason' => 'DB insert error: ' . $ex->getMessage(),
                ];
            }
        }

        // ตอบกลับ frontend ให้โชว์ใน Swal
        $statusCode = count($created) > 0 && count($failed) === 0
            ? 200
            : (count($created) > 0 ? 207 : 400); // 207 = partial success

        return response()->json([
            'message' => count($created) && count($failed)
                ? 'partial'
                : (count($created) ? 'success' : 'failed'),
            'created_count' => count($created),
            'failed_count' => count($failed),
            'created' => $created,
            'failed' => $failed,
        ], $statusCode);
    }


    /**
     * สร้าง Department ถ้ายังไม่มี
     * ใช้ใน bulk-import / หน้า upload excel
     * Request: { dpm_name: "Engineering" }
     * Return: { id: 7, dpm_name: "Engineering" }
     */
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

        // 1) หา department ที่ชื่อเหมือนกัน (ไม่สนช่องว่าง/ตัวพิมพ์)
        $existing = Department::whereRaw('LOWER(TRIM(dpm_name)) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            // ถ้าเจอแล้วแต่ยังไม่ active -> ปลุกให้ active
            if ($existing->dpm_delete_status !== 'active') {
                $existing->dpm_delete_status = 'active';
                $existing->save();

                return response()->json([
                    'id' => $existing->id,
                    'dpm_name' => $existing->dpm_name,
                    'status' => 'reactivated',
                ], 200);
            }

            // active อยู่แล้ว
            return response()->json([
                'id' => $existing->id,
                'dpm_name' => $existing->dpm_name,
                'status' => 'existing',
            ], 200);
        }

        // 2) ไม่มีในระบบ -> สร้างใหม่
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


    /**
     * สร้าง Position ถ้ายังไม่มี
     * Request: { pst_name: "Software Engineer" }
     * Return: { id: 5, pst_name: "Software Engineer" }
     */
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

        // 1) หา position เดิม (ignore case/space)
        $existing = Position::whereRaw('LOWER(TRIM(pst_name)) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            // ปลุกให้ active ถ้ายังไม่ active
            if ($existing->pst_delete_status !== 'active') {
                $existing->pst_delete_status = 'active';
                $existing->save();

                return response()->json([
                    'id' => $existing->id,
                    'pst_name' => $existing->pst_name,
                    'status' => 'reactivated',
                ], 200);
            }

            // active แล้ว
            return response()->json([
                'id' => $existing->id,
                'pst_name' => $existing->pst_name,
                'status' => 'existing',
            ], 200);
        }

        // 2) ไม่มี -> สร้างใหม่
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

    /**
     * สร้าง Team ถ้ายังไม่มี
     * Team ปกติต้องรู้อยู่ใน department ไหน
     *
     * Request: { tm_name: "CodeCraft", tm_department_id: 7 }
     * Return: { id: 9, tm_name: "CodeCraft" }
     */
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

        // หา team เดิมจากชื่ออย่างเดียว (ไม่สนแผนก)
        $existing = Team::whereRaw('LOWER(TRIM(tm_name)) = ?', [mb_strtolower($name)])
            ->first();

        if ($existing) {
            // ถ้ามีแต่ inactive -> ปลุก
            if ($existing->tm_delete_status !== 'active') {
                $existing->tm_delete_status = 'active';
                $existing->save();

                return response()->json([
                    'id' => $existing->id,
                    'tm_name' => $existing->tm_name,
                    'status' => 'reactivated',
                ], 200);
            }

            // active อยู่แล้ว -> ใช้เลย
            return response()->json([
                'id' => $existing->id,
                'tm_name' => $existing->tm_name,
                'status' => 'existing',
            ], 200);
        }

        // ไม่มี -> สร้างใหม่
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
}
