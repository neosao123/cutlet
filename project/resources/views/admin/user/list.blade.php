@extends('admin.layout.master', ['pageTitle'=>"Users List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/users/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Users List</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Users List</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
        <a href="{{ url('users/add') }}" class="btn btn-info btn-sm float-right">Add New</a>
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
			   <div class="col-sm-3 form-group d-none">
					<label>User Name:</label>
					<div class="form-group">
					   <select class="select2 form-control custom-select" style="width: 100%;" name="username" id="username">
							 <option value="">Select</option>
							   @foreach($users as $userItem )
								<option value="{{ $userItem->code}}">{{ $userItem->username}}</option>
							  @endforeach
					   </select>
					</div>
			   </div>
			   <div class="col-sm-3 form-group">
					<label>Designation:</label>
					<div class="form-group">
					   <select class="select2 form-control custom-select" name="designation" id="designation" style="width: 100%; height:36px;">
							 <option value="">Select</option>
							 @foreach($designation as $designationItem)
							  <option value="{{$designationItem->code}}">{{$designationItem->designation}}</option>
							 @endforeach
					   </select>
					</div>
			   </div>
			    <div class="col-sm-3 form-group">
					<label>City:</label>
					<div class="form-group">
					   <select class="select2 form-control custom-select" name="city" id="city" style="width: 100%; height:36px;">
							 <option value="">Select</option>
							 @foreach($city as $cityItem)
							  <option value="{{$cityItem->code}}">{{$cityItem->cityName}}</option>
							 @endforeach
					   </select>
					</div>
			   </div>
			   <div class="col-sm-3 form-group">
			       <label>Role:</label>
				   <div class="form-group">
				      <select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="role" id="role">
                                <option value="">Select Role</option>
                                <option value="USER">User</option>
								 <option value="DBOY">Delivery Boy</option>
                        </select>
				   </div>
			   </div>
			 <div class="mt-4">
					<button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
					<button type="Reset" class="btn btn-danger" id="btnClear">Clear</button>
			</div>
          </div>
		</div>
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">User list</h5>
              </div>
            </div>
		 </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-Users" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No. </th>
                    <th>Full Name</th>
                    <th>Designation</th>
                    <th>City</th>
                    <th>Mobile Number</th>
                    <th>Email</th>
					<th>Role</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/users/index.js') }}"></script>
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
