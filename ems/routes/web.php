<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;


// หน้า login ของ Vue (ไม่ครอบ auth)
Route::get('/login', fn() => view('spa'))->name('login');

// ทำ login (ไม่ครอบ auth)
Route::post('/logined', [LoginController::class, 'login'])->name('logined');

// ทำ logout (ต้องกำลังล็อกอินอยู่)
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
// routes/web.php
Route::get('/event/{id}/calendar.ics', function ($id) {
    $event = \App\Models\Event::findOrFail($id);
    $start = \Carbon\Carbon::parse($event->evn_date . ' ' . $event->evn_timestart)->format('Ymd\THis');
    $end = \Carbon\Carbon::parse($event->evn_date . ' ' . $event->evn_timeend)->format('Ymd\THis');
    $title = addslashes($event->evn_title);
    $loc = addslashes($event->evn_location);

    $ics = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//ByteForge//EMS//TH
BEGIN:VEVENT
UID:event-$id@byteforge
DTSTAMP:$start
DTSTART:$start
DTEND:$end
SUMMARY:$title
LOCATION:$loc
END:VEVENT
END:VCALENDAR";

    return response($ics, 200, [
        'Content-Type' => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'attachment; filename=\"event-$id.ics\"'
    ]);
});

// ทุก path อื่น ๆ ต้อง login ก่อน
Route::middleware('auth')->group(function () {
   
});

Route::get('/{any}', fn() => view('spa'))->where('any', '.*');

//my
use App\Http\Controllers\ReplyController;

Route::post('/replies', [ReplyController::class, 'store']);
