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
| Public Routes (เข้าถึงได้โดยไม่ต้อง Login)
|--------------------------------------------------------------------------
*/
// จัดการการตอบรับเข้าร่วมกิจกรรม (Reply Form)
Route::prefix('replies')->group(function () {
    Route::post('/', [ReplyController::class, 'store']);              // บันทึกคำตอบรับเข้าร่วมงาน
    Route::get('/{evnID}/{empID}', [ReplyController::class, 'show']); // ดึงข้อมูลสำหรับแสดงหน้าฟอร์มตอบรับ
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (ต้อง Login ก่อนเข้าถึง)
|--------------------------------------------------------------------------
*/
Route::middleware(['web', 'auth'])->group(function () {

    // === Auth & User (จัดการผู้ใช้งาน) ===
    Route::post('/logout', [LoginController::class, 'logout']);       // ออกจากระบบ
    Route::get('/profile', [LoginController::class, 'showProfile']);  // ดูข้อมูลโปรไฟล์ส่วนตัว

    // === Employees (จัดการพนักงาน) ===
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index']);      // ดูรายชื่อพนักงานทั้งหมด
        Route::post('/', [EmployeeController::class, 'store']);     // สร้างพนักงานใหม่

        // ข้อมูลเสริมและการจัดการไฟล์
        Route::get('/meta', [EmployeeController::class, 'meta']);   // ดึงข้อมูลตั้งต้น (ตำแหน่ง/แผนก) สำหรับ Dropdown
        Route::post('/check-duplicate', [EmployeeController::class, 'checkDuplicate']); // ตรวจสอบข้อมูลซ้ำ
        Route::post('/import', [EmployeeController::class, 'importBulk']); // นำเข้าพนักงานจากไฟล์ Excel/CSV

        // จัดการข้อมูลย่อย (แผนก/ตำแหน่ง/ทีม)
        Route::post('/departments', [EmployeeController::class, 'saveDepartment']); // เพิ่มแผนก
        Route::post('/positions', [EmployeeController::class, 'savePosition']);     // เพิ่มตำแหน่ง
        Route::post('/teams', [EmployeeController::class, 'saveTeam']);             // เพิ่มทีม

        // จัดการพนักงานรายคน
        Route::get('/{id}', [EmployeeController::class, 'show']);    // ดูรายละเอียดพนักงานรายคน
        Route::put('/{id}', [EmployeeController::class, 'update']);  // แก้ไขข้อมูลพนักงาน
        Route::delete('/{id}', [EmployeeController::class, 'destroy']); // ลบพนักงาน (Soft Delete)
    });

    // === Events (จัดการกิจกรรม) ===
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'index']);         // ดูรายการกิจกรรมทั้งหมด
        Route::post('/', [EventController::class, 'store']);        // สร้างกิจกรรมใหม่

        // ข้อมูลเสริมและสถิติ
        Route::get('/meta', [EventController::class, 'eventInfo']); // ดึงข้อมูลตั้งต้นสำหรับสร้างกิจกรรม
        Route::post('/statistics', [EventController::class, 'eventStatistics']); // ดูสถิติกิจกรรม
        Route::get('/permissions', [EventController::class, 'permission']); // ตรวจสอบสิทธิ์การเข้าถึง

        // จัดการกิจกรรมรายตัว
        Route::prefix('{id}')->group(function () {
            Route::get('/', [EventController::class, 'show']);          // ดูรายละเอียดกิจกรรม
            Route::get('/edit-data', [EventController::class, 'edit_pages']); // ดึงข้อมูลสำหรับหน้าแก้ไข
            Route::post('/update', [EventController::class, 'Update']); // บันทึกการแก้ไขกิจกรรม (ใช้ POST เพื่อรองรับไฟล์แนบ)
            Route::delete('/', [EventController::class, 'deleted']);    // ลบกิจกรรม

            // ข้อมูลที่เกี่ยวข้องกับกิจกรรม
            Route::get('/connects', [EventController::class, 'connectList']); // รายชื่อคนที่ได้รับเชิญ
            Route::get('/employees/{emp_id}', [EmployeeController::class, 'show']); // ดูข้อมูลพนักงานในบริบทของกิจกรรมนี้

            // ระบบเช็คชื่อ (Check-in)
            Route::prefix('check-in')->group(function () {
                Route::get('/employees', [CheckInController::class, 'getEmployeeForCheckin']); // รายชื่อคนสำหรับเช็คชื่อ
                Route::put('/all', [CheckInController::class, 'updateEmployeeAttendanceAll']); // เช็คชื่อทุกคนพร้อมกัน

                // เช็คชื่อรายคน
                Route::prefix('employees/{empId}')->group(function () {
                    Route::get('/invite-status', [CheckInController::class, 'getEmployeeInviteStatus']); // ดูสถานะคำเชิญ
                    Route::get('/status', [CheckInController::class, 'getEmployeeCheckinStatus']); // ดูสถานะเช็คชื่อ
                    Route::put('/', [CheckInController::class, 'updateEmployeeAttendance']);       // อัปเดตสถานะเช็คชื่อรายคน
                });
            });
        });
    });

    // === Categories (จัดการหมวดหมู่) ===
    // ใช้ Resource Route (สร้าง index, store, show, update, destroy ให้อัตโนมัติ)
    Route::apiResource('categories', CategoryController::class);

    // === History (ประวัติการลบ) ===
    Route::prefix('history')->group(function () {
        Route::get('/employees', [HistoryEmployeeController::class, 'index']);     // ประวัติการลบพนักงาน
        Route::get('/events', [HistoryEventController::class, 'eventInfo']);       // ประวัติการลบกิจกรรม (รายการ)
        Route::get('/events/{id}', [HistoryEventController::class, 'show']);       // ดูรายละเอียดกิจกรรมที่ถูกลบ
        Route::get('/categories', [HistoryCategoryController::class, 'index']);    // ประวัติการลบหมวดหมู่
    });

});