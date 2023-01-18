@extends('admin.layout.master', ['pageTitle'=>"Edit Slot"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/deliverySlots/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Slot</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Slot Update </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Update Slot</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('admin/Slot/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        @if ($slot)
          <form action="{{ url('deliveryCharges/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
            @csrf
            <input type="hidden" name="code" value="{{ $slot->code }}" readonly>
            <div class="row">
               <div class="col-sm-12 form-group">
				<label for="cityCode">City : <span style="color:red">*</span></label>
				<select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="cityCode" id="cityCode" required data-parsley-required-message="City is required">
					<option value="">Select City</option>
                  @foreach ($city as $cityItem)
                    <option value="{{ $cityItem->code }}" {{ $cityItem->code == $slot->cityCode ? 'selected' : '' }}>{{ $cityItem->cityName }}</option>
                  @endforeach
                </select>
                <span class="text-danger">
					{{ $errors->first('cityCode') }}
                </span>
            </div>
			<div class="col-sm-12 form-group">
				<label for="fromKM">From KM : <b style="color:red">*</b></label>	
				<input type="number" id="fromKM" name="fromKM" class="form-control" onchange="validateOverlappingSlots();validateToKm()" required value="{{ $slot->fromKM }}" data-parsley-required-message="From KM is required">
				 <span class="text-danger">
					{{ $errors->first('fromKM') }}
                </span>
			</div>
			<div class="col-sm-12 form-group">
				<label for="toKM">To KM : <b style="color:red">*</b></label>							
				<input type="number" id="toKM" name="toKM" class="form-control" onchange="validateToKm()" required value="{{ $slot->toKM }}" data-parsley-required-message="To KM is required">
				<span class="text-danger">
					{{ $errors->first('toKM') }}
                </span>
			</div>					
			<div class="col-sm-12 form-group">					
				<label for="deliveryCharges">Delivery charges : <b style="color:red">*</b></label>							
				<input type="number" id="deliveryCharges" name="deliveryCharges" class="form-control" required value="{{ $slot->deliveryCharges }}" data-parsley-required-message="Delivery Charges is required">       
				 <span class="text-danger">
					{{ $errors->first('deliveryCharges') }}
                </span>
			</div>		
            <div class="col-sm-12 form-group">
                <div class="custom-control custom-checkbox">
                  <input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1" {{ $slot->isActive == 1 ? 'checked' : '' }}>
                  <label class="custom-control-label" for="isActive">Active</label>
                </div>
            </div>
              <div class="col-sm-12 form-group">
                <button class="btn btn-success"> Submit </button>
              </div>
            </div>
          </form>
        @endif
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
