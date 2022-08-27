<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap" rel="stylesheet">
        <script src="https://kit.fontawesome.com/a32969d67a.js" crossorigin="anonymous"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            body {
                font-family: 'Ubuntu', sans-serif;
            }
        </style>

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        <!-- Scripts -->
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        <script src="{{ asset('js/app.js') }}" defer></script>

        @stack('javascript')
    </head>
    <body>
        <div>
            @include('layouts.navigation')
            <div class="container">
                <!-- Page Heading -->
                <header>
                    <h1>
                        {{ $header }}
                    </h1>
                </header>

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
