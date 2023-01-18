@extends('admin.layout.master', ['pageTitle'=>"Activity List"])
@push('styles')
<link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/activity/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Activity Log</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Activity Log List</a></li>
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
						<label>Date:</label>
						<div class="form-group">
						   <input type="date" name="date" id="date" class="form-control" value="<?= date('Y-m-d') ?>">
						</div>
				   </div>				
			   </div>
			   <div class="row">
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
                <h5 class="mb-0" data-anchor="data-anchor">Activity Log List</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-activitylog" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
				     <th>NO</th>
                    <th>Date & Time</th> 
                    <th>IP</th>
                    <th>USER</th>
                    <th>Operation</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/activity/index.js') }}"></script>
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
