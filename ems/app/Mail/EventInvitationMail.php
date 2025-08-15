<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $event;
    public $files;

    public function __construct($employee, $event, $files = [])
    {
        $this->employee = $employee;
        $this->event = $event;
        $this->files = $files; // ไฟล์ที่ส่งมาจาก Controller
    }

    public function build()
    {
        $mail = $this->subject('คำเชิญเข้าร่วมกิจกรรม: ' . $this->event->evn_title)
            ->markdown('emails.event.invitation');

        foreach ($this->files as $f) {
            // รองรับได้ทั้ง array และ object (stdClass/Eloquent)
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
