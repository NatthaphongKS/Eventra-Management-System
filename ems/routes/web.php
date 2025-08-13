<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

// หน้า login ของ Vue (ไม่ครอบ auth)
Route::get('/login', fn () => view('spa'))->name('login');

// ทำ login (ไม่ครอบ auth)
Route::post('/logined', [LoginController::class, 'login'])->name('logined');

// ทำ logout (ต้องกำลังล็อกอินอยู่)
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ทุก path อื่น ๆ ต้อง login ก่อน
Route::middleware('auth')->group(function () {
    Route::get('/{any}', fn () => view('spa'))->where('any', '.*');
});
