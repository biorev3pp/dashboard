<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Activecampaign Login | Biorev</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('/img/favicon.png') }}" sizes="16x16">

</head>
<body class="bg-fullscreen">
    <div id="app" class="pt-8p">
        <main class="py-4 text-center">
            @yield('content')
        </main>
    </div>
</body>
</html>
