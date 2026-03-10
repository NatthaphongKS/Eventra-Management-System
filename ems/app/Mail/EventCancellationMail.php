<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class EventCancellationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var \App\Models\Employee ผู้รับอีเมลล์ */
    public $employee;

    /** @var \App\Models\Event ข้อมูลกิจกรรมที่จะแจ้งการยกเลิก */
    public $event;

    /** @var string วันที่จัดกิจกรรม (ยังไม่จัดรูปแบบ) */
    public $date;

    /** @var string วันที่จัดกิจกรรม (จัดรูปแบบแล้ว เช่น 10 มี.ค.) */
    public $dateStr;

    /** @var string ชื่อผู้สร้างกิจกรรม (พร้อมชื่อเล่น) */
    public $creatorName;

    /** @var string เบอร์โทรศัพท์ของผู้สร้างกิจกรรม */
    public $creatorPhone;

    /**
     * สร้างอีเมลล์แจ้งการยกเลิกกิจกรรม
     *
     * ทำการเก็บข้อมูลพนักงาน กิจกรรม ชื่อผู้สร้าง และจัดรูปแบบวันที่เพื่อส่งในอีเมลล์
     *
     * @param mixed $employee ข้อมูลพนักงาน
     * @param mixed $event ข้อมูลกิจกรรม
     */
    public function __construct($employee, $event)
    {
        $this->employee = $employee;
        $this->event = $event;

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
        $this->employee = $employee;
        $this->event = $event;
        // 1. สร้าง Array เก็บชื่อเดือนภาษาไทยแบบย่อ
        $thaiMonths = [
            1 => 'ม.ค.',
            2 => 'ก.พ.',
            3 => 'มี.ค.',
            4 => 'เม.ย.',
            5 => 'พ.ค.',
            6 => 'มิ.ย.',
            7 => 'ก.ค.',
            8 => 'ส.ค.',
            9 => 'ก.ย.',
            10 => 'ต.ค.',
            11 => 'พ.ย.',
            12 => 'ธ.ค.'
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

    /**
     * สร้างและส่งอีเมลล์ชุดแจ้งการยกเลิกกิจกรรม
     *
     * สร้างหัวข้ออีเมลล์พร้อมชื่อกิจกรรมและวันที่ แล้วเลือก View สำหรับอีเมลล์template
     *
     * @return void
     */
    public function build()
    {
        $dateStr = $this->dateStr ? ' วันที่ ' . $this->dateStr : '';
        return $this->subject('[Clicknext] แจ้งยกเลิกกิจกรรม ' . $this->event->evn_title . $dateStr)
            ->view('emails.event.cancellation');
    }
}
