@php
/**
 * ชื่อไฟล์: invitation.blade.php
 * คำอธิบาย: หน้าแจ้งอัพเดทอีเมล
 * Input: $employee, $event, $files = [], $formURL ,$oldData = []
 * Output: หน้าต่างอีเมลใน gmail, outlook
 * ชื่อผู้เขียน/แก้ไข: Chitdanai R.
 * วันที่จัดทำ/แก้ไข: 2026-03-09
 */
 use Carbon\Carbon;

// --- DATA ---
$fname   = $employee->emp_firstname ?? '';
$lname   = $employee->emp_lastname ?? '';
$title   = $event->evn_title ?? 'กิจกรรมพิเศษ';
$formURL1 = $formURL;

$dateObj = isset($event->evn_date) ? Carbon::parse($event->evn_date)->locale('th') : null;
$dateStr = $dateObj ? $dateObj->isoFormat('dddd D MMM YYYY') : '-';

$tstart  = isset($event->evn_timestart) ? Carbon::parse($event->evn_timestart)->format('H:i') : '';
$tend    = isset($event->evn_timeend) ? Carbon::parse($event->evn_timeend)->format('H:i') : '';

$loc     = $event->evn_location ?? '-';
$desc    = $event->evn_description ?? '';

// --- คำนวณความเปลี่ยนแปลง ---
$changes = [];
if (!empty($oldData)) {
    $oldTitle = $oldData['title'] ?? '';
    $oldLoc   = $oldData['location'] ?? '';
    $oldStart = $oldData['start'] ?? '';
    $oldEnd   = $oldData['end'] ?? '';

    $oldDateObj = isset($oldData['date']) ? Carbon::parse($oldData['date'])->locale('th') : null;
    $oldDateStr = $oldDateObj ? $oldDateObj->isoFormat('dddd D MMM YYYY') : '-';

    // เช็คการเปลี่ยนชื่อ
    if ($oldTitle != $title && $oldTitle != '') {
        $changes[] = "เปลี่ยนหัวข้อกิจกรรมจากเดิม \"{$oldTitle}\" เป็น \"{$title}\"";
    }
    // เช็คการเปลี่ยนวันที่
    if ($oldDateObj && $dateObj && $oldDateObj->format('Y-m-d') != $dateObj->format('Y-m-d')) {
        $changes[] = "เปลี่ยนวันที่จากเดิม \"{$oldDateStr}\" เป็น \"{$dateStr}\"";
    }
    // เช็คการเปลี่ยนเวลา
    if ($oldStart != $tstart || $oldEnd != $tend) {
        $changes[] = "เปลี่ยนเวลาจากเดิม \"{$oldStart} - {$oldEnd} น.\" เป็น \"{$tstart} - {$tend} น.\"";
    }
    // เช็คการเปลี่ยนสถานที่
    if ($oldLoc != $loc && $oldLoc != '') {
        $changes[] = "เปลี่ยนสถานที่จากเดิม \"{$oldLoc}\" เป็น \"{$loc}\"";
    }
}

// หากเข้าเงื่อนไข Critical change แต่ไม่ตกอยู่ใน if ด้านบนเลย (เช่นแก้ Description หรือไฟล์แนบ)
if (empty($changes)) {
    $changes[] = "มีการอัปเดตรายละเอียด หรือไฟล์แนบของกิจกรรม (กรุณาตรวจสอบในเนื้อหาอีเมล)";
}

$eventUrl   = url($formURL1);
@endphp

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div>
    <div>
        <div class=""><strong>เรียน คุณ {{$fname}},</strong></div><br>
        <div>
            อ้างถึงประกาศเชิญชวนเข้าร่วมกิจกรรม {{$oldData['title']}} ที่ได้ประชาสัมพันธ์ไปก่อนหน้านี้
        </div>
        <div>เนื่องด้วยมีการปรับปรุงรูปแบบและเนื้อหาของกิจกรรมเพื่อให้เกิดประโยชน์สูงสุดแก่ผู้เข้าร่วม ทางทีมงานผู้จัดงานขอแจ้ง <strong>การเปลี่ยนแปลงรายละเอียดกิจกรรมใหม่</strong> โดยมีข้อมูลสำคัญดังนี้</div>
        

        <div style="color: #d32f2f; margin-bottom: 15px;">
            <strong>ระบบมีการอัปเดตข้อมูลกิจกรรมดังนี้:</strong>
            <ul style="margin-top: 5px;">
                @foreach($changes as $change)
                    <li>{{ $change }}</li>
                @endforeach
            </ul>
        </div>

        @if($desc)
        <div class="">
            {!! nl2br(e($desc)) !!}
        </div><br>
        @endif

        <div class="date-detail">
            <div><strong>รายละเอียดกิจกรรม (ข้อมูลล่าสุด) ดังนี้:</strong></div>
            <div>กิจกรรมจัดขึ้นใน{{$dateStr}} เริ่มเวลา {{ $tstart }} - {{ $tend }} น. สถานที่ ณ {{$loc}}</div><br>
        </div>

        <div class="action" style="font-size: 20px">
            <div class="action-link">
                <a href="{{ $eventUrl }}" style="color: #1C73E8; text-decoration: underline;">
                    *คลิกเพื่อตอบรับการเข้าร่วม*
                </a>
            </div>
        </div>
    </div>

    <br>
    <div>
        <div>หากมีข้อสงสัยเพิ่มเติม สามารถติดต่อกลับมาได้ตามที่อยู่แนบท้าย</div><br>
        <div>หวังว่าจะได้พบทุกท่านในกิจกรรมครั้งนี้</div><br>
        <div>------------------</div>
        <div><strong>Best regards,</strong></div><br>

        <div>{{ $creatorName ?? 'ไม่ระบุชื่อผู้สร้าง' }}</div>
        <div>Tel: {{ $creatorPhone ?? '-' }}</div>

        <div><strong>Clicknext Company Limited.</strong></div>
        <br>
        <div>
            <img src="{{ $message->embed(public_path('images/email/clicknext_logo.png')) }}" alt="Clicknext Logo" style="max-width: 150px; height: auto;">
        </div>
        <br>
        <div>128/323-333 30th FL. Phayathai Plaza Bldg.</div>
        <div>Phayathai Road., Rajtaewee, Bangkok 10400 Thailand.</div>
        <a href="https://www.clicknext.com" style="color: #1C73E8;">www.clicknext.com</a>
    </div>
</div>
</body>
</html>