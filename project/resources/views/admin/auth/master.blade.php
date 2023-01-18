<!DOCTYPE html>
<html lang="en" class="chrome windows fontawesome-i2svg-active fontawesome-i2svg-complete">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="baseurl" content="{{ url('/') }}">
  <title>{{ config('app.name') }}</title>
  <link rel="shortcut icon" href="{{asset('assets/theme/assets/images/logo.png')}}" type="image/x-icon">
 <link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
  @stack('styles')
</head>

<body>
  <div id="main-wrapper">
    <div class="preloader">
      <div class="lds-ripple">
        <div class="lds-pos"></div>
        <div class="lds-pos"></div>
      </div>
    </div>
    @yield('content')
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="{{ asset('assets/theme/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
  <script src="{{ asset('assets/theme/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
  @stack('scripts')
</body>

</html>
