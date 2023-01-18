@extends('admin.layout.master', ['pageTitle'=>"Customer List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/customer/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
  <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
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
              <li class="breadcrumb-item"><a href="#">Customer List</a></li>
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
						<label>Customer:</label>
						<div class="form-group">
						  <select class="select2 form-control"  style="width: 100%;" name="clientCode" id="clientCode">
							<option value="">Select</option>
							@if($clientmaster)
								@foreach($clientmaster as $cus)
									<option value="{{$cus->code}}">{{ $cus->name}}</option>
								@endforeach
							@endif							
							</select>
						</div>
				   </div>
				   <div class="col-sm-3 form-group">
						<label>Login City:</label>
						<div class="form-group">
						   <select class="select2 form-control"  style="width: 100%;" name="cityCode" id="cityCode">
							<option value="">Select</option>
							@if($citymaster)
								@foreach($citymaster as $city)
									<option value="{{ $city->code }}">{{ $city->cityName}}</option>
								@endforeach
							@endif
							</select>
						</div>
				   </div>
				   
					<div class="col-sm-3 form-group">
						<label>Mobile Number:</label>
						<div class="form-group">
						   <select class="select2 form-control"  style="width: 100%;" name="mobile" id="mobile">
								 <option value="">Select</option>
								 @if($mobiles)
									@foreach ($mobiles as $mobile)
										<option value="{{ $mobile->mobile}}">{{ $mobile->mobile}}</option>
									@endforeach
								@endif
						   </select>
						</div>
				   </div>
				    <div class="col-sm-3 form-group">
						<label>Email Id:</label>
						<div class="form-group">
						   <select class="select2 form-control"  style="width: 100%;" name="email" id="email">
								 <option value="">Select</option>
								@if($emails)
									@foreach ($emails as $email) 
										<option value="{{ $email->emailId }}">{{ $email->emailId }}</option>
									@endforeach
								@endif
						   </select>
						</div>
				   </div>
				   <div class="col-sm-12 form-group text-center">
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
                <h5 class="mb-0" data-anchor="data-anchor">Customer List</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-customer" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
						<th>Sr.No</th>
						<th>Code</th>
                        <th>Client Name</th>
						<th>City</th>
						<th>Mobile</th>
						<th>Email ID</th>
						<th>Status</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/customer/index.js') }}"></script>
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
