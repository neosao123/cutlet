@extends('admin.layout.master', ['pageTitle'=>"Delivery Charges Slot"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/deliverySlots/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Delivery Charges Slot</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Delivery Charges Slot View </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">View Delivery Charges Slot </h5>
          </div>
		   <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('deliveryCharges/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if ($slot)
          <div class="row">
        <div class="col-sm-12 form-group">
				<label for="cityCode">City : <span style="color:red">*</span></label>
				<select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="cityCode" id="cityCode" readonly>
					<option value="">Select City</option>
                  @foreach ($city as $cityItem)
                    <option value="{{ $cityItem->code }}" {{ $cityItem->code == $slot->cityCode ? 'selected' : '' }}>{{ $cityItem->cityName }}</option>
                  @endforeach
                </select>
            </div>
			<div class="col-sm-12 form-group">
				<label for="fromKM">From KM : <b style="color:red">*</b></label>	
				<input type="text" id="fromKM" name="fromKM" class="form-control" readonly value="{{ $slot->fromKM }}">
			</div>
			<div class="col-sm-12 form-group">
				<label for="toKM">To KM : <b style="color:red">*</b></label>							
				<input type="text" id="toKM" name="toKM" class="form-control" readonly value="{{ $slot->toKM }}">
			</div>					
			<div class="col-sm-12 form-group">					
				<label for="deliveryCharges">Delivery charges : <b style="color:red">*</b></label>							
				<input type="text" id="deliveryCharges" name="deliveryCharges" class="form-control" readonly value="{{ $slot->deliveryCharges }}">       
			</div>		
            <div class="col-sm-12 form-group">
              <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" readonly id="isActive" name="isActive" value="1" {{ $slot->isActive == 1 ? 'checked' : '' }}>
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
@endpush
