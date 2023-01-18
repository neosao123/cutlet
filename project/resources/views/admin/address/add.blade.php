@extends('admin.layout.master', ['pageTitle'=>"Add Address"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/address/index.css') }}" rel="stylesheet">
@endpush
@section('content')
  @php
  $userImage = asset('assets/images/avatar.png');
  @endphp
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Address</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Address Add</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-10">
            <h5 class="mb-0" data-anchor="data-anchor">New Address</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('address/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form action="{{ url('address/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
          @csrf
          <div class="row">
            <div class="col-sm-12 form-group">
				<label for="cityCode">City : <span style="color:red">*</span></label>
				<select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="cityCode" id="cityCode" required data-parsley-required-message="City is required">
					<option value="">Select City</option>
                  @foreach ($city as $cityItem)
                    <option value="{{ $cityItem->code }}">{{ $cityItem->cityName }}</option>
                  @endforeach
                </select>
                <span class="text-danger">
					{{ $errors->first('cityCode') }}
                </span>
            </div>
			<div class="col-sm-12 form-group">
                <label for="place">Place : <b style="color:red">*</b></label>					
                <input type="text" id="place" name="place" class="form-control" required data-parsley-required-message="Place is required">
                 <span class="text-danger">
					{{ $errors->first('place') }}
                </span>
            </div>
			<div class="col-sm-12 form-group">
                <label for="taluka">Taluka : <b style="color:red">*</b></label>							
                <input type="text" id="taluka" name="taluka" class="form-control" required data-parsley-required-message="Taluka is required">
				 <span class="text-danger">
					{{ $errors->first('taluka') }}
                </span>
            </div>					
			<div class="col-sm-12 form-group">					
                <label for="district">District : <b style="color:red">*</b> </label>							
                <input type="text" id="district" name="district" class="form-control" required data-parsley-required-message="District is required">
				<span class="text-danger">
					{{ $errors->first('taluka') }}
                </span>                 
            </div>	
			<div class="col-sm-12 form-group">					
                 <label for="pincode">Pincode : <b style="color:red">*</b></label>							
                <input type="text" id="pincode" name="pincode" class="form-control" required data-parsley-required-message="Pincode is required">
				<span class="text-danger">
					{{ $errors->first('pincode') }}
                </span>                 
            </div>		
			<div class="col-sm-12 form-group">					
                <label for="state">State : <b style="color:red">*</b></label>                            
                <input type="text" id="state" name="state" class="form-control" required data-parsley-required-message="State is required">
				<span class="text-danger">
					{{ $errors->first('state') }}
                </span>                 
            </div>		
            <div class="col-sm-12 form-group">	
				<div id="myMap">
				</div>
			</div>
			<div class="col-sm-4 form-group">					
               <label for="latitude">Latitude : <b style="color:red">*</b></label>                            
                <input type="number" step="any" id="latitude" name="latitude"  class="form-control" required data-parsley-required-message="Latitude is required">
				<span class="text-danger">
					{{ $errors->first('latitude') }}
                </span>                 
            </div>
			<div class="col-sm-4 form-group">					
               <label for="longitude">Longitude : <b style="color:red">*</b></label>                            
                <input type="number" step="any" id="longitude" name="longitude"  class="form-control" required data-parsley-required-message="Longitude is required">
				<span class="text-danger">
					{{ $errors->first('longitude') }}
                </span>                 
            </div>
			<div class="col-sm-4 form-group">					
               <label for="radius">Radius : <b style="color:red">*</b></label>                            
               <input type="number" step="any" id="radius" name="radius" class="form-control" required data-parsley-required-message="Radius is required">				
				<span class="text-danger">
					{{ $errors->first('radius') }}
                </span>                 
            </div>
			 <div class="col-sm-12 form-group">
				  <div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input" id="isService" name="isService" value="1">
					<label class="custom-control-label" for="isService">Service Available For address?</label>
				  </div>
            </div>
            <div class="col-sm-12 form-group">
				  <div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1">
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

<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key={{ Config::get('constants.PLACE_API_KEY');}}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/address/add.js') }}"></script>
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
