@component('mail::message')
# สวัสดี {{ $employee->emp_firstname }} {{ $employee->emp_lastname }}

คุณได้รับเชิญให้เข้าร่วมกิจกรรม **{{ $event->evn_title }}**

**รายละเอียด**
- วันที่: {{ \Carbon\Carbon::parse($event->evn_date)->format('d/m/Y') }}
- เวลา: {{ \Carbon\Carbon::parse($event->evn_timestart)->format('H:i') }} - {{ \Carbon\Carbon::parse($event->evn_timeend)->format('H:i') }} (ประมาณ {{ $event->evn_duration }} ชม.)
- สถานที่: {{ $event->evn_location }}

@component('mail::button', ['url' => url('/event/'.$event->id)])
ดูรายละเอียดกิจกรรม
@endcomponent

ขอบคุณ,<br>
Eventra
@endcomponent