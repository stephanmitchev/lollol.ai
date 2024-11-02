<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOLLOL</title>
    
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-M2LZRZ0WMF"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-M2LZRZ0WMF');
    </script>
    
    @livewireStyles
    @vite('resources/css/app.css')
</head>
<body class="max-w-full w-full">

    <div class=" w-full">
    {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>