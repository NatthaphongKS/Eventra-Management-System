@php
    use Carbon\Carbon;

    // --- DATA ---
    $fname   = $employee->emp_firstname ?? '';
    $lname   = $employee->emp_lastname ?? '';
    $title   = $event->evn_title ?? 'กิจกรรมพิเศษ';

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
    $eventUrl   = url('/reply/'.$eid.'/'.$empid);
    // สร้างลิงก์แผนที่ Google Maps จริง
    $mapUrl     = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($loc);
@endphp

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
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
            margin: 0; /* ชิดซ้ายตามภาพ */
        }
        h1.title {
            font-size: 24px;
            font-weight: 400; /* Outlook มักไม่หนามาก */
            margin: 0 0 4px 0;
            color: #242424;
        }
        .date-line {
            font-size: 14px;
            color: #444444;
            margin-bottom: 20px;
        }
        .description {
            font-size: 14px;
            font-weight: 600; /* ทำให้ตัวหนาเหมือนหัวข้อในภาพ */
            color: #242424;
            margin-bottom: 24px;
            white-space: pre-line; /* รองรับการขึ้นบรรทัดใหม่จาก DB */
        }
        .location-section {
            margin-bottom: 30px;
        }
        .location-label {
            font-weight: 700;
            font-size: 14px;
            margin-bottom: 2px;
        }
        .location-value {
            font-size: 14px;
        }
        .map-link {
            color: #0066CC; /* สีฟ้าลิงก์มาตรฐาน */
            text-decoration: none;
            font-weight: 600;
        }
        .map-link:hover {
            text-decoration: underline;
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
            <div>
                <a href="{{ $mapUrl }}" target="_blank" class="map-link">ดูแผนที่</a>
            </div>
        </div>

        <div>
            <a href="{{ $eventUrl }}" class="action-link">*คลิกเพื่อตอบรับการเข้าร่วม*</a>
        </div>

    </div>
</body>
</html>