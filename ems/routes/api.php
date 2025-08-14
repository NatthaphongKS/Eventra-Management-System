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

    Route::post('/save-employee', [EmployeeController::class, 'store']);
    Route::post('/save-department', [EmployeeController::class, 'saveDepartment']);
    Route::post('/save-position', [EmployeeController::class, 'savePosition']);
    Route::post('/save-team', [EmployeeController::class, 'saveTeam']);
});