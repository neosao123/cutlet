@extends('admin.layout.master', ['pageTitle' => 'Users Add'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/admin/users/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Users Add</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Users Add</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">
            </div>
        </div>
    </div>
    <div class="container-fluid col-md-6">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h5 class="mb-0" data-anchor="data-anchor">User Add</h5>
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
                <form action="{{ url('users/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                    @csrf
                    <div class="row">
					     <div class="col-sm-12 form-group">
                            <label for="username">Users Name</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" class="form-control">
                           
                        </div>
                        <div class="col-sm-12 form-group">
                            <label for="fullname">Full Name<span style="color:red">*</span></label>
                            <input type="text" id="fullname" name="fullname" value="{{ old('fullname') }}" class="form-control" required data-parsley-required-message="Full Name is required" >
                            <span class="text-danger">
                                {{ $errors->first('fullname') }}
                            </span>
                        </div>
						<div class="col-sm-12 form-group">
                            <label for="email">Email<span style="color:red">*</span></label>
                            <input type="email" id="email" name="email"  value="{{ old('email') }}" class="form-control" required data-parsley-required-message="Email is required">
                            <span class="text-danger">
                                {{ $errors->first('email') }}
                            </span>
                        </div>
						<div class="col-sm-12 form-group">
                            <label for="mobilenumber">Mobile Number<span style="color:red">*</span></label>
                            <input type="text" id="mobilenumber" name="mobilenumber" value="{{ old('mobilenumber') }}" class="form-control" required data-parsley-required-message="Mobile number is required" >
                            <span class="text-danger">
                                {{ $errors->first('mobilenumber') }}
                            </span>
                        </div>
						<div class="col-sm-12 form-group">
                            <label for="role">Role <span style="color:red">*</span></label>
                            <select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="role" required data-parsley-required-message="Role is required">
                                <option value="">Select Role</option>
                                <option value="USER" {{ old('role') == 'USER' ? 'selected' : '' }}>User</option>
								 <option value="DBOY" {{ old('role') == 'DBOY' ? 'selected' : '' }}>Delivery Boy</option>
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('role') }}
                            </span>
                        </div>
                        <div class="col-sm-12 form-group">
                            <label for="designation">Designation <span style="color:red">*</span></label>
                            <select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="designation" required data-parsley-required-message="Designation is required">
                                <option value="">Select</option>
                                @foreach ($designation as $desi)
                                    <option value="{{ $desi->code }}" @if(old('designation') == $desi->code) selected @endif>{{ $desi->designation }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('designation') }}
                            </span>
                        </div>
                        <div class="col-sm-12 form-group">
                            <label for="city">City <span style="color:red">*</span></label>
                            <select class="form-control" style="width: 100%; height:36px;" name="city" id="city" required data-parsley-required-message="City is required">
                                <option value="">Select</option>
                                @foreach ($city as $cityItem)
                                    <option value="{{ $cityItem->code }}" @if(old('city') == $cityItem->code) selected @endif>{{ $cityItem->cityName }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('city') }}
                            </span>
                        </div>
						<div class="col-12">
						  <div class="mb-3">
							  <label class="form-label"><span style="color:red">*</span> Password</label>
							  <input class="form-control" name="password" type="password"  id="password" required="" data-parsley-required-message="Password is required"/>
							   <span class="text-danger text-center">{{ $errors->first('password') }}</span>
						  </div> 
					  </div>
					  <div class="col-12">
						  <div class="mb-3">
							  <label class="form-label"><span style="color:red">*</span> Confirm Password</label>
							  <input class="form-control" name="password_confirmation" type="password" id="password_confirmation"  required="" data-parsley-required-message="Confirm Password is required"/>
							  <span class="text-danger text-center">{{ $errors->first('password') }}</span> 
						 </div>
					 </div>
					  <div class="col-sm-6">
					      <div class="mb-3">
						       <label class="form-label" for="form-wizard-progress-wizard-profilephoto">Profile Photo</label>
					          <input type="file" id="file" class="form-control " name="profilephoto" accept=".jpg, .jpeg, .png">
					      </div>
					  </div>
					<div class="col-sm-12 form-group">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1" checked="">
							<label class="custom-control-label" for="isActive">Active</label>
						</div>
					</div>
                        <div class="col-sm-12 form-group">
                            <button class="btn btn-success"> Submit </button>
                            <button type="reset" class="btn btn-danger">Reset</button>
                        </div>
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
    <script type="text/javascript" src="{{ asset('assets/init_site/admin/users/add.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
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