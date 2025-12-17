<!doctype html>
<html lang="th">

<head>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/clicknextIcon.png') }}?v=3">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="evnID" content="{{ $evnID }}">
    <meta name="empID" content="{{ $empID }}">
    <title>แบบตอบรับ</title>
    @vite('resources/js/app.js') {{-- มี @vite แค่ตัวเดียว --}}
</head>

<body>
    <div id="reply-app" v-cloak>Loading…</div>
    <style>
        [v-cloak] {
            display: none
        }
    </style>
</body>

</html>
