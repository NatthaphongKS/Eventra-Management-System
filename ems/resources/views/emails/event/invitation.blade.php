@php
    use Carbon\Carbon;
    // --- DATA (ตามที่ให้มา) ---
    $fname   = $employee->emp_firstname ?? '';
    $lname   = $employee->emp_lastname ?? '';
    $title   = $event->evn_title ?? $event->event_title ?? 'กิจกรรมพิเศษ';
    $date    = isset($event->evn_date) ? Carbon::parse($event->evn_date)->isoFormat('D MMMM YYYY') : '';
    $tstart  = isset($event->evn_timestart) ? Carbon::parse($event->evn_timestart)->format('H:mm') : '';
    $tend    = isset($event->evn_timeend) ? Carbon::parse($event->evn_timeend)->format('H:mm') : '';
    $durHrs  = $event->evn_duration ?? $event->event_duration ?? null; // ชั่วโมง
    $loc     = $event->evn_location ?? $event->event_location ?? '';
    $desc    = $event->evn_description ?? $event->event_description ?? '';
    $eid     = $event->id ?? 0;
    $empid  = $employee->id ?? 0;

    // --- LINKS ---
    $eventUrl   = url('/reply/'.$eid'/'$empid);
    $icsUrl     = url('/event/'.$eid.'/calendar.ics');
    $mapUrl     = 'https://www.google.com/maps/search/?api=1&query='.urlencode($loc);

    // --- ASSETS (ใส่ของจริงเอง) ---
    $logo        = asset('images/email/byteforge-logo-light.png');      // 200x60 (โปร่ง)
    $bgLoopGif   = asset('images/email/techno-anime.gif');               // พื้นหลังแอนิเมชัน 1600x900 (เบา)
    $bgStillJpg  = asset('images/email/techno-still.gif');              // fallback สำหรับ Outlook
    $noisePng    = asset('images/email/noise.png');                     // noise โปร่ง 1200x1200
    $chipLine    = asset('images/email/circuit-line.png');              // เส้นวงจร 600x40
    $btnShineGif = asset('images/email/btn-shine.gif');                 // เงาไหลบนปุ่ม (แถบ gif 600x80)
@endphp
<!doctype html>
<html lang="th">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<title>เชิญเข้าร่วม {{ $title }}</title>
<style>
/* ===== Reset สำหรับอีเมล ===== */
img{border:0;outline:none;text-decoration:none;display:block}
a{text-decoration:none}
table{border-collapse:collapse}

