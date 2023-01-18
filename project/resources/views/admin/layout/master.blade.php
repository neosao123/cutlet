<!DOCTYPE html>
<html lang="en" class="chrome windows fontawesome-i2svg-active fontawesome-i2svg-complete">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="baseurl" content="{{ url('/') }}">
    <meta name="csrf_token" content="{{ csrf_token() }}" />
    <title>{{ config('app.name') }}</title>
    <link rel="shortcut icon" href="{{ asset('assets/theme/assets/images/logo.png') }}" type="image/x-icon">
    <link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/dist/css/add.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/frontend/masterlayout/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
    <script src="https://maps.googleapis.com/maps/api/js?key={{ Config::get('constants.PLACE_API_KEY') }}&v=weekly" defer></script>
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
        @include('admin.layout.topnav')
        @include('admin.layout.sidebar_rolewise')
        <div class="page-wrapper">
            @yield('content')
            @include('admin.layout.footer')
        </div>
        <div id="maintenance-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
            <div class="modal-dialog model-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Maintenance Mode</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                    </div>
                    <div class="modal-body maintenancemodal-body">
                        <p>Maintenance Mode - <b>ON</b></p>
                        <span>When Maintenance Mode is kept "On" - all of your application users shall not be able to access the application unless you turn off the maintenace!</span>
                        <form>
                            <div class="form-row">
                                <div class="col-sm-12 mb-2">
                                    <label for="messageTitle">Message Title</label>
                                    <input class="form-control" id="messageTitle" maxlength="150" required>
                                </div>
                                <div class="col-sm-12 mb-2">
                                    <label for="messageDescription">Message Description</label>
                                    <textarea class="form-control" id="messageDescription" maxlength="255" required></textarea>
                                </div>
                                <div class="col-sm-12 mb-2">
                                    <button class="btn btn-success" type="button" id="updateMaintenance">Submit</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger waves-effect" data-dismiss="modal" id="close">Close</button>
                    </div>
                </div>
            </div>
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
    <script src="{{ asset('assets/theme/dist/js/custom.min.js') }}"></script>
    <script src="{{ asset('assets/init_site/frontend/masterlayout/index.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
