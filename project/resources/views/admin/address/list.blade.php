@extends('admin.layout.master', ['pageTitle'=>"Address List"])
@push('styles')
<link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/address/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Address</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Address List</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
        <a href="{{ url('address/add') }}" class="btn btn-info btn-sm float-right">Add New</a>
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
						<label>City:</label>
						<div class="form-group">
						   <select class="select2 form-control" style="width: 100%;" name="city" id="city">
								 <option value="">Select</option>
								 @foreach($city as $cityItem )
									<option value="{{ $cityItem->code}}">{{ $cityItem->cityName}}</option>
								  @endforeach
						   </select>
						</div>
				   </div>
				   <div class="col-sm-3 form-group">
						<label>State:</label>
						<div class="form-group">
						   <select class="select2 form-control custom-select" style="width: 100%;" name="state" id="state">
								 <option value="">Select</option>
								 @foreach($state as $stateItem)
								  <option value="{{$stateItem->state}}">{{$stateItem->state}}</option>
								 @endforeach
						   </select>
						</div>
				   </div>
				   <div class="col-sm-3 form-group">
						<label>Taluka:</label>
						<div class="form-group">
						   <select class="select2 form-control custom-select" style="width: 100%;" name="taluka" id="taluka"> 
								 <option value="">Select</option>
								 @foreach($taluka as $talukaItem)
								  <option value="{{$talukaItem->taluka}}">{{$talukaItem->taluka}}</option>
								 @endforeach
						   </select>
						</div>
				   </div>
				   <div class="col-sm-3 form-group">
						<label>District:</label>
						<div class="form-group">
						   <select class="select2 form-control custom-select" style="width: 100%;" name="district" id="district"> 
								 <option value="">Select</option>
								 @foreach($district as $districtItem)
								  <option value="{{$districtItem->district}}">{{$districtItem->district}}</option>
								 @endforeach
						   </select>
						</div>
				   </div>
			   </div>
			   <div class="row">
			       <div class="col-sm-3 form-group">
						<label>Place:</label>
						<div class="form-group">
						   <select class="select2 form-control custom-select" style="width: 100%;" name="place" id="place"> 
								 <option value="">Select</option>
								 @foreach($place as $placeItem)
								  <option value="{{$placeItem->place}}">{{$placeItem->place}}</option>
								 @endforeach
						   </select>
						</div>
				   </div>
				   <div class="col-sm-3 form-group mt-4 text-center">
						<button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
						<button type="button" class="btn btn-danger" id="btnClear">Clear</button>
				   </div>
				</div>
		  </div>
		</div>
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">Address List</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-address" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
						<th>Sr.No</th>
						<th>Code</th>
						<th>City</th>
						<th>State</th>
						<th>District</th>
						<th>Place</th>
						<th>Pincode</th>
						<th>Service Available Status</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/address/index.js') }}"></script>
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
