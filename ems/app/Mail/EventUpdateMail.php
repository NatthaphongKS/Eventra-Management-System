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

    public function __construct($employee, $event, $files = [])
    {
        $this->employee = $employee;
        $this->event = $event;
        $this->files = $files;
    }

    public function build()
    {
        // เปลี่ยนหัวข้ออีเมลให้รู้ว่ามีการแก้ไข
        return $this->subject('แจ้งการเปลี่ยนแปลงรายละเอียด: ' . $this->event->evn_title)
                    ->view('emails.event.invitation'); // ใช้ View เดิม (แบบ Outlook ที่ทำไว้)
    }
}