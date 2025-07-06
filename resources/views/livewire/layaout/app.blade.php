<!doctype html>
<html class="no-js" lang="fr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>@yield('title', 'Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Styles -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700,900" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/owl.transitions.css') }}">
    <link rel="stylesheet" href="{{ asset('css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('css/normalize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/meanmenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('css/educate-custon-icon.css') }}">
    <link rel="stylesheet" href="{{ asset('css/morrisjs/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('css/scrollbar/jquery.mCustomScrollbar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/metisMenu/metisMenu.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/metisMenu/metisMenu-vertical.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendar/fullcalendar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/calendar/fullcalendar.print.min.css') }}">
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <script src="{{ asset('js/vendor/modernizr-2.8.3.min.js') }}"></script>
</head>

<body>

<div>
@livewireStyles

    <!-- Sidebar -->
    @livewire('menu-component')

    <!-- Page Content -->
    <div class="all-content-wrapper" >
        @include('layout.header')

        <main style="margin-left: 250px; padding: 20px; flex-grow: 1;">
            <div class="container">
            {{ $slot }}
            </div>
        </main>

        @include('layout.footer')
    </div>
</div>

<!-- Scripts -->
<script src="{{ asset('js/vendor/jquery-1.12.4.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/wow.min.js') }}"></script>
<script src="{{ asset('js/jquery-price-slider.js') }}"></script>
<script src="{{ asset('js/jquery.meanmenu.js') }}"></script>
<script src="{{ asset('js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('js/jquery.sticky.js') }}"></script>
<script src="{{ asset('js/jquery.scrollUp.min.js') }}"></script>
<script src="{{ asset('js/scrollbar/jquery.mCustomScrollbar.concat.min.js') }}"></script>
<script src="{{ asset('js/scrollbar/mCustomScrollbar-active.js') }}"></script>
<script src="{{ asset('js/metisMenu/metisMenu.min.js') }}"></script>
<script src="{{ asset('js/metisMenu/metisMenu-active.js') }}"></script>
<script src="{{ asset('js/sparkline/jquery.sparkline.min.js') }}"></script>
<script src="{{ asset('js/sparkline/jquery.charts-sparkline.js') }}"></script>
<script src="{{ asset('js/calendar/moment.min.js') }}"></script>
<script src="{{ asset('js/calendar/fullcalendar.min.js') }}"></script>
<script src="{{ asset('js/calendar/fullcalendar-active.js') }}"></script>
<script src="{{ asset('js/plugins.js') }}"></script>
<script src="{{ asset('js/main.js') }}"></script>

@livewireScripts

</body>
</html>
