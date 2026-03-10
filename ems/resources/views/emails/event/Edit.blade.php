@php
/**
 * ชื่อไฟล์: update.blade.php
 * คำอธิบาย: หน้าอีเมลแจ้งเปลี่ยนแปลงรายละเอียดกิจกรรม
 * Input: $employee, $event, $files = [], $formURL, $changes = []
 * Output: หน้าต่างอีเมลใน gmail, outlook
 * ชื่อผู้เขียน/แก้ไข: Chitdanai R.
 * วันที่จัดทำ/แก้ไข: 2026-03-10
 */
    use Carbon\Carbon;

    // --- DATA ---
    $fname    = $employee->emp_firstname ?? '';
    $lname    = $employee->emp_lastname  ?? '';
    $title    = $event->evn_title        ?? 'กิจกรรมพิเศษ';
    $formURL1 = $formURL;

    $dateObj  = isset($event->evn_date)      ? Carbon::parse($event->evn_date)->locale('th')       : null;
    $dateStr  = $dateObj ? $dateObj->isoFormat('dddd D MMM YYYY') : '-';
    $tstart   = isset($event->evn_timestart) ? Carbon::parse($event->evn_timestart)->format('H:i') : '';
    $tend     = isset($event->evn_timeend)   ? Carbon::parse($event->evn_timeend)->format('H:i')   : '';
    $loc      = $event->evn_location         ?? '-';
    $desc     = $event->evn_description      ?? '';

    // --- LINKS ---
    $eventUrl = url($formURL1);

    // --- CHANGES ---
    // รับ $changes จาก EventUpdateMail  [ 'evn_title' => ['old'=>'...','new'=>'...'], ... ]
    $changes = $changes ?? [];

    // Thai label สำหรับแสดงใน email
    $fieldLabels = [
        'evn_title'       => 'ชื่อกิจกรรม',
        'evn_date'        => 'วันที่จัดกิจกรรม',
        'evn_timestart'   => 'เวลาเริ่ม',
        'evn_timeend'     => 'เวลาสิ้นสุด',
        'evn_location'    => 'สถานที่',
        'evn_description' => 'รายละเอียด',
    ];

    // Format ค่าแต่ละ field ให้อ่านง่าย
    $fmtVal = function($key, $val) {
        try {
            if ($key === 'evn_date')      return Carbon::parse($val)->locale('th')->isoFormat('dddd D MMM YYYY');
            if ($key === 'evn_timestart') return Carbon::parse($val)->format('H:i') . ' น.';
            if ($key === 'evn_timeend')   return Carbon::parse($val)->format('H:i') . ' น.';
        } catch (\Exception $e) {}
        return $val;
    };
