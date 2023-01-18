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
                    <h5 class="font-medium m-b-20 mt-2">Reset Password</h5>
					 @if (session('message'))
                        <p class="text-danger">{{ session('message') }} </p>
                    @endif
                </div>
                <div class="row m-t-20">
                    <div id="altbx" class="text-center text-danger"></div>
                    <!-- Form -->
                       <form class="col-12" action="{{ url('/recover-password') }}" data-parsley-validate="" method="post" id="resetpassword">
                        <!-- email -->
                        @csrf
                        <div class="form-group row">
                            <div class="col-12">
                                <label>New Password</label>
                                <input type="hidden" name="code" value="{{ $result->code }}" readonly class="form-control">
                                <input type="hidden" name="token" value="{{ $result->resetToken }}" readonly class="form-control">
                                <input type="text" name="password" id="password" class="form-control" type="password" required="" data-parsley-required-message="Password is required">
                                <span class="text-center">{{ $errors->first('password') }}</span>
                            </div>
                            <div class="col-12">
                                <label>Confirm Password</label>
                                <input type="text" name="password_confirmation" id="password_confirmation" class="form-control" type="password" required="" data-parsley-required-message="Confirm Password is required">
                                <span class="text-center">{{ $errors->first('password') }}</span>
                            </div>
                        </div>
                        <!-- pwd -->
                        <div class="row m-t-20">
                            <div class="col-12">
                                <button class="btn btn-block btn-lg btn-info" type="submit" id="submit" name="submit">Reset Password</button>
                            </div>
                        </div>
                        <p class="f-w-600 text-right"><a href="{{ url('/login') }}">Back to Login.</a></p>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".preloader").fadeOut();

            $("#password_confirmation").on("change", function() {
                var cpassword = $(this).val();
                var password = $('#password').val();
                if (cpassword != password) {
                    $("#altbx").text("Password does not match.");
                    setTimeout(() => {
                        $("#altbx").empty();
                    }, 5000);
                    $('#submit').prop("disabled", true);
                    return false;
                } else {
                    $('#submit').prop("disabled", false);
                }
            });

            $("#password").on("change", function() {
                var cpassword = $(this).val();
                var password = $('#password_confirmation').val();
                if (cpassword != password && password != '') {
                    $("#altbx").text("Password does not match.");
                    setTimeout(() => {
                        $("#altbx").empty();
                    }, 5000);
                    $('#submit').prop("disabled", true);
                    return false;
                } else {
                    $('#submit').prop("disabled", false);
                }
            });

        });
    </script>
@endpush
