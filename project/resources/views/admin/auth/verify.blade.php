@extends('admin.auth.master', ['pageTitle' => 'Reset Login'])
@push('styles')
<link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="auth-wrapper d-flex no-block justify-content-center align-items-center" style="background: linear-gradient(rgba(0,0,0, 0.7), rgba(0, 0, 0, .8)),url({{ asset('assets/theme/assets/images/big/auth-bg.jpg') }}) ;background-position: center; background-repeat: no-repeat;background-size: cover;">
        <div class="auth-box">
            <div>
                <div class="logo">
                    <span class="db"><img src="{{ asset('assets/theme/assets/images/logo.png') }}" alt="logo" /></span>
                    <h5 class="font-medium m-b-20 mt-2">Recover Password</h5> 
					@if (session('error'))
                        <p class="text-danger">{{ session('error') }} </p>
                    @endif
                    @if (session('success'))
                        <p class="text-danger">{{ session('success') }} </p>
                    @endif
                </div>
                <div class="row m-t-20">
                    <div id="altbx"></div>
                    <!-- Form -->
                    <form class="col-12" action="{{ url('/forgot-password') }}" data-parsley-validate="" method="post" id="resetpassword">
                        <!-- email -->
                        @csrf
                        <div class="form-group row">
                            <div class="col-12">
                                <label>Enter your Email and instructions will be sent to you!</label>
                                <input class="form-control form-control-lg" id="userEmail" type="email" required="" data-parsley-required-message="Email is required" placeholder="Enter Email" name="useremail">
                            </div>
                            <span id="error" style="color:red"></span>
                        </div>
                        <!-- pwd -->
                        <div class="row m-t-20">
                            <div class="col-12">
                                <button class="btn btn-block btn-lg btn-info" type="submit" id="submit" name="submit">Reset</button>
                            </div>
                        </div>
                        <p class="mt-3 text-right"><a href="{{ url('/login') }}">Back to Login.</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();
        });
    </script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
@endpush
