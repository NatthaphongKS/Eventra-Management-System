<?php
/**
 * ชื่อไฟล์: EventUpdateMail.php
 * คำอธิบาย: Controller สำหรับจัดการ การส่งอีเมลแจ้งการแก้ไขกิจกรรมเข้าร่วมกิจกรรม
 * ผู้เขียน: Chitdanai
 * วันที่แก้ไข: 09/03/2026
 */
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class EventUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $employee;
    public $event;
    public $files;
    public $formURL;
    public $date;
    public $dateStr;

    // เพิ่มตัวแปรสำหรับเก็บชื่อและเบอร์โทรผู้สร้าง
    public $creatorName;
    public $creatorPhone;

    public $oldData; // 1. เพิ่มตัวแปรเก็บข้อมูลเก่า

    public function __construct($employee, $event, $files = [], $formURL, $oldData = [])
    {
        $this->employee = $employee;
        $this->event = $event;
        $this->files = $files;
        $this->formURL = $formURL;
        $this->oldData = $oldData;


        // ดึงข้อมูลผู้สร้างกิจกรรมผ่าน Relationship creator()
        // ดึงข้อมูลผู้สร้างกิจกรรม และจัดรูปแบบ: คำนำหน้า ชื่อ นามสกุล (ชื่อเล่น)
        if ($event->creator) {
            $prefix = $event->creator->emp_prefix ?? '';
            $fname = $event->creator->emp_firstname ?? '';
            $lname = $event->creator->emp_lastname ?? '';
            $nickname = !empty($event->creator->emp_nickname) ? ' (' . $event->creator->emp_nickname . ')' : '';

            // นำตัวแปรมาต่อกัน
            $this->creatorName = trim("$prefix $fname $lname") . $nickname;
            $this->creatorPhone = $event->creator->emp_phone ?? '-';
        } else {
            $this->creatorName = 'ไม่ระบุผู้สร้าง';
            $this->creatorPhone = '-';
        }

        // 1. สร้าง Array เก็บชื่อเดือนภาษาไทยแบบย่อ
        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
            7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        // 2. ตรวจสอบว่ามีข้อมูล evn_date หรือไม่ แล้วนำมาจัดรูปแบบ
        if (!empty($event->evn_date)) {
            $parsedDate = Carbon::parse($event->evn_date);
            $day = $parsedDate->format('j');
            $month = $thaiMonths[$parsedDate->month];

            $this->dateStr = $day . ' ' . $month;
        } else {
            $this->dateStr = '';
        }
    }

    public function build()
    {
        // 3. เพิ่มวันที่ต่อท้าย Subject
        $subject = '[Clicknext] แจ้งการเปลี่ยนแปลงกิจกรรม ' . $this->event->evn_title . ($this->dateStr ? ' วันที่ ' . $this->dateStr : '');

        $mail = $this->subject($subject)
            ->view('emails.event.editation');

        foreach ($this->files as $f) {
            $path = is_array($f) ? ($f['file_path'] ?? null) : ($f->file_path ?? null);
            $name = is_array($f) ? ($f['file_name'] ?? null) : ($f->file_name ?? null);
            $mime = is_array($f) ? ($f['file_type'] ?? null) : ($f->file_type ?? null);

            if ($path && $name) {
                $fullPath = storage_path('app/public/' . $path);
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
