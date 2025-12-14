@php
    use Carbon\Carbon;

    // --- DATA ---
    $fname   = $employee->emp_firstname ?? '';
    $lname   = $employee->emp_lastname ?? '';
    $title   = $event->evn_title ?? 'กิจกรรม';

    // จัดรูปแบบวันที่ (ภาษาไทย)
    $dateObj = isset($event->evn_date) ? Carbon::parse($event->evn_date)->locale('th') : null;
    $dateStr = $dateObj ? $dateObj->isoFormat('dddd D MMM YYYY') : '-';

    $tstart  = isset($event->evn_timestart) ? Carbon::parse($event->evn_timestart)->format('H:i') : '';
    $tend    = isset($event->evn_timeend) ? Carbon::parse($event->evn_timeend)->format('H:i') : '';
    $loc     = $event->evn_location ?? '-';
@endphp

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยกเลิก: {{ $title }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #242424;
            background-color: #ffffff;
            margin: 0;
            padding: 20px;
            font-size: 14px;
            line-height: 1.5;
        }
        .container {
            max-width: 800px;
            margin: 0;
        }
        h1.title {
            font-size: 18px;
            font-weight: 400;
            margin: 0 0 4px 0;
            color: #242424;
        }
        .date-line {
            font-size: 14px;
            color: #444444;
            margin-bottom: 20px;
            /* text-decoration: line-through; ขีดฆ่าเวลาเพื่อให้รู้ว่ายกเลิก */
        }
        .message-box {
            margin-bottom: 24px;
            color: #444;
        }
        .location-section {
            margin-bottom: 30px;
            opacity: 0.7; /* จางลงเล็กน้อย */
        }
        .location-label {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 2px;
        }
    </style>
</head>
<body>
    <div class="container">

        <h1 class="title">ยกเลิกกิจกรรม : {{ $title }}</h1>

        <div class="date-line">
            วัน{{ $dateStr }} · {{ $tstart }} - {{ $tend }}
        </div>

        <div class="message-box">
            <p style="margin: 0 0 10px 0;"><strong>เรียน คุณ {{ $fname }} {{ $lname }}</strong></p>
            <p style="margin: 0;">
                ขอแจ้งให้ทราบว่ากิจกรรมดังกล่าวได้ถูก <strong>"ยกเลิก"</strong> แล้ว <br>
                ทางผู้จัดต้องขออภัยในความไม่สะดวกมา ณ ที่นี้
            </p>
        </div>

        <div class="location-section">
            <div class="location-label">สถานที่ (เดิม)</div>
            <div>{{ $loc }}</div>
        </div>

        <div style="font-size: 12px; color: #888;">
            *อีเมลนี้เป็นการแจ้งเตือนอัตโนมัติ ไม่จำเป็นต้องตอบกลับ*
        </div>

    </div>
</body>
</html>