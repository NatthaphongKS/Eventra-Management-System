<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee;
use App\Models\Event;
use App\Models\Connect;
use App\Http\Controllers\{
    EmployeeController,
    LoginController,
    EventController,
    CategoryController,
    HistoryEmployeeController,
    HistoryEventController,
    ReplyController,
    CheckInController,
    HistoryCategoryController,
    DepartmentController,
    TeamController,
    PositionController
};

/*
|--------------------------------------------------------------------------
| Public Routes (เดิม)
|--------------------------------------------------------------------------
*/

Route::get('/reply/{encryptURL}', [ReplyController::class, 'show']);
Route::post('/store', [ReplyController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Mobile API Routes (Flutter) ← เพิ่มใหม่
|--------------------------------------------------------------------------
*/
Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
    ]);

    // ค้นหาจาก email อย่างเดียว (ตาม LoginService เดิม)
    $employee = Employee::where('emp_email', $request->username)->first();

    // เช็ค credentials
    if (!$employee || !Hash::check($request->password, $employee->emp_password)) {
        return response()->json([
            'message' => 'Incorrect username or password. Please try again'
        ], 401);
    }

    // เช็ค disabled
    if ($employee->emp_status === 'disabled') {
        return response()->json(['message' => 'Account is disabled'], 403);
    }

    // เช็ค inactive
    if ($employee->emp_delete_status === 'inactive') {
        return response()->json(['message' => 'Account is inactive'], 403);
    }

    $employee->tokens()->where('name', 'flutter-mobile')->delete();
    $token = $employee->createToken('flutter-mobile')->plainTextToken;

    return response()->json([
        'token' => $token,
        'user'  => [
            'id'    => $employee->id,
            'name'  => $employee->full_name,
            'email' => $employee->emp_email,
        ],
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', function (Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'ออกจากระบบแล้ว']);
    });

    // ── EVENTS ──────────────────────────────────────────────────────────
    Route::get('/events', function (Request $request) {
        $query = Event::whereNull('evn_deleted_at');

        if ($request->filled('status')) {
            $query->where('evn_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('evn_title', 'like', '%' . $request->search . '%')
                    ->orWhere('evn_description', 'like', '%' . $request->search . '%');
            });
        }

        return response()->json([
            'data' => $query->orderBy('evn_date', 'desc')->get()->map(function ($e) {
                return [
                    'id'                => $e->id,
                    'name'              => $e->evn_title ?? '',
                    'description'       => $e->evn_description ?? '',
                    'location'          => $e->evn_location ?? '',
                    'date'              => $e->evn_date?->format('Y-m-d'),
                    'start_time'        => $e->evn_timestart ? \Carbon\Carbon::parse($e->evn_timestart)->format('H:i') : '',
                    'end_time'          => $e->evn_timeend ? \Carbon\Carbon::parse($e->evn_timeend)->format('H:i') : '',
                    'participant_count' => $e->connects()->where('con_delete_status', 0)->count(),
                    'status'            => $e->evn_status ?? 'upcoming',
                ];
            })
        ]);
    });

    Route::get('/events/{id}', function ($id) {
        $e = Event::whereNull('evn_deleted_at')->findOrFail($id);
        return response()->json(['data' => [
            'id'                => $e->id,
            'name'              => $e->evn_title ?? '',
            'description'       => $e->evn_description ?? '',
            'location'          => $e->evn_location ?? '',
            'date'              => $e->evn_date?->format('Y-m-d'),
            'start_time'        => $e->evn_timestart ? \Carbon\Carbon::parse($e->evn_timestart)->format('H:i') : '',
            'end_time'          => $e->evn_timeend ? \Carbon\Carbon::parse($e->evn_timeend)->format('H:i') : '',
            'participant_count' => $e->connects()->where('con_delete_status', 0)->count(),
            'status'            => $e->evn_status ?? 'upcoming',
        ]]);
    });

    Route::post('/events', function (Request $request) {
        $request->validate(['name' => 'required|string', 'date' => 'required|date']);
        $e = Event::create([
            'evn_title'       => $request->name,
            'evn_description' => $request->description ?? '',
            'evn_location'    => $request->location ?? '',
            'evn_date'        => $request->date,
            'evn_timestart'   => $request->start_time,
            'evn_timeend'     => $request->end_time,
            'evn_status'      => $request->status ?? 'upcoming',
            'evn_create_by'   => $request->user()->id,
            'evn_created_at'  => now(),
        ]);
        return response()->json(['data' => ['id' => $e->id, 'name' => $e->evn_title]], 201);
    });

    Route::put('/events/{id}', function (Request $request, $id) {
        $e = Event::whereNull('evn_deleted_at')->findOrFail($id);
        $e->update([
            'evn_title'       => $request->name        ?? $e->evn_title,
            'evn_description' => $request->description ?? $e->evn_description,
            'evn_location'    => $request->location    ?? $e->evn_location,
            'evn_date'        => $request->date        ?? $e->evn_date,
            'evn_timestart'   => $request->start_time  ?? $e->evn_timestart,
            'evn_timeend'     => $request->end_time    ?? $e->evn_timeend,
            'evn_status'      => $request->status      ?? $e->evn_status,
        ]);
        return response()->json(['data' => ['id' => $e->id, 'name' => $e->evn_title]]);
    });

    Route::delete('/events/{id}', function (Request $request, $id) {
        $e = Event::whereNull('evn_deleted_at')->findOrFail($id);
        $e->update(['evn_deleted_at' => now(), 'evn_deleted_by' => $request->user()->id]);
        return response()->json(['message' => 'ลบกิจกรรมแล้ว']);
    });

    // ── GUESTS ──────────────────────────────────────────────────────────
    Route::get('/events/{id}/guests', function ($id) {
        Event::findOrFail($id);
        $guests = Employee::active()
            ->leftJoin('ems_connect', function ($join) use ($id) {
                $join->on('ems_employees.id', '=', 'ems_connect.con_employee_id')
                    ->where('ems_connect.con_event_id', '=', $id)
                    ->where('ems_connect.con_delete_status', '=', 0);
            })
            ->select(
                'ems_employees.id',
                'ems_employees.emp_firstname as first_name',
                'ems_employees.emp_lastname as last_name',
                'ems_employees.emp_email as email',
                'ems_connect.con_event_id',
                'ems_connect.con_answer'
            )
            ->get()
            ->map(function ($g) {
                return [
                    'id'            => $g->id,
                    'first_name'    => $g->first_name ?? '',
                    'last_name'     => $g->last_name ?? '',
                    'email'         => $g->email ?? '',
                    'avatar_url'    => null,
                    'is_invited'    => !is_null($g->con_event_id),
                    'is_checked_in' => $g->con_answer === 'attended',
                ];
            });
        return response()->json(['data' => $guests]);
    });

    Route::post('/events/{eventId}/guests/{guestId}/invite', function ($eventId, $guestId) {
        Connect::updateOrCreate(
            ['con_event_id' => $eventId, 'con_employee_id' => $guestId],
            ['con_delete_status' => 0]
        );
        return response()->json(['message' => 'เชิญแล้ว']);
    });

    Route::delete('/events/{eventId}/guests/{guestId}', function ($eventId, $guestId) {
        Connect::where('con_event_id', $eventId)
            ->where('con_employee_id', $guestId)
            ->update(['con_delete_status' => 1]);
        return response()->json(['message' => 'ยกเลิกการเชิญแล้ว']);
    });

    Route::post('/events/{eventId}/guests/{guestId}/checkin', function (Request $request, $eventId, $guestId) {
        Connect::updateOrCreate(
            ['con_event_id' => $eventId, 'con_employee_id' => $guestId],
            [
                'con_answer'        => $request->boolean('checked_in') ? 'attended' : 'not_attended',
                'con_delete_status' => 0,
            ]
        );
        return response()->json(['message' => 'อัปเดตการเช็คชื่อแล้ว']);
    });
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (web, auth) - ของเดิม ไม่แตะ
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {

    // === Auth & Profile ===
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/show-profile', [LoginController::class, 'showProfile']);

    // === Employee ===
    Route::get('/employees', [EmployeeController::class, 'index']);
    Route::get('/get-employees', [EmployeeController::class, 'index']);
    Route::get('/employees/{id}', [EmployeeController::class, 'show']);
    Route::get('/meta', [EmployeeController::class, 'meta']);
    Route::get('/employees-meta', [EmployeeController::class, 'meta']);
    Route::post('/save-employee', [EmployeeController::class, 'store']);
    Route::post('/check-employee-duplicate', [EmployeeController::class, 'checkDuplicate']);
    Route::post('/import-employees', [EmployeeController::class, 'importBulk']);

    Route::put('/employees/{id}', [EmployeeController::class, 'update']);
    Route::put('/employees/soft-delete/{id}', [EmployeeController::class, 'softDelete']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);

    // === Employee Sub-items ===
    Route::post('/save-department', [EmployeeController::class, 'saveDepartment']);
    Route::post('/save-position', [EmployeeController::class, 'savePosition']);
    Route::post('/save-team', [EmployeeController::class, 'saveTeam']);

    // === Department ===
    Route::get('/departments', [DepartmentController::class, 'index']);
    Route::get('/departments/{id}', [DepartmentController::class, 'show']);
    Route::get('/departments-all', [DepartmentController::class, 'getAll']);
    Route::post('/departments', [DepartmentController::class, 'store']);
    Route::put('/departments/{id}', [DepartmentController::class, 'update']);
    Route::delete('/departments/{id}', [DepartmentController::class, 'destroy']);

    // === Team ===
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('/teams/{id}', [TeamController::class, 'show']);
    Route::get('/teams-all', [TeamController::class, 'getAll']);
    Route::get('/teams/by-department/{departmentId}', [TeamController::class, 'getByDepartment']);
    Route::post('/teams', [TeamController::class, 'store']);
    Route::put('/teams/{id}', [TeamController::class, 'update']);
    Route::delete('/teams/{id}', [TeamController::class, 'destroy']);

    // === Position ===
    Route::get('/positions', [PositionController::class, 'index']);
    Route::get('/positions/{id}', [PositionController::class, 'show']);
    Route::get('/positions-all', [PositionController::class, 'getAll']);
    Route::get('/positions/by-team/{teamId}', [PositionController::class, 'getByTeam']);
    Route::post('/positions', [PositionController::class, 'store']);
    Route::put('/positions/{id}', [PositionController::class, 'update']);
    Route::delete('/positions/{id}', [PositionController::class, 'destroy']);

    // === Event ===
    Route::get('/event', [EventController::class, 'index']);
    Route::get('/get-event', [EventController::class, 'eventTable']);
    Route::get('/event-info', [EventController::class, 'eventInfo']);
    Route::get('/event-info-dashboard', [EventController::class, 'eventInfo']);
    Route::post('/event-statistics', [EventController::class, 'eventStatistics']);
    Route::get('/permission', [EventController::class, 'permission']);
    Route::get('/event/{id}', [EventController::class, 'show']);
    Route::get('/edit-event/{id}', [EventController::class, 'editPages']);
    Route::get('/events/{id}/connects', [EventController::class, 'connectList']);
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);

    Route::post('/event-save', [EventController::class, 'store']);
    Route::post('/edit-event', [EventController::class, 'Update']);
    Route::patch('/event/{id}/deleted', [EventController::class, 'deleted'])->whereNumber('id');

    // === Category ===
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // === Check-in ===
    Route::get('/get-employee-for-checkin/eve_id/{eve_id}', [CheckInController::class, 'getEmployeeForCheckin']);
    Route::get('/get-employee-invite-status/eve_id/{eve_id}/emp_id/{emp_id}', [CheckInController::class, 'getEmployeeInviteStatus']);
    Route::get('/get-employee-checkin-stauts/eve_id/{eve_id}/emp_id/{emp_id}', [CheckInController::class, 'getEmployeeCheckinStatus']);
    Route::put('/update-employee-attendance/eve_id/{eve_id}/emp_id/{emp_id}', [CheckInController::class, 'updateEmployeeAttendance']);
    Route::put('/update-employee-attendance-all/eve_id/{eve_id}', [CheckInController::class, 'updateEmployeeAttendanceAll']);

    // === History ===
    Route::get('/history/employees', [HistoryEmployeeController::class, 'index']);
    Route::get('/history/events', [HistoryEventController::class, 'eventInfo']);
    Route::get('/history/event/{id}', [HistoryEventController::class, 'show']);
    Route::get('/history/categories', [HistoryCategoryController::class, 'index']);
});
