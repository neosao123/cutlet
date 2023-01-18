@extends('admin.layout.master', ['pageTitle'=>"Address Master"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/address/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Address</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Address View </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">View Address </h5>
          </div>
		   <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('address/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if ($address)
          <div class="row">
        <div class="col-sm-12 form-group">
				<label for="cityCode">City : <span style="color:red">*</span></label>
				<select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="cityCode" id="cityCode" readonly>
					<option value="">Select City</option>
                  @foreach ($city as $cityItem)
                    <option value="{{ $cityItem->code }}" {{ $cityItem->code == $address->cityCode ? 'selected' : '' }}>{{ $cityItem->cityName }}</option>
                  @endforeach
                </select>
            </div>
			<div class="col-sm-12 form-group">
                <label for="place">Place : <b style="color:red">*</b></label>					
                <input type="text" id="place" name="place" class="form-control" readonly value="{{ $address->place }}">
            </div>
			<div class="col-sm-12 form-group">
                <label for="taluka">Taluka : <b style="color:red">*</b></label>							
                <input type="text" id="taluka" name="taluka" class="form-control" readonly value="{{ $address->taluka }}">
            </div>					
			<div class="col-sm-12 form-group">					
                <label for="district">District : <b style="color:red">*</b> </label>							
                <input type="text" id="district" name="district" class="form-control" readonly value="{{ $address->district }}">                 
            </div>	
			<div class="col-sm-12 form-group">					
                 <label for="pincode">Pincode : <b style="color:red">*</b></label>							
                <input type="text" id="pincode" name="pincode" class="form-control" readonly value="{{ $address->pincode }}">                
            </div>		
			<div class="col-sm-12 form-group">					
                <label for="state">State : <b style="color:red">*</b></label>                            
                <input type="text" id="state" name="state" class="form-control" readonly value="{{ $address->state }}" data-parsley-required-message="State is required">                
            </div>		
            <div class="col-sm-12 form-group">	
				<div id="myMap">
				</div>
			</div>
			<div class="col-sm-4 form-group">					
               <label for="latitude">Latitude : <b style="color:red">*</b></label>                            
                <input type="number" step="any" id="latitude" name="latitude"  class="form-control" readonly value="{{ $address->latitude }}" data-parsley-required-message="Latitude is required">               
            </div>
			<div class="col-sm-4 form-group">					
               <label for="longitude">Longitude : <b style="color:red">*</b></label>                            
                <input type="number" step="any" id="longitude" name="longitude"  class="form-control" readonly value="{{ $address->longitude }}" data-parsley-required-message="Longitude is required">          
            </div>
			<div class="col-sm-4 form-group">					
               <label for="radius">Radius : <b style="color:red">*</b></label>                            
               <input type="number" step="any" id="radius" name="radius" class="form-control" readonly value="{{ $address->radius }}" data-parsley-required-message="Radius is required">				              
            </div>
            <div class="col-sm-12 form-group">
				  <div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input" id="isService" name="isService" value="1" {{ $address->isService == 1 ? 'checked' : '' }}>
					<label class="custom-control-label" for="isService">Service Available For address?</label>
				  </div>
            </div>
            <div class="col-sm-12 form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1" {{ $address->isActive == 1 ? 'checked' : '' }}>
                <label class="custom-control-label" for="isActive">Active</label>
              </div>
            </div>
          </div>
        @endif
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/address/edit.js') }}"></script>
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
