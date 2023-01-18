@extends('admin.layout.master', ['pageTitle'=> "Orders List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/orders/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Orders List</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Orders List</a></li>
            </ol>
          </nav>
        </div>
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
							<label for="orderCode">Order Code :</label>
							<select type="text" class="form-control select2" style="width: 100%;" id="orderCode" name="orderCode">
							<option value="">Select</option>
							@if ($orderList) {
								@foreach ($orderList as $od)
									<option value="{{ $od->code }}">{{ $od->code }}</option>
								@endforeach
							@endif
							</select>
				   </div>
				   <div class="col-sm-3 form-group">
						<span> <label for="orderStatus">Order Status :</label> </span>
						<select class="form-control select2" style="width: 100%;" id="orderStatus" name="orderStatus">
							<option value="">Select</option>
							@if($orderStatus)
								@foreach ($orderStatus as $status)
									@if($status->statusSName!='PND')
										<option value="{{ $status->statusSName }}">{{ $status->statusName }}</option>
									@endif
								@endforeach
							@endif
						</select>
				   </div>
				    <div class="col-sm-3 form-group">
						<label for="cityCode">Restaurant:</label>
						<select class="form-control select2" style="width: 100%;" id="restaurantCode" name="restaurantCode">
						<option value="">Select</option>
						@if($restaurant)
							@foreach ($restaurant as $r)
								<option value="{{ $r->code }}">{{ $r->entityName }}</option>
							@endforeach
						@endif
						</select>
				   </div>
				   <div class="col-sm-3 form-group">
					  <span> <label for="orderStatus">Delivery Boy:</label> </span>
						<select type="text" name="deliveryboy" style="width: 100%;" id="deliveryboy" class="form-control select2">
						<option value="">Select</option>
						@if($deliveryboy)
							@foreach ($deliveryboy as $db)
								<option value="{{ $db->code }}">{{ $db->name }}</option>
							@endforeach
						@endif
						</select>
				   </div>
				    @php
					$todayDate = date('d-m-Y');
					$previousDate = date('d-m-Y', strtotime(' - 7 days'));
					@endphp
					<input type="hidden" class="form-control" id="todayDate" name="todayDate" value="{{ $todayDate}}">
					<input type="hidden" class="form-control" id="previousDate" name="previousDate" value="{{ $previousDate}}">
					<div class="col-sm-5">
						<div class="input-daterange input-group">
							<span> <label> Search Dates :</label> </span>
							<div class="input-daterange input-group" id="productDateRange">
								<input type="text" class="form-control text-center date-inputmask col-sm-5" name="start" id="fromDate" placeholder="dd/mm/yyyy" value="<?= $previousDate ?>" />
								<div class="input-group-append">
									<span class="input-group-text bg-cutlet b-0 text-white">TO</span>
								</div>
								<input type="text" class="form-control text-center date-inputmask toDate" name="end" id="toDate" placeholder="dd/mm/yyyy" value="<?= $todayDate ?>" />
							</div>
						</div>
					</div>
			   </div>
			   <div class="row">
				   <div class="col-sm-12 form-group mt-4 text-center">
						<button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
						<button type="Reset" class="btn btn-danger" id="btnClear">Clear</button>
				   </div>
				</div>
		  </div>
		</div>
		<div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">Orders list</h5>
              </div>
            </div>
		 </div>
         <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-pending" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No</th>
					<th>Code</th>
					<th>Client Name</th>
					<th>Restaurant</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/orders/orderList.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
   <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
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
