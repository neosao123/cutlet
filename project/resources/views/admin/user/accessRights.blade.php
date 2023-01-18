@extends('admin.layout.master', ['pageTitle' => 'Users Edit'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/admin/users/index.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Users Edit</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">User Edit</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">
            </div>
        </div>
    </div>
    <div class="container-fluid col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h5 class="mb-0" data-anchor="data-anchor">User Edit</h5>
                    </div>
                    <div class="col-sm-2">
                        <a href="{{ url('users/list') }}" class="btn btn-outline-primary btn-sm"> Back </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                  <form class="needs-validation"  novalidate>
					<label><b>User Code : <?= $userCode;?></b></label>
					<input type="hidden" id="userCode" name="userCode" value="<?= $userCode;?>">
					<div id="userAccessTable"></div>
					
					<hr/>
					
					 <div class="text-xs-right">
                        <button type="button" id="btnSubmit" class="btn btn-success" onclick="page_isPostBack=true;">Submit</button>
                        <button type="reset" class="btn btn-reset">Back</button>
					</div>
					
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/init_site/admin/users/accessRights.js') }}"></script>
	@if(Session::has('status'))
		  <script type="text/javascript">
          notification = @json(session()->pull("status"));
		  function message() {
		  Swal.fire({
			  icon: 'success',
			  text: notification.message, 
			});
		  }
		  window.onload = message;
		  @php 
			  session()->forget('status'); 
		   @endphp
		 </script>
    @endif
    @if (session('error'))
        <script>
            $(document).ready(function() {
                'use strict';
                setTimeout(() => {
                    $(".alert").remove();
                }, 5000);
            });
        </script>
    @endif
	
@endpush