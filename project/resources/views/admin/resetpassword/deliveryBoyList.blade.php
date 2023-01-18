@extends('admin.layout.master', ['pageTitle'=>"Delivery Boy List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/resetpassword/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Delivery Boy List</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Delivery Boy List</a></li>
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
                <h4 class="mb-1" data-anchor="data-anchor">Filter</h4>
              </div>
            </div>
			<hr>
			<div class="row">
			    <div class="col-sm-3 form-group">
					<label>City:</label>
					<div class="form-group">
					   <select class="form-control select2" style="width: 100%; height:36px;" name="city" id="city">
							 <option value="">Select</option>
							 @foreach($city as $cityItem)
							  <option value="{{$cityItem->code}}">{{$cityItem->cityName}}</option>
							 @endforeach
					   </select>
					</div>
			   </div>
			   <div class="col-sm-4 form-group mt-4">
					<button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
					<button type="Reset" class="btn btn-danger" id="btnClear">Clear</button>
			   </div>
			</div>
		 </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-deliveryBoy" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No. </th>
                    <th>Code</th>
					<th>City</th>
					<th>Name</th>
                    <th>Username</th>
                    <th>Mobile</th>
                    <th>Email</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/resetpassword/index.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
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