/* ===== โทน/ตัวอักษร ===== */
.text      { color:#eaf3ff; }
.muted     { color:#bcd4ff; }
.body-bg   { background:#0a0f1f; }

/* ===== โมบาย ===== */
@media only screen and (max-width:600px){
  .container{width:100% !important;border-radius:0 !important}
  .px{padding-left:18px !important;padding-right:18px !important}
}

/* ===== แอนิเมชัน (ไคลเอนต์ที่รองรับ) ===== */
@keyframes holoScroll {
  0% { background-position: 0% 50% }
  100% { background-position: 200% 50% }
}
@keyframes neonPulse {
  0%,100% { box-shadow:0 0 0 rgba(0,255,255,0), 0 0 0 rgba(173,0,255,0) }
  50%     { box-shadow:0 0 25px rgba(0,255,255,.45), 0 0 55px rgba(173,0,255,.35) }
}
@keyframes borderGlow {
  0%   { filter: drop-shadow(0 0 0 rgba(160,210,255,.0)) }
  100% { filter: drop-shadow(0 0 18px rgba(160,210,255,.55)) }
}
@keyframes shineMove {
  0%   { background-position: -200% 0 }
  100% { background-position: 200% 0 }
}

/* หัวข้อไฮเทคแบบฮอโล (gradient เคลื่อน) */
.holo-title {
  background: linear-gradient(90deg,#00f7ff,#ad00ff,#ff5bd1,#00f7ff);
  -webkit-background-clip:text; background-clip:text; color:transparent;
  background-size: 200% auto;
  animation: holoScroll 5s linear infinite;
}

/* การ์ดแก้ว + ขอบนีออน */
.card-neo {
  background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02));
  border: 1px solid rgba(120,170,255,.28);
  border-radius: 16px;
  animation: borderGlow 1.8s ease-in-out infinite alternate;
}

/* ปุ่มหลัก (มี shine เคลื่อน + pulse) */
.btn-neo {
  display:inline-block; font-weight:800; font-size:16px; border-radius:999px;
  padding:14px 24px; color:#08121f;
  background: linear-gradient(90deg,#00f7ff,#ad00ff,#ff5bd1,#00f7ff);
  background-size: 220% auto; animation: shineMove 4s linear infinite, neonPulse 2.4s ease-in-out infinite;
  box-shadow:0 10px 28px rgba(90,40,255,.45);
}
.btn-wrap {
  position:relative; display:inline-block; border-radius:999px; overflow:hidden;
}
.btn-wrap:after { /* ชั้น shine GIF เป็น fallback/ซ้อนทับบางส่วน */
  content:""; position:absolute; inset:0;
  background:url('{{ $btnShineGif }}') center/cover no-repeat;
  opacity:.35; pointer-events:none;
}

/* ปุ่มรอง */
.btn-yes {
  display:inline-block; padding:10px 16px; font-size:13px; font-weight:700; border-radius:12px;
  background:#69ffd6; color:#08221a; border:1px solid rgba(0,0,0,.05);
}
.btn-no {
  display:inline-block; padding:10px 16px; font-size:13px; font-weight:700; border-radius:12px;
  background:#ffc1ce; color:#30060e; border:1px solid rgba(0,0,0,.05);
}

/* กล่องข้อมูล */
.info-card {
  background: rgba(14,20,40,.86);
  border:1px solid rgba(120,170,255,.35);
  border-radius:14px;
}
.label {
  color:#cfe3ff; font-weight:700; font-size:13px; letter-spacing:.5px; text-transform:uppercase;
}
.value {
  color:#f9fbff; font-size:16px; margin-top:6px;
}
</style>
<!--[if mso]>
<style>
  .holo-title { color:#00AEEF !important; background:none !important; }
  .btn-neo { background:#00AEEF !important; color:#08121f !important; }
</style>
<![endif]-->
</head>

<body class="body-bg" style="margin:0;padding:0;background:#0a0f1f;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;">

  <!-- พื้นหลัง: GIF สำหรับไคลเอนต์ทั่วไป + JPG สำหรับ Outlook -->
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0"
         style="background:#0a0f1f url('{{ $bgLoopGif }}') center/cover no-repeat fixed;">
    <!--[if mso]>
      <tr><td background="{{ $bgStillJpg }}" style="background-position:center; background-size:cover;">
    <![endif]-->
    <tr>
      <td align="center" style="padding:24px;">
        <table role="presentation" class="container" width="640" cellpadding="0" cellspacing="0"
               style="width:640px;max-width:640px;border-radius:22px;overflow:hidden;box-shadow:0 25px 80px rgba(0,0,0,.55);
                      background:
                        radial-gradient(1200px 500px at 50% -10%, rgba(173,0,255,.28), transparent 50%),
                        radial-gradient(900px 400px at 80% 120%, rgba(0,247,255,.18), transparent 55%),
                        linear-gradient(180deg, rgba(255,255,255,.07), rgba(255,255,255,.02));
                      position:relative;">
          <!-- noise overlay -->
          <tr>
            <td style="position:relative">
              <div style="position:absolute;inset:0;background:url('{{ $noisePng }}') repeat;opacity:.07;pointer-events:none;"></div>
              <!-- Header -->
              <table role="presentation" width="100%" style="position:relative; z-index:2;">
                <tr>
                  <td style="padding:20px 24px; border-bottom:1px solid rgba(120,170,255,.35);
                             background:linear-gradient(90deg,rgba(0,247,255,.18),rgba(173,0,255,.18));">
                    <img src="{{ $logo }}" width="160" height="48" alt="ByteForge">
                  </td>
                </tr>

                <!-- Hero Title -->
                <tr>
                  <td style="padding:28px 24px 8px 24px;" align="center">
                    <h1 style="margin:0; font-size:28px; line-height:1.25; letter-spacing:.3px; color:#eaf3ff;">
                      เชิญเข้าร่วม: <span class="holo-title">{{ $title }}</span>
                    </h1>
                    <p class="muted" style="margin:10px 0 0; color:#bed4ff; font-size:14px;">
                      สวัสดี {{ $fname }} {{ $lname }} · บัตรผ่านดิจิทัลของคุณพร้อมแล้ว
                    </p>
                  </td>
                </tr>

                <!-- Divider line -->
                <tr>
                  <td align="center" style="padding:8px 24px 0 24px;">
                    <img src="{{ $chipLine }}" width="560" alt="" style="animation:borderGlow 1.6s ease-in-out infinite alternate;">
                  </td>
                </tr>

                <!-- Info Cards -->
                <tr>
                  <td class="px" style="padding:18px 24px 6px 24px;">
                    <table role="presentation" width="100%">
                      <tr>
                        <td width="50%" valign="top" style="padding:8px;">
                          <table role="presentation" class="info-card" width="100%">
                            <tr><td style="padding:16px 18px;">
                              <div class="label">วันเวลา</div>
                              <div class="value">
                                {{ $date }}<br>
                                {{ $tstart }} - {{ $tend }}
                                @if($durHrs) <span style="color:#a8b4ff;">(ประมาณ {{ $durHrs }} ชม.)</span> @endif
                              </div>
                            </td></tr>
                          </table>
                        </td>
                        <td width="50%" valign="top" style="padding:8px;">
                          <table role="presentation" class="info-card" width="100%">
                            <tr><td style="padding:16px 18px;">
                              <div class="label">สถานที่</div>
                              <div class="value">{{ $loc }}</div>
                              <div style="margin-top:8px;">
                                <a href="{{ $mapUrl }}" target="_blank" style="font-size:12px;color:#99d1ff;text-decoration:underline">ดูแผนที่</a>
                              </div>
                            </td></tr>
                          </table>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>

                @if($desc)
                <!-- Description -->
                <tr>
                  <td class="px" style="padding:6px 24px;">
                    <table role="presentation" class="card-neo" width="100%">
                      <tr>
                        <td style="padding:16px 18px">
                          <div class="text" style="font-size:15px; line-height:1.7;">
                            {!! nl2br(e($desc)) !!}
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                @endif

                <!-- CTAs -->
                <tr>
                  <td align="center" style="padding:14px 24px 6px 24px;">
                    <!-- ปุ่มหลัก (มี shine+pulse) -->
                    <!--[if mso]>
                      <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $eventUrl }}" arcsize="50%" fillcolor="#00AEEF" stroke="f" style="height:44px;v-text-anchor:middle;width:280px;">
                        <center style="color:#08121f;font-family:Segoe UI, Arial;font-size:16px;font-weight:800;">เปิดดูรายละเอียดแบบโฮโล</center>
                      </v:roundrect>
                    <![endif]-->
                    <!--[if !mso]><!-- -->
                    <span class="btn-wrap">
                      <a href="{{ $eventUrl }}" class="btn-neo">เปิดดูรายละเอียดแบบโฮโล</a>
                    </span>
                    <!--<![endif]-->
                  </td>
                </tr>
                <tr>
                </tr>
                <tr>
                  <td align="center" style="padding:10px 24px 18px 24px;">
                    <a href="{{ $icsUrl }}" style="display:inline-block;padding:8px 14px;font-size:12px;border-radius:10px;border:1px solid rgba(120,170,255,.45);color:#bfe0ff">
                      ➕ เพิ่มลงปฏิทิน (ICS)
                    </a>
                  </td>
                </tr>

              </table>
            </td>
          </tr>

        </table>
      </td>
    </tr>
    <!--[if mso]></td></tr><![endif]-->
  </table>
</body>
</html>
