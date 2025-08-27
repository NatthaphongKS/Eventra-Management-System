<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HistoryEmployeeController;
use App\Http\Controllers\HistoryEventController;

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

    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('categories', [CategoryController::class, 'store']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    Route::get('/history/employees', [HistoryEmployeeController::class, 'index']);
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);
    Route::get('/history/events', [HistoryEventController::class, 'eventInfo']);

    // (ถ้าคุณยังมี index() อยู่และใช้งาน ก็เก็บไว้)
    Route::get('/event', [EventController::class, 'index']);
});
