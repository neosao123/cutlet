@extends('admin.layout.master', ['pageTitle'=>"Restaurant Order List Report"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/other/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
  <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Restaurant Order</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Restaurant Orders List</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-lg-12">
        @if (session('success'))
          <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            {{ session('success') }}
          </div>
        @endif
              <div class="card">
			   <div class="card-header">
					<div class="row">
					  <div class="col-sm-6">
						<h5 class="mb-0" data-anchor="data-anchor">Filter</h5>
					  </div>
					</div>
			   </div>
			  <div class="card-body">
				<div class="row">
					<div class="col-sm-3 form-group">
						<label>Restaurant:</label>
						<div class="form-group">
						  <select class="select2 form-control"  style="width: 100%;" name="restaurantCode" id="restaurantCode">
							<option value="">Select</option>
							@if($restaurant)
								@foreach($restaurant as $res)
									<option value="{{$res->code}}">{{ $res->entityName}}</option>
								@endforeach
							@endif							
							</select>
						</div>
				   </div>
				   <div class="col-sm-3 form-group">
						<label>Order Code:</label>
						<div class="form-group">
						   <select class="select2 form-control"  style="width: 100%;" name="orderCode" id="orderCode">
							<option value="">Select</option>
							@if($ordermaster)
								@foreach($ordermaster as $od)
									<option value="{{ $od->code }}">{{ $od->code}}</option>
								@endforeach
							@endif
							</select>
						</div>
				   </div>
				   
					<div class="col-sm-3 form-group">
						<label>Order Status:</label>
						<div class="form-group">
						   <select class="select2 form-control"  style="width: 100%;" name="status" id="status">
								 <option value="">Select</option>
								 @if($statusmaster)
									@foreach ($statusmaster as $status)
										<option value="{{ $status->statusSName}}">{{ $status->statusName }}</option>
									@endforeach
								@endif
						   </select>
						</div>
				   </div>
				    <div class="col-sm-3 form-group">
						<label>Customer:</label>
						<div class="form-group">
						   <select class="select2 form-control"  style="width: 100%;" name="customerCode" id="customerCode">
								 <option value="">Select</option>
								@if($customer)
									@foreach ($customer as $cu) 
										<option value="{{ $cu->code }}">{{ $cu->name }}</option>
									@endforeach
								@endif
						   </select>
						</div>
				   </div>
				    @php
					$todayDate = date('d-m-Y');
					$previousDate = date('d-m-Y', strtotime(' - 7 days'));
					@endphp
				   <div class="col-sm-5 form-group">
					   <div class="input-daterange input-group">
						<span> <label> Search Dates :</label> </span>
							<div class="input-daterange input-group" id="productDateRange">
								<input type="text" class="form-control date-inputmask col-sm-5" name="start"  id="fromDate" placeholder="dd/mm/yyyy" value="{{$previousDate}}"/>
								<div class="input-group-append">
								<span class="input-group-text bg-myvegiz b-0 text-white">TO</span>
							  </div>
							<input type="text" class="form-control date-inputmask toDate" name="end" id="toDate" placeholder="dd/mm/yyyy" value="{{$todayDate}}"/>
							</div>
						</div>
				   </div>
				   <div class="col-sm-4 form-group mt-4">
						<button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
						<button type="reset" class="btn btn-danger" id="btnClear" name="btnClear">Clear</button>
				   </div>
				</div>
			 </div>
		</div>
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">Order Report</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="orderreport" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
						<th>Sr. No</th>
						<th>Restaurant Name</th>
						<th>Order Code</th>
						<th>Client Name</th> 
						<th>Address</th>
						<th>Mobile No</th>
						<th>Order Status</th>
						<th>Amount</th>
						<th>Order Date</th>
						<th>Delivery Boy</th>
						<th>Operations</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/other/report.js') }}"></script>
   <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
   <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
   <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
  @if (session('success'))
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
