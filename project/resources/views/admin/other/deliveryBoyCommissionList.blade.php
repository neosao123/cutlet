@extends('admin.layout.master', ['pageTitle'=>"Delivery Boy Commission List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/other/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Delivery Boy Commission List</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Delivery Boy Commission List</a></li>
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
			   <div class="col-sm-6 form-group">
					<label>Delivery Boy Name:</label>
					<div class="form-group">
					   <select class="select2 form-control custom-select" style="width: 100%;" name="deliveryboyCode" id="deliveryboyCode">
							 <option value="">Select Delivery Boy</option>
							 @if($user)
							   @foreach($user as $userItem )
								<option value="{{ $userItem->code}}">{{ $userItem->name}} - ({{ $userItem->mobile }})</option>
							  @endforeach
							 @endif
					   </select>
					</div>
			   </div>
			 @php $addDate = date('d-m-Y') @endphp
				<div class="col-sm-3">
					<div class="input-daterange input-group">
					<span> <label> Search Dates :</label> </span>
						<div class="input-daterange input-group" id="productDateRange">
							<input type="text" class="form-control date-inputmask" name="start" id="addDate" placeholder="dd/mm/yyyy" value="{{ $addDate }}"/>
							
						</div>
					</div>
				</div>
				<div class="col-sm-3" style="margin-top:28px;">
					<button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
					<button type="Reset" class="btn btn-danger" id="btnClear">Clear</button>
				</div>
          </div>
		</div>
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">Commission list</h5>
              </div>
            </div>
		 </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-commission" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No. </th>
                     <th>Delivery Boy</th>
					<th>Total Order Amount</th>
					<th>Receive Amount</th>
					<th>Delivery Boy Amount</th> 
					<th>Operations</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
	<div id="responsive-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">View Commission</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
				</div>
				<div class="modal-body">
					
				</div>
				
			</div>
		</div>
	</div>
			<!-- /.modal -->
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/other/db.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
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
