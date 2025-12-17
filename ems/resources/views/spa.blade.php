<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/clicknextIcon.png') }}?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Prompt:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <meta charset="UTF-8">
    @vite(['resources/js/app.js'])
    <title>Eventra</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>

    <div id="app"></div>

</body>

</html>