@endphp

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0; padding: 0; background-color: #f4f4f4;
               font-family: 'Sarabun', 'Helvetica Neue', Helvetica, Arial, sans-serif;
               -webkit-font-smoothing: antialiased; }

        /* ── change diff ── ใช้ table แทน flex เพื่อ support Outlook / Gmail */
        .changes-box {
            border: 1px solid #E8E8E8;
            border-radius: 6px;
            background-color: #FAFAFA;
            padding: 16px 20px;
            margin: 16px 0 20px;
        }
        .changes-box-title {
            font-size: 13px;
            font-weight: 700;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
        }
        .change-row {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #E0E0E0;
        }
        .change-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
        .change-label { font-size: 12px; font-weight: 700; color: #999999; margin-bottom: 5px; }
        .change-diff-table { width: 100%; border-collapse: collapse; }
        .change-old-cell {
            width: 44%; background-color: #FFF0F0; border-left: 3px solid #E53E3E;
            padding: 6px 9px; font-size: 14px; color: #C53030;
            text-decoration: line-through; vertical-align: top; line-height: 1.5;
        }
        .change-arrow-cell {
            width: 8%; text-align: center; font-size: 15px;
            color: #BBBBBB; vertical-align: middle; padding: 0 4px;
        }
        .change-new-cell {
            width: 44%; background-color: #F0FFF4; border-left: 3px solid #38A169;
            padding: 6px 9px; font-size: 14px; color: #276749;
            font-weight: 600; vertical-align: top; line-height: 1.5;
        }
    </style>
</head>
<body>
    <div>
        {{-- ── Greeting ── --}}
        <div><strong>เรียน คุณ {{ $fname }},</strong></div><br>

        {{-- ── Intro ── --}}
        <div>
            อ้างถึงประกาศเชิญชวนเข้าร่วมกิจกรรม <strong>{{ $title }}</strong> ที่ได้ประชาสัมพันธ์ไปก่อนหน้านี้
        </div><br>
        <div>
            เนื่องด้วยมีการปรับปรุงรูปแบบและเนื้อหาของกิจกรรมเพื่อให้เกิดประโยชน์สูงสุดแก่ผู้เข้าร่วม
            ทางทีมงานผู้จัดงานขอแจ้ง <strong>เปลี่ยนแปลงรายละเอียดกิจกรรมใหม่</strong>
            โดยมีข้อมูลสำคัญดังนี้
        </div>

        {{-- ── Changes diff box (แสดงเฉพาะ field ที่เปลี่ยน) ── --}}
        @if(count($changes) > 0)
        <div class="changes-box">
            <div class="changes-box-title">รายการที่เปลี่ยนแปลง</div>

            @foreach($changes as $key => $diff)
                @if(isset($fieldLabels[$key]))
                <div class="change-row">
                    <div class="change-label">{{ $fieldLabels[$key] }}</div>
                    <table class="change-diff-table">
                        <tr>
                            <td class="change-old-cell">{{ $fmtVal($key, $diff['old']) }}</td>
                            <td class="change-arrow-cell">→</td>
                            <td class="change-new-cell">{{ $fmtVal($key, $diff['new']) }}</td>
                        </tr>
                    </table>
                </div>
                @endif
            @endforeach
        </div>
        @endif

        {{-- ── รายละเอียดกิจกรรม (ข้อมูลปัจจุบัน) ── --}}
        @if($desc)
        <div>{!! nl2br(e($desc)) !!}</div><br>
        @endif

        <div>
            <div>โดยมีรายละเอียดกิจกรรมดังนี้</div>
            <div>กิจกรรมจัดขึ้นใน{{ $dateStr }} เริ่มเวลา {{ $tstart }} - {{ $tend }} น. สถานที่ ณ {{ $loc }}</div>
        </div><br>

        {{-- ── Apology ── --}}
        <div>
            ทางทีมงานต้องขออภัยหากการเปลี่ยนแปลงนี้ทำให้ท่านไม่สะดวก
            และหวังเป็นอย่างยิ่งว่าจะได้พบทุกท่านตามกำหนดการใหม่นี้ครับ
        </div><br>

        {{-- ── CTA ── --}}
        <div style="font-size: 20px">
            <a href="{{ $eventUrl }}" style="color: #1C73E8; text-decoration: underline;">
                *คลิกเพื่อตอบรับการเข้าร่วม*
            </a>
        </div><br>

        {{-- ── Footer / Signature (เหมือน invitation.blade.php) ── --}}
        <div>หากมีข้อสงสัยเพิ่มเติม สามารถติดต่อกลับมาได้ตามที่อยู่แนบท้าย</div><br>
        <div>หวังว่าจะได้พบทุกท่านในกิจกรรมครั้งนี้</div><br>
        <div>------------------</div>
        <div><strong>Best regards,</strong></div><br>

        <div>{{ $creatorName  ?? 'ไม่ระบุชื่อผู้สร้าง' }}</div>
        <div>Tel: {{ $creatorPhone ?? '-' }}</div>

        <div><strong>Clicknext Company Limited.</strong></div>
        <div>
            <img src="{{ $message->embed(public_path('images/email/clicknext_logo.png')) }}"
                 alt="Clicknext Logo" style="max-width: 150px; height: auto;">
        </div>
        <div>128/323-333 30th FL. Phayathai Plaza Bldg.</div>
        <div>Phayathai Road., Rajtaewee, Bangkok 10400 Thailand.</div>
        <a href="https://www.clicknext.com">www.clicknext.com</a>
    </div>
</body>
</html>
