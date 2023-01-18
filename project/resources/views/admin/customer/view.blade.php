@extends('admin.layout.master', ['pageTitle'=>"Customer Master"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/customer/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Customer</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Customer View </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">View Customer </h5>
          </div>
		   <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('customer/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if ($customer)
			
          <div class="form-row">
			<div class="col-md-6 mb-3">
				<label><b> Code:</b> </label>
				<input type="text" value="{{ $customer->code}}" class="form-control-line" id="clientCode" readonly>
			</div>
			<div class="col-md-6 mb-3">
				<label><b> Client Name:</b> </label>
				<input type="text" value="{{ $customer->name}}" class="form-control-line"  readonly>
			</div>	
			<div class="col-md-6 mb-3">
				<label><b> Mobile Number:</b></label>
				<input type="text" value="{{ $customer->mobile}}" class="form-control-line"  readonly>
			</div>
			<div class="col-md-6 mb-3">
				<label><b>Email ID:</b> </label>
				<input type="text" value="{{ $customer->emailId}}" class="form-control-line"  readonly>
			</div>
		</div>	
		<h4>Profile Details</h4>
		<hr>
		<div class="form-row">
			@if($clientprofile)
				@foreach($clientprofile as $cp)
				<div class="col-md-3 mb-3"><label><b> Local:</b> </label>
					<input type="text" value="{{ $cp->local }}" class="form-control-line"  readonly>
				</div>
				<div class="col-md-3 mb-3">
					<label><b> Flat :</b> </label>
					<input type="text" value="{{ $cp->flat }}" class="form-control-line"  readonly>
				</div>
				<div class="col-md-4 mb-3"><label><b> Landmark :</b> </label>
					<input type="text" value="{{ $cp->landMark}}" class="form-control-line"  readonly>
				</div>
				<div class="col-md-2 mb-3"><label> <b>City :</b> </label>
					<input type="text" class="form-control-line" value="{{ $cp->city}}"  readonly>
				</div> 
				<div class="col-md-3 mb-3"><label> <b>Pincode  :</b> </label>
					<input type="text" class="form-control-line" value="{{ $cp->pincode}}"  readonly>				
				</div> 
				<div class="col-md-3 mb-3"><label><b> State: </b></label>
					<input type="text" class="form-control-line rpadding" value="{{ $cp->state }}"  readonly>
				</div> 
			@endforeach
				<div class="col-md-2 mb-3">
					<label><b> Status: </b></label>
					<div class="form-group">
					@if($customer->isActive == "1")
						<span class="label label-sm label-success">Active</span>
					@else
						<span class="label label-sm label-warning">Inactive</span>
					@endif
					</div>
				</div>					     
        @endif
		</div>
		@endif
      </div>
    </div>
  </div>
@endsection
@push('scripts')
@endpush
