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
        body {
            font-family: 'Sarabun', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f4f4;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            background-color: #ffffff;
            margin: 20px auto;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .title {
            font-size: 24px;
            font-weight: 700;
            color: #111111;
            margin-top: 0;
            margin-bottom: 10px;
            line-height: 1.2;
        }
        .date-line {
            font-size: 16px;
            color: #444444;
            margin-bottom: 25px;
        }
        .description {
            font-size: 15px;
            line-height: 1.6;
            color: #555555;
            margin-bottom: 30px;
            white-space: pre-line; /* รักษาการขึ้นบรรทัดใหม่จากข้อมูลใน DB */
        }
        .location-section {
            border-top: 1px solid #eeeeee;
            padding-top: 20px;
            margin-bottom: 30px;
        }
        .location-label {
            font-weight: 700;
            font-size: 14px;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .location-value {
            font-size: 16px;
            color: #111111;
            margin-bottom: 10px;
        }
        .map-link {
            display: inline-block;
            color: #0066CC;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
        }
        .map-link:hover {
            text-decoration: underline;
        }
        .footer-action {
            margin-top: 40px;
            text-align: left;
        }
        .action-link {
            font-weight: 600;
            color: #111111;
            text-decoration: underline;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <h1 class="title">{{ $title }}</h1>

            <div class="date-line">
                วัน{{ $dateStr }} · {{ $tstart }} - {{ $tend }}
            </div>

            @if($desc)
            <div class="description">
                {!! nl2br(e($desc)) !!}
            </div>
            @endif

            <div class="location-section">
                <div class="location-label">ตำแหน่งที่ตั้ง</div>
                <div class="location-value">
                    {{ $loc }}
                </div>
            </div>

            <div class="footer-action">
                <a href="{{ $eventUrl }}" class="action-link">*คลิกเพื่อตอบรับการเข้าร่วม*</a>
            </div>
        </div>
    </div>
</body>
</html>
