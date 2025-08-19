<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    @vite(['resources/js/app.js'])
    <title>Laravel Vue SPA</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/js/main.js')
</head>
<body>
    <div id="app"></div>

</body>
</html>