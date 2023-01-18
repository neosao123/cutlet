@extends('admin.layout.master', ['pageTitle'=>"Add Delivery Charges Slot"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/deliverySlots/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  @php
  $userImage = asset('assets/images/avatar.png');
  @endphp
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Delivery Charges Slot</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Delivery Charges Slot Add</a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">New Delivery Charges Slot</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('deliveryCharges/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form action="{{ url('deliveryCharges/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
          @csrf
          <div class="row">
			<div class="col-sm-12 form-group">
				<label for="cityCode">City : <span style="color:red">*</span></label>
				<select class="select2 form-control custom-select" style="width: 100%; height:36px;" onchange="validateOverlappingSlots();" name="cityCode" id="cityCode" required data-parsley-required-message="City is required">
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
				<label for="fromKM">From KM : <b style="color:red">*</b></label>	
				<input type="hidden" class="form-control" id="code" name="code" value="">
				<input type="number" id="fromKM" name="fromKM" class="form-control" onchange="validateOverlappingSlots();validateToKm()" required data-parsley-required-message="City is required">
				 <span class="text-danger">
					{{ $errors->first('fromKM') }}
                </span>
			</div>
			<div class="col-sm-12 form-group">
				<label for="toKM">To KM : <b style="color:red">*</b></label>							
				<input type="number" id="toKM" name="toKM" class="form-control"  onchange="validateToKm()" required data-parsley-required-message="From KM is required">
				 <span class="text-danger">
					{{ $errors->first('toKM') }}
                </span>
			</div>					
			<div class="col-sm-12 form-group">					
				<label for="deliveryCharges">Delivery charges : <b style="color:red">*</b></label>							
				<input type="number" id="deliveryCharges" name="deliveryCharges" class="form-control" required data-parsley-required-message="Delivery Charges is required">       
				 <span class="text-danger">
					{{ $errors->first('deliveryCharges') }}
                </span>
			</div>		
			<div class="col-sm-12 form-group">
				  <div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1">
					<label class="custom-control-label" for="isActive">Active</label>
				  </div>
			</div>	
			<div class="col-sm-12 form-group">
			  <button type="submit" class="btn btn-success" id="submit"> Submit </button>
			   <button type="reset" class="btn btn-danger">Reset</button>
			</div>
		</div>

        </form>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/deliverySlots/index.js') }}"></script>
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
