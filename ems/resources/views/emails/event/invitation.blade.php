@php
    use Carbon\Carbon;

    // --- DATA ---
    $fname   = $employee->emp_firstname ?? '';
    $lname   = $employee->emp_lastname ?? '';
    $title   = $event->evn_title ?? 'กิจกรรมพิเศษ';
    $formURL1 = $formURL;

    // ปรับ Format วันที่ให้เหมือนในภาพ: "วันพฤหัสบดี 21 ส.ค. 2025"
    // D = วันที่, MMM = เดือนย่อ (ไทย), YYYY = ปี, dddd = ชื่อวันเต็ม
    $dateObj = isset($event->evn_date) ? Carbon::parse($event->evn_date)->locale('th') : null;
    $dateStr = $dateObj ? $dateObj->isoFormat('dddd D MMM YYYY') : '-';

    $tstart  = isset($event->evn_timestart) ? Carbon::parse($event->evn_timestart)->format('H:i') : '';
    $tend    = isset($event->evn_timeend) ? Carbon::parse($event->evn_timeend)->format('H:i') : '';

    $loc     = $event->evn_location ?? '-';
    $desc    = $event->evn_description ?? '';
    $eid     = $event->id ?? 0;
    $empid   = $employee->id ?? 0;

    // --- LINKS ---
    $eventUrl   = url($formURL1);
    // สร้างลิงก์แผนที่ Google Maps จริง
    $mapUrl     = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($loc);
@endphp

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>

    </style>
</head>
<body>
    <div >
        <div>
            <div class=""><strong>เรียน คุณ {{$fname}},</strong></div><br>

            @if($desc)
            <div class="">
                {!! nl2br(e($desc)) !!}
            </div><br>
            @endif

        <div class="date-detail">
            <div>โดยมีรายละเอียดกิจกรรมดังนี้</div>
            <div>กิจกรรมจัดขึ้นใน{{$dateStr}} เริ่มเวลา {{ $tstart }} - {{ $tend }} น.  สถานที่ ณ {{$loc}}</div><br>
        </div>

        <div class="action" style="font-size: 20px">
            <div class="action-link">
                <a href="{{ $eventUrl }}" style="color: #1C73E8; text-decoration: underline;">
                    *คลิกเพื่อตอบรับการเข้าร่วม*
                </a>
            </div>
        </div>
        </div>
        <div>
            <div>หากมีข้อสงสัยเพิ่มเติม สามารถติดต่อกลับมาได้ตามที่อยู่แนบท้าย</div><br>
            <div>หวังว่าจะได้พบทุกท่านในกิจกรรมครั้งนี้</div><br>
            <div>------------------</div>
            <div><strong>Best regards,</strong></div><br>

            <div>{{ $creatorName ?? 'ไม่ระบุชื่อผู้สร้าง' }}</div>
            <div>Tel: {{ $creatorPhone ?? '-' }}</div>

            <div><strong>Clicknext Company Limited.</strong></div>
            <div>
                <img src="{{ $message->embed(public_path('images/email/clicknext_logo.png')) }}" alt="Clicknext Logo" style="max-width: 150px; height: auto;">
            </div>
            <div>128/323-333 30th FL. Phayathai Plaza Bldg.</div>
            <div>Phayathai Road., Rajtaewee, Bangkok 10400 Thailand.</div>
            <a href="https://www.clicknext.com" >www.clicknext.com</a>
        </div>
    </div>
</body>
</html>
