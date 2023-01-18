<!DOCTYPE html>
<html lang="en" class="chrome windows fontawesome-i2svg-active fontawesome-i2svg-complete">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="baseurl" content="{{ url('/') }}">
	<meta name="csrf_token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{asset('assets/theme/assets/images/logo.png')}}" type="image/x-icon">
    <link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
	 <link href="{{ asset('assets/theme/dist/css/add.css') }}" rel="stylesheet">
	  <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
    <style>
        body .p-15 {
            padding: 15px;
        }

        body .p-l-30 {
            padding-left: 30px;
        }

        body .p-10 {
            padding: 10px;
        }

        .logo-icon img {
            border-radius: 10px;
        }

        .logo-text span {
            font-weight: bold;
            font-size: 24px;
        }
		.collapse.first-level {
			background-image: linear-gradient(to bottom left,#fb8c00 , #d05012)!important;
			color: #fff;
		}

    </style>
    @stack('styles')
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>
    <div id="main-wrapper">
        @include('restaurant.layout.topnav')
        @include('restaurant.layout.sidebar')
        <div class="page-wrapper">
            @yield('content')
            @include('restaurant.layout.footer')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/theme/assets/libs/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/theme/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/theme/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/theme/dist/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/theme/dist/js/app.init.js') }}"></script>
    <script src="{{ asset('assets/theme/dist/js/app-style-switcher.js') }}"></script>
    <script src="{{ asset('assets/theme/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
    <script src="{{ asset('assets/theme/assets/extra-libs/sparkline/sparkline.js') }}"></script>
    <script src="{{ asset('assets/theme/dist/js/waves.js') }}"></script>
    <script src="{{ asset('assets/theme/dist/js/sidebarmenu.js') }}"></script>
    <script src="{{ asset('assets/theme/dist/js/custom.min.j') }}s"></script>
    <script src="{{ asset('assets/init_site/frontend/masterlayout/index.js') }}"></script>
	<script src="{{ asset('assets/init_site/restaurant/dashboard/index.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
