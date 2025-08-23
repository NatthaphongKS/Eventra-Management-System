<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EventController;

// ตัวอย่าง API ที่ต้อง login (คุณมีอะไรใช้ก็ครอบไว้ได้)
Route::middleware(['web','auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/meta', [EmployeeController::class, 'meta']);
    Route::get('/get-employees', [EmployeeController::class, 'index']);
    Route::get('/event-info', [EventController::class, 'eventInfo']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);

    Route::post('/save-employee', [EmployeeController::class, 'store']);
    Route::post('/save-department', [EmployeeController::class, 'saveDepartment']);
    Route::post('/save-position', [EmployeeController::class, 'savePosition']);
    Route::post('/save-team', [EmployeeController::class, 'saveTeam']);
    Route::post('/event-save', [EventController::class, 'store']);

    // เพิ่มเส้นทางสำหรับอ่าน/แก้ไขรายบุคคล
Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
Route::put('/employees/{employee}', [EmployeeController::class, 'update']);

// (ตัวเลือก dropdown) เอาไว้ดึงรายการแผนก/ทีม/ตำแหน่ง
Route::get('/departments', fn() => Department::orderBy('name')->get());
Route::get('/teams', fn() => Team::orderBy('name')->get());
Route::get('/positions', fn() => Position::orderBy('name')->get());

Route::get('/employees/{id}',  [EmployeeController::class, 'show']);    // อ่านพนักงานรายคน
Route::put('/employees/{id}',  [EmployeeController::class, 'update']);  // อัปเดตพนักงาน
Route::get('/employees-meta',  [EmployeeController::class, 'meta']);    // รายการตำแหน่ง/แผนก/ทีม
});
