@extends('admin.layout.master', ['pageTitle'=>"Restaurant Offer List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/restaurantcoupon/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Restaurant Offer</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Restaurant Offer List</a></li>
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
						<form>
							<div class="form-row">
							    <div class="col-sm-3 mb-3">
									<div class="form-group">
										<span> <label for="restaurantCode">Restaurant Name:</label> </span>
										<select id="restaurantCode" name="restaurantCode" class="form-control select2 custom-select" required style="width: 100%; height:36px;">
											<option value="">Select</option>
											@if ($restaurants)
												@foreach ($restaurants as $p)
													<option value="{{ $p->code }}">{{ $p->entityName}}</option>
												@endforeach
											@endif
										</select> 
									</div>
								</div>
							    
								<div class="col-sm-3 mb-3">
									<div class="form-group">
										<span> <label for="coupanCode">Coupon Code :</label> </span>
										<select type="text" class="form-control select2 custom-select" id="couponCode" name="couponCode" style="width: 100%; height:36px;">
											<option value="">Select</option>
											@if ($restaurantcoupon)
												@foreach ($restaurantcoupon as $p)
												    <option value="{{ $p->code }}">{{ $p->couponCode }}</option>
												@endforeach
											@endif
										</select>
									</div>
								</div>

								<div class="col-sm-3 mb-3">
									<div class="form-group">
										<span> <label for="offerType">Offer Type:</label> </span>
										<select id="offerType" name="offerType" class="form-control" required>
											<option value="">Select</option>
											<option value="flat">Flat</option>
											<option value="cap">Cap</option>
										</select>
									</div>
								</div> 
								
								<div class="card-body">
									 <div class="col-sm-12 form-group text-center">
										<button type="button" id="btnSearch" name="btnSearch" class="btn btn-success">Search</button>
										<button type="Reset" class="btn btn-danger" id="btnClear">Clear</button>
								   </div>
								</div>
							</div>
						</form>
					</div>
				</div>
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-sm-6">
                <h5 class="mb-0" data-anchor="data-anchor">Restaurant Offer List</h5>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-offer" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
						<th>Sr.No</th>
						<th>Code</th>
						<th>Restaurant</th>
						<th>Coupon Code</th>
						<th>Offer Type</th>
						<th>Discount (%) / Flat Amount</th>
						<th>Minimum Amount</th>
						<th>Approved</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/restaurantcoupon/index.js') }}"></script>
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
