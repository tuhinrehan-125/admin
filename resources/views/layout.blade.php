<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{config('app.name', 'Holister | Dashboard')}}</title>
    <meta name="description" content="Digital logistics management system.">
    <meta name="keywords" content="logistics, digital, holister, courier, service, buy for you, buy4u">
    <meta name="author" content="Zubair Hasan">
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('assets/img/brand/favicon.png') }}" type="image/png">
    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700">
    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/nucleo/css/nucleo.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') }}" type="text/css">
    <!-- Page plugins -->
    <!-- Argon CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/argon.css?v=1.2.0') }}" type="text/css">
    {{--    nested-list css--}}
    <link rel="stylesheet" href="{{ asset('assets/css/nested-list.css') }}" type="text/css">

    @stack('custom-css')

    {{-- data table --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>

    {{--axios--}}
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
     
</head>
<body>
@include('components.sidenav')
<div class="main-content" id="panel">
    @yield('header')
    <div class="container-fluid">
        @yield('page-content')
        @include('components.footer')
    </div>
</div>
@stack('scripts')
</body>
</html>
