<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"><title>Search Engine</title>
    @livewireStyles
    @vite('resources/css/app.css')
</head>
<body>

    <div class="container w-full">
    {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>