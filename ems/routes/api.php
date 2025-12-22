<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    EmployeeController,
    LoginController,
    EventController,
    CategoryController,
    HistoryEmployeeController,
    HistoryEventController,
    ReplyController,
    CheckInController,
    HistoryCategoryController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/reply/{evnID}/{empID}', [ReplyController::class, 'show']);
Route::post('/store', [ReplyController::class, 'store']);


/*
|--------------------------------------------------------------------------
| Authenticated Routes (web, auth)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {

    // === Auth & Profile ===
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/showProfile', [LoginController::class, 'showProfile']);

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

    // === Event ===
    Route::get('/event', [EventController::class, 'index']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/get-event', [EventController::class, 'Eventtable']);
    Route::get('/event-info', [EventController::class, 'eventInfo']);
    Route::get('/get-event', [EventController::class, 'Eventtable']);   // << ใช้กับหน้า List
    // Route::delete('/event/{id}', [EventController::class, 'destroy']);  // << ปุ่มลบในหน้า Vue
    // Route::patch('/event/{id}/deleted', [EventController::class, 'deleted'])->whereNumber('id');
    // Route::patch('/event/{id}/soft-delete', [EventController::class, 'deleted'])->whereNumber('id');
    // Route::get('/me', [EventController::class, 'me']);
    Route::get('/permission', [EventController::class, 'permission']);
    Route::get('/event/{id}', [EventController::class, 'show']);
    Route::get('/edit-event/{id}', [EventController::class, 'edit_pages']);
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
    Route::get('/getEmployeeForCheckin/eveId/{eveId}', [CheckInController::class, 'getEmployeeForCheckin']);
    Route::get('/getEmployeeInviteStatus/eveId/{eveId}/empId/{empId}', [CheckInController::class, 'getEmployeeInviteStatus']);
    Route::get('/getEmployeeCheckinStatus/eveId/{eveId}/empId/{empId}', [CheckInController::class, 'getEmployeeCheckinStatus']);
    Route::put('/updateEmployeeAttendance/empId/{empId}/eveId/{eveId}', [CheckInController::class, 'updateEmployeeAttendance']);
    Route::put('/updateEmployeeAttendanceAll/eveId/{eveId}', [CheckInController::class, 'updateEmployeeAttendanceAll']);

    // === History ===
    Route::get('/history/employees', [HistoryEmployeeController::class, 'index']);
    Route::get('/history/events', [HistoryEventController::class, 'eventInfo']);
    Route::get('/history/event/{id}', [HistoryEventController::class, 'show']);
    Route::get('/history/categories', [HistoryCategoryController::class, 'index']);

});