<!doctype html>
<html lang="en">
  @php
    $authAppName = !empty(optional($appConfig)->app_name) ? $appConfig->app_name : APP_NAME;
    $authFavicon = !empty(optional($appConfig)->favicon_url) ? $appConfig->favicon_url : null;
    $authLogo = !empty(optional($appConfig)->logo_url) ? $appConfig->logo_url : null;
  @endphp
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $authAppName }} | Login</title>
    @if($authFavicon)
      <link rel="icon" type="image/png" href="{{ $authFavicon }}">
    @endif
    <link href="{!! url('assets/css/bootstrap.min.css') !!}" rel="stylesheet">
    @include('layouts.styles', ['is_auth_layout' => true])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.1/font/bootstrap-icons.css">
    <script src="{!! url('assets/js/bootstrap.bundle.min.js') !!}"></script>
    <script src="{!! url('assets/js/my-script.js') !!}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- code added by binal start--->
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="{{ !empty(optional($appConfig)->primary_theme_colour) ? $appConfig->primary_theme_colour : '#012341' }}">
    <!-- code added by binal end--->
</head>
<body>
	<div class="wrapper">
@yield('content')
	</div>
	</div>
</body>
</html>