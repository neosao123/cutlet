@extends('restaurant.layout.master', ['pageTitle'=>"Restaurant Confirm Order List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/restaurant/confirmorder/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
  <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Confirm Order</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Confirm Orders List</a></li>
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
						<label>Order Code:</label>
						<div class="form-group">
						   <input type="text"  class="form-control" list="orderlist" id="orderCode" name="orderCode" placeholder="Enter Order Code Here ">
							<datalist id="orderlist">
							<?php if($restaurantorder){ foreach($restaurantorder as $od){
							echo'<option value="'.$od->code.'"></option>';
							} } ?>
							</datalist>
						</div>
				   </div>
					<div class="col-sm-3 form-group">
						<label>Order Status:</label>
						<div class="form-group">
						   <select class="select2 form-control"  style="width: 100%;" name="status" id="status">
								 <option value="">Select</option>
								 <?php
								foreach ($restaurantstatus as $status) {
									echo '<option value="' . $status->statusSName . '">' . $status->statusName . '</option>';
									
								} ?>
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
                <h5 class="mb-0" data-anchor="data-anchor">Confirm Orders List</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="orderconfirm" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
						<th>Sr. No</th>
						<th>Code</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/restaurant/confirmorder/index.js') }}"></script>
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
		
		var data='<?php echo $error; ?>';
		if(data!='')
		{
			var obj=JSON.parse(data);
			if(obj.status)
			{ 
				toastr.success(obj.message, 'Order', { "progressBar": true });
			}
			else
			{
			  toastr.error(obj.message, 'Order', { "progressBar": true });
			}
		}
      });
    </script>
  @endif
@endpush
