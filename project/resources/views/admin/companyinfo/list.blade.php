@extends('admin.layout.master', ['pageTitle' => 'Company Details'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/admin/restaurant/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Company Details</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Company Details</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
	
	 <div class="container-fluid col-md-10">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h5 class="mb-0" data-anchor="data-anchor">Company Details</h5>
                    </div>
                </div>
            </div>
			 @if (session('success'))
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            {{ session('success') }}
          </div>
        @endif
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
               <form action="{{ url('companyinfo/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
			   @csrf
					
				@if($companyinfo)
					@foreach($companyinfo as  $row)
					 <input type="hidden" id="code" name="code" value="{{ $row->code }}" class="form-control" >
					 
					<div class="form-row">
					    <div class="col-md-6">
						     <label for="companyName">Company Name</label>
							<input type="text" id="companyName" name="companyName" readonly class="form-control" value="{{ $row->companyName }}">
						</div>
						<div class="col-md-6">
						     <label for="companyRegNo">Company Register No</label>
							  <input type="text" id="companyRegNo" name="companyRegNo" readonly class="form-control" value="{{ $row->companyRegNo }}">
						</div>
					</div><hr>
					
						<div class="form-row">
					    <div class="col-md-4">
						     <label for="contactNo">Contact No</label>
							<input type="number" id="contactNo" name="contactNo" readonly class="form-control" value="{{$row->contactNo}}">
						</div>
						<div class="col-md-4">
						     <label for="altContactNo">Alt Contact No</label>
							<input type="number" id="altContactNo" name="altContactNo" readonly  class="form-control" value="{{ $row->alternateContactNo}}">
						</div>
						<div class="col-md-4">
						     <label for="email">Email</label>
							<input type="email" id="email" name="email" readonly class="form-control" value="{{ $row->email }}">
						</div>
					</div></br></br>
					<hr>
					<h4 class="card-title">Shipping Address:</h4> 
						<div class="form-row">
					
					    <div class="col-md-8">
						     <label for="shippingAddress">Address :</label>
							<input type="text" id="shippingAddress" name="shippingAddress" readonly class="form-control" value="{{ $row->shippingAddress }}">						
						</div>
						<div class="col-md-4">
						     <label for="shippingPinCode">Pincode :</label>
							<input type="text" id="shippingPinCode" name="shippingPinCode" readonly class="form-control" value="{{ $row->shippingPinCode }}">
							
						</div>
						
					</div>
					<div class="form-row">
					  <div class="col-md-2">
						    <label for="shippingPlace">Place :</label>
							<input type="text" id="shippingPlace" name="shippingPlace" readonly  class="form-control" value="{{ $row->shippingPlace }}">
						</div>
						<div class="col-md-2">
						     <label for="shippingTaluka">Taluka :</label>
							<input type="text" id="shippingTaluka" name="shippingTaluka" readonly class="form-control" value="{{ $row->shippingTaluka }}">
						</div>
						<div class="col-md-2">
						     <label for="shippingDistrict">District :</label>
							<input type="text" id="shippingDistrict" name="shippingDistrict" readonly class="form-control" value="{{ $row->shippingDistrict }}">
						</div>
						<div class="col-md-3">
						     <label for="shippingState">State :</label>
							<input type="text" id="shippingState" name="shippingState" readonly class="form-control" value="{{ $row->shippingState }}">
						</div>
						<div class="col-md-3">
						     <label for="shippingCountry">Country :</label>
							<input type="text" id="shippingCountry" name="shippingCountry" readonly class="form-control" value="{{ $row->shippingCountry }}">
						</div>
						
					</div>
					</br></br>
					
					<div class="row">
					<div class="col-md-5"><h4 class="card-title">Billing Address:</h4></div>
					<div class="col-md-7">
						<div class="custom-control custom-checkbox">
                        @if($row->xyz == '1')
                            <input type="checkbox" class="custom-control-input" disabled name="isBillingAddressSame" id="isBillingAddressSame" value="1" checked>
                        @else
							<input type="checkbox" class="custom-control-input" disabled	 name="isBillingAddressSame" id="isBillingAddressSame" value="1">
                        @endif
                                        <label class="custom-control-label" for="isBillingAddressSame">Check if Billing Address is same to Current address</label>
                                      </div>
						</div>
					</div>
					<div class="form-row">
					
					    <div class="col-md-8">
						     <label for="billingAddress">Address :</label>
							<input type="text" id="billingAddress" name="billingAddress" readonly class="form-control" value="{{ $row->billingAddress }}">
						</div>
						<div class="col-md-4">
						     <label for="billingPinCode">Pincode :</label>
							<input type="text" id="billingPinCode" name="billingPinCode" readonly  class="form-control" value="{{ $row->billingPinCode }}">
						
						</div>
						
					</div>
					<div class="form-row">
					  <div class="col-md-2">
						     <label for="billingPlace">Place :</label>
							<input type="text" id="billingPlace" name="billingPlace" readonly class="form-control" value="{{ $row->billingPlace }}">
						</div>
						<div class="col-md-2">
						     <label for="shippingTaluka">Taluka :</label>
							<input type="text" id="billingTaluka" name="billingTaluka" readonly class="form-control" value="{{ $row->billingTaluka }}">
						</div>
						<div class="col-md-2">
						     <label for="shippingDistrict">District :</label>
							<input type="text" id="billingDistrict" name="billingDistrict" readonly class="form-control" value="{{ $row->billingDistrict }}">
						</div>
						<div class="col-md-3">
						     <label for="shippingState">State :</label>
							<input type="text" id="billingState" name="billingState" readonly class="form-control" value="{{ $row->billingState }}">
							
						</div>
						<div class="col-md-3">
						     <label for="shippingCountry">Country :</label>
							<input type="text" id="billingCountry" name="billingCountry" readonly class="form-control" value="{{ $row->billingCountry}}">
							
						</div>
						
					</div>
				@endforeach					
				<div class="text-xs-right mt-2">
					<a type="button" class="btn btn-success" href="{{ url('companyinfo/edit')}}">Edit</a>
				</div>
				</form>
				@endif
            </div>
        </div>
    </div>
	
	
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
	 <script type="text/javascript" src="{{ asset('assets/init_site/admin/companyinfo/index.js') }}"></script>
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