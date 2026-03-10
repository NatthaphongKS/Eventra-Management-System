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
    public $formURL;

    /**
     * $changes = [
     *   'evn_title'     => ['old' => 'Surviving the AI Era',  'new' => 'การฝึกอบรมความปลอดภัย'],
     *   'evn_date'      => ['old' => '2025-08-21',            'new' => '2025-08-22'],
     *   'evn_timestart' => ['old' => '09:00',                 'new' => '10:00'],
     *   'evn_timeend'   => ['old' => '12:00',                 'new' => '13:00'],
     *   'evn_location'  => ['old' => 'ลานกว้างหน้าบริษัท',    'new' => 'ห้องประชุม A'],
     *   'evn_description'=> ['old' => '...',                  'new' => '...'],
     * ]
     */
    public $changes;

    public function __construct($employee, $event, $files = [], $formURL, array $changes = [])
    {
        $this->employee = $employee;
        $this->event    = $event;
        $this->files    = $files;
        $this->formURL  = $formURL;
        $this->changes  = $changes;
    }

    public function build()
    {
        // ---- Build subject ----
        // ถ้ามีการเปลี่ยนชื่อ event ให้โชว์ชื่อเดิมด้วย
        $currentTitle = $this->event->evn_title ?? '';

        if (!empty($this->changes['evn_title'])) {
            $oldTitle = $this->changes['evn_title']['old'] ?? '';
            $subject  = "[Clicknext] แจ้งเปลี่ยนแปลงรายละเอียดกิจกรรม {$currentTitle} (จากเดิม {$oldTitle})";
        } else {
            $subject  = "[Clicknext] แจ้งเปลี่ยนแปลงรายละเอียดกิจกรรม {$currentTitle}";
        }

        $mail = $this->subject($subject)
                     ->view('emails.event.Edit');

        // แนบไฟล์
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
