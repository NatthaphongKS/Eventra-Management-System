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

        <div class=""><strong>เรียน คุณ {{$fname}},</strong></div><br>

        <div class="date-detail">

            <div>กล่าวถึงการเชิญเข้าร่วมสัมมนาหัวข้อ <strong>{{$title}}</strong> ที่มีกำหนดจัดขึ้นในวัน{{$dateStr}}</div><br>
            <div>เนื่องจากเกิดเหตุขัดข้องบางประการทางทีมงานจึงขออภัยที่จะต้องขอ <strong>ยกเลิกการจัดกิจกรรมดังกล่าว</strong></div><br>
            <div>ทั้งนี้ หากมีการกำหนดวันจัดกิจกรรมใหม่อีกครั้ง ทางทีมงานจะรีบแจ้งให้ท่านทราบโดยเร็วที่สุด</div><br>
            <div>จึงเรียนมาเพื่อโปรดทราบ และขออภัยในความไม่สะดวกมา ณ ที่นี้</div><br>

        </div>

        <div>
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
