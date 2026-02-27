<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReplyController;

// Ensure middleware groups are registered (missing Kernel in this setup)
Route::middlewareGroup('web', [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

Route::middlewareGroup('api', [
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
]);

Route::get('/reply/{encryptURL}', [ReplyController::class, 'openForm']);

// หน้า login ของ Vue (ไม่ครอบ auth)
Route::get('/login', fn() => view('spa'))->name('login');

// ทำ login (ไม่ครอบ auth)
Route::post('/login', [LoginController::class, 'login'])->name('login');

// ทำ logout (ต้องกำลังล็อกอินอยู่)
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

//ทุก path อื่น ๆ ต้อง login ก่อน
Route::middleware('auth')->group(function () {
   Route::get('/{any}', fn() => view('spa'))->where('any', '.*');
});

Route::post('/replies', [ReplyController::class, 'store']);
