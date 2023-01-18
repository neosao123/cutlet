@extends('admin.layout.master', ['pageTitle'=>"Profile Update"])
@push('styles')
 <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
 <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
 <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Home</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Edit Profile</a></li>
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
          <div class="col-sm-10">Profile Edit</h5>
          </div>
        </div>
      </div>
        <div class="card-body pt-3 pb-2">
          <form id="profileform" method="post" action="{{ url('profile-update/'.$details->code)}}" enctype="multipart/form-data" data-parsley-validate="">
            @csrf
				<input type="hidden" value="{{ $details->code}}" name="code">
				 <div class="row g-2">
				
				   <div class="col-12">
					 <div class="mb-3">
						  <label class="form-label" for="form-wizard-progress-wizard-name">Name : <b style="color:red">*</b></label>
						  <input class="form-control" type="text" name="name" placeholder="Enter Name" id="form-wizard-progress-wizard-name" value="{{ $details->name}}" onkeypress="return ValidateAlpha(event)" required="" data-parsley-required-message="Name is required"/>
						  <span class="text-danger text-center">{{ $errors->first('name') }}</span>
					 </div>
				    </div>
				     <div class="col-12">
					  <div class="mb-3">
						  <label class="form-label" for="form-wizard-progress-wizard-email">Email : <b style="color:red">*</b></label>
						  <input class="form-control" type="email" name="userEmail" placeholder="Enter Email" id="form-wizard-progress-wizard-email" value="{{ $details->userEmail}}" data-parsley-type="email" required="" data-parsley-required-message="Email id is required"/>
						   <span class="text-danger text-center">{{ $errors->first('userEmail') }}</span>
					 </div>
					</div>
					 <div class="col-12">
						  <div class="mb-3">
							  <label class="form-label" for="form-wizard-progress-wizard-password">Password</label>
							  <input class="form-control" name="password" type="password" autocomplete="on" id="password" placeholder="Password" />
							   <span class="text-danger text-center">{{ $errors->first('password') }}</span>
						  </div>
					  </div>
					  <div class="col-12">
						  <div class="mb-3">
							  <label class="form-label" for="form-wizard-progress-wizard-cpassword">Confirm Password</label>
							  <input class="form-control" name="password_confirmation" type="password" autocomplete="on" id="password_confirmation" placeholder="Confirm Password" />
							  <span class="text-danger text-center">{{ $errors->first('password') }}</span> 
						 </div>
					 </div>
				      <div class="col-sm-6">
					      <div class="mb-3">
						       <label class="form-label" for="form-wizard-progress-wizard-profilephoto">Profile Photo</label>
					          <input type="file" id="file" class="form-control " name="profilephoto" accept=".jpg, .jpeg, .png">
					      </div>
					  </div>
					  @if(!empty($details->profilePhoto))
						<div class="col-sm-2">
						    <img class="img-radius" id="profile_image" src="{{ url('assets/images/profileimages/'.$details->profilePhoto)}}" height="80" width="80" accept=".jpg,.png,.jpeg"/>
						</div>
						@endif
				  </div>
				<div class="mt-5 mb-5 text-center">
				  <button type="submit" class="btn btn-primary btnsubmit">Update Profile</button>
				</div>
				
          </form>
        </div>
      </div>
    </div>
  </div>
  </div>
 @endsection
@push('scripts')
<script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
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

@endpush