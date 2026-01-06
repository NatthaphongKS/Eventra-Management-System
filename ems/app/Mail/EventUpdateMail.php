<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $event;
    public $files;

    // 1. เพิ่มตัวแปรนี้
    public $formURL;

    // 2. รับ $formURL เข้ามาใน Constructor (ตัวที่ 4)
    public function __construct($employee, $event, $files = [], $formURL)
    {
        $this->employee = $employee;
        $this->event = $event;
        $this->files = $files;
        $this->formURL = $formURL; // เก็บค่า
    }

    public function build()
    {
        $mail = $this->subject('แจ้งการเปลี่ยนแปลงรายละเอียด: ' . $this->event->evn_title)
                    ->view('emails.event.invitation'); // ใช้ View เดียวกับ Invitation (ที่มีปุ่มกด)

        // 3. เพิ่ม Loop เพื่อแนบไฟล์ (เหมือน InvitationMail)
        // ถ้าไม่ใส่User จะไม่ได้รับไฟล์แนบ แม้จะส่งเข้ามา
        foreach ($this->files as $f) {
            $path = is_array($f) ? ($f['file_path'] ?? null) : ($f->file_path ?? null);
            $name = is_array($f) ? ($f['file_name'] ?? null) : ($f->file_name ?? null);
            $mime = is_array($f) ? ($f['file_type'] ?? null) : ($f->file_type ?? null);

            if ($path && $name) {
                $mail->attach(storage_path('app/public/' . $path), [
                    'as'   => $name,
                    'mime' => $mime,
                ]);
            }
        }

        return $mail;
    }
}
