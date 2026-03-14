<?php
/**
 * ชื่อไฟล์: EventInvitationMail.php
 * คำอธิบาย: Controller สำหรับจัดการ การส่งคำเชิญเข้าร่วมกิจกรรม
 * ผู้เขียน: Chitdanai
 * วันที่แก้ไข: 09/03/2026
 */
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class EventInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // ข้อมูลพนักงานผู้รับอีเมล
    public $employee;

    // ข้อมูลกิจกรรมที่ถูกสร้าง
    public $event;

    // รายการไฟล์แนบของกิจกรรม
    public $files;

    // URL สำหรับตอบรับการเข้าร่วมกิจกรรม
    public $formURL;

    // วันที่จัดกิจกรรม (Carbon object)
    public $date;

    // วันที่จัดกิจกรรมในรูปแบบภาษาไทย เช่น "21 ส.ค."
    public $dateStr;

    // ชื่อผู้สร้างกิจกรรม (คำนำหน้า ชื่อ นามสกุล (ชื่อเล่น))
    public $creatorName;

    // เบอร์โทรผู้สร้างกิจกรรม
    public $creatorPhone;

    public function __construct($employee, $event, $files = [], $formURL)
    {
        $this->employee = $employee;
        $this->event    = $event;
        $this->files    = $files;
        $this->formURL  = $formURL;

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

        // ชื่อเดือนภาษาไทยแบบย่อ สำหรับแสดงใน Subject ของอีเมล
        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];

        // แปลงวันที่จัดกิจกรรมเป็นรูปแบบไทย เช่น "21 ส.ค."
        // ถ้าไม่มีข้อมูลวันที่ ให้ใช้ค่าว่าง (ไม่แสดงวันที่ใน Subject)
        if (!empty($event->evn_date)) {
            $parsedDate      = Carbon::parse($event->evn_date);
            $day             = $parsedDate->format('j');
            $month           = $thaiMonths[$parsedDate->month];
            $this->dateStr   = $day . ' ' . $month;
        } else {
            $this->dateStr = '';
        }
    }

    public function build()
    {
        // สร้าง Subject โดยต่อท้ายด้วยวันที่ ถ้ามีข้อมูล evn_date
        $subject = '[Clicknext] เชิญเข้าร่วมกิจกรรม '
                   . $this->event->evn_title
                   . ($this->dateStr ? ' วันที่ ' . $this->dateStr : '');

        $mail = $this->subject($subject)
                     ->view('emails.event.invitation');

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
