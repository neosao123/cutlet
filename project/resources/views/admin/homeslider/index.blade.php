@extends('admin.layout.master', ['pageTitle'=>"Home Slider"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/homeslider/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Home Slider</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Home Slider List</a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Home Slider</h5>
          </div>
          <div class="card-body pt-0">
            <form id="sliderForm" class="" data-parsley-validate="">
              @csrf
              <input type="hidden" value="" name="code" id="code">
			  <div class="row">
			  <div class="col-lg-2 mb-3">
				<label class="form-label" for="imagePath">City</label>
				<select class="form-control" style="width: 100%; height:36px;" name="cityCode" id="cityCode" required data-parsley-required-message="City is required">
					<option value="">Select</option>
                  @foreach ($city as $cityItem)
                    <option value="{{ $cityItem->code }}">{{ $cityItem->cityName }}</option>
                  @endforeach
                </select>
				<span class="text-danger" id="cityerr"></span>
				 </div>
				 <div class="col-lg-4 mb-3">
				<label class="form-label" for="imagePath">Restaurant</label>
				<input type="hidden" class="form-control" id="dbResCode" name="dbResCode">
				<select class="form-control" style="width: 100%; height:36px;" name="restaurantCode" id="restaurantCode" data-parsley-required-message="Restaurant is required">
					<!--<option value="">Select</option>
                  @foreach ($restaurant as $res)
                    <option value="{{ $res->code }}">{{ $res->entityName }}</option>
                  @endforeach-->
                </select>
				<span class="text-danger" id="reserr"></span>
				 </div>
			  <div class="col-lg-4 mb-3">
				<label class="form-label" for="imagePath">Home Slider Image</label>
				<input type="file" id="imagePath" name="imagePath" class="form-control">
				<small>Please upload an image (480 W x 320 H)</small><br>
				<span class="text-danger" id="err"></span>
			  </div>
			  <div class="row el-element-overlay d-none" id="preview">
				<div class="col-md-6 text-center">
					<div class="card">
						<div class="el-card-item">
							<div class="el-card-avatar el-overlay-1"> <img src="" id="src_id" alt="user">
								<div class="el-overlay">
									<ul class="list-style-none el-info">
										<li class="el-item"><a class="btn default btn-outline image-popup-vertical-fit el-link" id="href_id" href=""><i class="icon-magnifier"></i></a></li>
										<li class="el-item"><a class="btn default btn-outline el-link" href="javascript:void(0);"><i class="icon-link"></i></a></li>
									</ul>
								</div>
							</div> 
						</div>
					</div>
				</div>
			</div>
              <div style="margin-top:35px;" class="mb-3">
                <div class="custom-control custom-checkbox">
                  <input class="custom-control-input" name="isActive" id="isActive" type="checkbox" value="1" />
                  <label class="custom-control-label" for="isActive">Active</label>
                </div>
              </div>
              <div class="mb-3">
                <button type="button" class="btn btn-success btnsubmit">Submit</button>
                <button type="reset" class="btn btn-danger">Reset</button>
              </div>
			  </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0" data-anchor="data-anchor">Home Slider List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-slider" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No. </th>
					<th>Code</th>
					<th>City</th>
					<th>Restaurant</th>
                    <th>Image</th>
                    <th>Status</th>
                    <th>Actions</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/homeslider/index.js') }}"></script>
   <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
@endpush
