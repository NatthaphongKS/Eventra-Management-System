<?php

/**
 * ชื่อไฟล์: EventUpdateMail.php
 * คำอธิบาย: Mailable สำหรับส่งอีเมลแจ้งเปลี่ยนแปลงรายละเอียดกิจกรรม
 *           แสดง diff ของข้อมูลที่เปลี่ยนแปลง พร้อมแนบไฟล์ที่เกี่ยวข้อง
 * Input: $employee, $event, $files, $formURL, $changes
 * Output: อีเมลแจ้งเปลี่ยนแปลงกิจกรรมไปยังผู้รับ
 * ชื่อผู้เขียน/แก้ไข: รวีโรจน์ สนธิ
 * วันที่จัดทำ/แก้ไข: 10 มีนาคม 2569
 */

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // ข้อมูลพนักงานผู้รับอีเมล
    public $employee;

    // ข้อมูลกิจกรรมที่ถูกแก้ไข
    public $event;

    // รายการไฟล์แนบของกิจกรรม
    public $files;

    // URL สำหรับตอบรับการเข้าร่วมกิจกรรม
    public $formURL;

    /**
     * รายการ field ที่เปลี่ยนแปลง รูปแบบ:
     * [ 'evn_title' => ['old' => '...', 'new' => '...'], ... ]
     */
    public $changes;

    // ชื่อผู้สร้างกิจกรรม (คำนำหน้า ชื่อ นามสกุล (ชื่อเล่น))
    public $creatorName;

    // เบอร์โทรผู้สร้างกิจกรรม
    public $creatorPhone;

    public function __construct($employee, $event, $files = [], $formURL, array $changes = [])
    {
        $this->employee = $employee;
        $this->event    = $event;
        $this->files    = $files;
        $this->formURL  = $formURL;
        $this->changes  = $changes;

        // ดึงข้อมูลผู้สร้างกิจกรรมผ่าน Relationship creator()
        // จัดรูปแบบ: คำนำหน้า ชื่อ นามสกุล (ชื่อเล่น)
        if ($event->creator) {
            $prefix   = $event->creator->emp_prefix    ?? '';
            $fname    = $event->creator->emp_firstname ?? '';
            $lname    = $event->creator->emp_lastname  ?? '';

            // เพิ่มชื่อเล่นในวงเล็บ ถ้ามี
            $nickname = !empty($event->creator->emp_nickname)
                        ? ' (' . $event->creator->emp_nickname . ')'
                        : '';

            $this->creatorName  = trim("$prefix $fname $lname") . $nickname;
            $this->creatorPhone = $event->creator->emp_phone ?? '-';
        } else {
            // กรณีไม่มีข้อมูลผู้สร้าง ใช้ค่า default
            $this->creatorName  = 'ไม่ระบุผู้สร้าง';
            $this->creatorPhone = '-';
        }
    }

    public function build()
    {
        // ดึงชื่อกิจกรรมปัจจุบัน (หลัง update แล้ว)
        $currentTitle = $this->event->evn_title ?? '';

        // ถ้าชื่อกิจกรรมถูกเปลี่ยน ให้แสดงชื่อเดิมใน subject ด้วย
        if (!empty($this->changes['evn_title'])) {
            $oldTitle = $this->changes['evn_title']['old'] ?? '';
            $subject  = "[Clicknext] แจ้งเปลี่ยนแปลงรายละเอียดกิจกรรม {$currentTitle} (จากเดิม {$oldTitle})";
        } else {
            $subject  = "[Clicknext] แจ้งเปลี่ยนแปลงรายละเอียดกิจกรรม {$currentTitle}";
        }

        $mail = $this->subject($subject)
                     ->view('emails.event.Edit');

        // วนแนบไฟล์ทุกรายการที่เชื่อมกับกิจกรรม
        foreach ($this->files as $f) {
            // รองรับทั้ง array และ object
            $path = is_array($f) ? ($f['file_path'] ?? null) : ($f->file_path ?? null);
            $name = is_array($f) ? ($f['file_name'] ?? null) : ($f->file_name ?? null);
            $mime = is_array($f) ? ($f['file_type'] ?? null) : ($f->file_type ?? null);

            if ($path && $name) {
                $fullPath = storage_path('app/public/' . $path);

                // ตรวจสอบว่าไฟล์มีอยู่จริงก่อน attach เพื่อป้องกัน error ใน Queue worker
                if (file_exists($fullPath)) {
                    $mail->attach($fullPath, [
                        'as'   => $name,
                        'mime' => $mime,
                    ]);
                }
            }
        }

        return $mail;
    }
}
