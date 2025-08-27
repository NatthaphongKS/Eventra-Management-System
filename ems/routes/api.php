<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\CategoryController;

// ตัวอย่าง API ที่ต้อง login (คุณมีอะไรใช้ก็ครอบไว้ได้)
// ถ้า "ทุกหน้า" ต้องล็อกอิน คงไว้ใน group เดิมก็ได้
Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout']);

    // === Employee ===
    Route::get('/meta', [EmployeeController::class, 'meta']);
    Route::get('/get-employees', [EmployeeController::class, 'index']);
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    Route::post('/save-employee', [EmployeeController::class, 'store']);
    Route::post('/save-department', [EmployeeController::class, 'saveDepartment']);
    Route::post('/save-position', [EmployeeController::class, 'savePosition']);
    Route::post('/save-team', [EmployeeController::class, 'saveTeam']);

    // === Event ===
    Route::get('/event-info', [EventController::class, 'eventInfo']);
    Route::get('/get-event', [EventController::class, 'Eventtable']);   // << ใช้กับหน้า List
    Route::delete('/event/{id}', [EventController::class, 'destroy']);  // << ปุ่มลบในหน้า Vue
    Route::post('/event-save', [EventController::class, 'store']);
    //Route::get('/reply/{evn_id}/{emp_id}', [ReplyController::class, 'openForm']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);

    Route::get('/employees/{id}',  [EmployeeController::class, 'show']);    // อ่านพนักงานรายคน
    Route::put('/employees/{id}',  [EmployeeController::class, 'update']);  // อัปเดตพนักงาน
    Route::get('/employees-meta',  [EmployeeController::class, 'meta']);    // รายการตำแหน่ง/แผนก/ทีม
    Route::get('categories', [CategoryController::class, 'index']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);
    Route::get('/event', [EventController::class, 'index']);

});


Route::get('/reply/{evn_id}/{emp_id}', [ReplyController::class, 'show']);
Route::post('/store', [ReplyController::class, 'store']);
