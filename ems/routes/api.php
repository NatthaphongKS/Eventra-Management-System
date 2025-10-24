<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HistoryEmployeeController;
use App\Http\Controllers\HistoryEventController;
use App\Http\Controllers\ReplyController;
use App\Http\Controllers\CheckInController;

// API ที่ต้อง login
// ถ้า "ทุกหน้า" ต้องล็อกอิน คงไว้ใน group เดิมก็ได้
Route::middleware(['web', 'auth'])->group(function () {


    Route::post('/logout', [LoginController::class, 'logout']); // ล็อคเอ้าท์

    // === Employee ===
    Route::get('/meta', [EmployeeController::class, 'meta']); //ข้อมูลที่ใช้สร้าง employee
    Route::get('/get-employees', [EmployeeController::class, 'index']); // ข้อมูล employee
    Route::get('/employees', [EmployeeController::class, 'index']);  // alias เผื่อเรียกสั้น ๆ
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']); // soft delete employee
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);
    Route::get('/event-info', [EventController::class, 'index']); // ข้อมูล event
    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
    Route::get('/event/{id}', [EventController::class, 'show']);
    Route::get('/events/{id}/connects', [EventController::class, 'connectList']);


    Route::post('/save-employee', [EmployeeController::class, 'store']); //บันทึก employee
    Route::post('/save-department', [EmployeeController::class, 'saveDepartment']); // บันทึก แผนก
    Route::post('/save-position', [EmployeeController::class, 'savePosition']);// บันทึก ตำแหน่ง
    Route::post('/save-team', [EmployeeController::class, 'saveTeam']); // บันทึก ทีม

    // === Event ===
    Route::get('/event-info', [EventController::class, 'eventInfo']);
    Route::get('/get-event', [EventController::class, 'Eventtable']);   // << ใช้กับหน้า List
    // Route::delete('/event/{id}', [EventController::class, 'destroy']);  // << ปุ่มลบในหน้า Vue
    // Route::patch('/event/{id}/deleted', [EventController::class, 'deleted'])->whereNumber('id');
    // Route::patch('/event/{id}/soft-delete', [EventController::class, 'deleted'])->whereNumber('id');
    Route::patch('/event/{id}/deleted', [EventController::class, 'deleted'])->whereNumber('id');
    Route::post('/event-save', [EventController::class, 'store']);
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);

    Route::post('/edit-event', [EventController::class, 'Update']); // บันทึกการแก้ไขอีเว้น
    Route::get('/edit-event/{id}', [EventController::class, 'edit_pages']); // ข้อมูลอีเว้นนั้นๆ อ้างอิงจาก id
    
    // Event participants for dashboard
    Route::get('/event/{id}/participants', [EventController::class, 'getEventParticipants']); // ข้อมูล participants ของ event
    Route::get('/event/{id}/participation-by-department', [EventController::class, 'getEventParticipationByDepartment']); // ข้อมูลการเข้าร่วมแยกตามแผนก
    
    // Attendance data for dashboard
    Route::get('/attendance/{eventId}', [EventController::class, 'getAttendanceData']); // ข้อมูลการเข้าร่วมจริง

    Route::get('/events', [EventController::class, 'index']);


    Route::post('categories', [CategoryController::class, 'store']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    Route::get('/history/employees', [HistoryEmployeeController::class, 'index']); //ประวัติการลบ พนักงาน
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);
    Route::get('/history/events', [HistoryEventController::class, 'eventInfo']); //ประวัติการลบ อีเว้น
    //Route::get('/reply/{evn_id}/{emp_id}', [ReplyController::class, 'openForm']);
    Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
    Route::put('/employees/{employee}', [EmployeeController::class, 'update']);

    Route::get('/employees/{id}', [EmployeeController::class, 'show']);    // อ่านพนักงานรายคน
    Route::put('/employees/{id}', [EmployeeController::class, 'update']);  // อัปเดตพนักงาน
    Route::get('/employees-meta', [EmployeeController::class, 'meta']);    // รายการตำแหน่ง/แผนก/ทีม
    Route::get('/event/{evn_id}/employee/{emp_id}', [EmployeeController::class, 'show']);
    Route::get('/event', [EventController::class, 'index']);

    // === Category ===
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
    Route::post('categories', [CategoryController::class, 'store']);
});


Route::get('/reply/{evnID}/{empID}', [ReplyController::class, 'show']); // ดึงข้อมูลพนักงานกับอีเว้น
Route::post('/store', [ReplyController::class, 'store']);//บันทึกข้อมูลการตอบกลับ

Route::get('/getEmployeeAttendancers/eveId/{eveId}', [CheckInController::class, 'getEmployeeAttendancers']); // ดึงข้อมูลการเช็คอินพนักงาน
Route::put('/updateEmployeeAttendance/conId/{conId}', [CheckInController::class, 'updateEmployeeAttendance']); // อัปเดตสถานะเช็คอินพนักงาน