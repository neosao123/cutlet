@extends('admin.layout.master', ['pageTitle'=>"Add Restaurant Item"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/restaurantitem/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Restaurant Item</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Restaurant Item Add</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-10">
            <h5 class="mb-0" data-anchor="data-anchor">New Restaurant Item</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('restaurantItem/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        <form action="{{ url('restaurantItem/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
          @csrf
			<div class="form-row">
				<div class="col-md-7 mb-3">
					<label for="itemName">Item Name :<b style="color:red">*</b></label>
					<input type="text" id="itemName" name="itemName" class="form-control" required data-parsley-required-message="Item Name is required">
					<span class="text-danger">
                                {{ $errors->first('itemName') }}
                     </span>
				</div> 
				<div class="col-md-5 mb-3">
					<label for="restaurantCode">Restaurant :<b style="color:red">*</b></label>
					<select id="restaurantCode" name="restaurantCode" class="form-control" required style="width:100%" data-parsley-required-message="Restaurant is required">
						<option value="">Select Restaurant</option>
						@foreach ($restaurant as $res)
							<option value="{{ $res->code }}">{{ $res->entityName }}</option>
						@endforeach
					</select>
					<span class="text-danger">
                                {{ $errors->first('restaurantCode') }}
                     </span>
				</div> 
			</div>
			<div class="form-row">
				<div class="col-md-4 mb-3">
					<label for="salePrice"> Sale Price : <b style="color:red">*</b></label>
					<input type="text" id="salePrice" name="salePrice" class="form-control" required data-parsley-required-message="Sale Price is required" onkeypress="return isDecimal(event)" onchange="checkPrice();">
					<span class="text-danger">
                                {{ $errors->first('salePrice') }}
                     </span>
				</div>  
				<div class="col-md-4 mb-3">
					<label for="itemPackagingPrice"> Packing Charges : <b style="color:red">*</b></label>
					<input type="text" id="itemPackagingPrice" name="itemPackagingPrice" class="form-control" required data-parsley-required-message="Packaging charges is required" onkeypress="return isDecimal(event)" onchange="checkPackPrice();">
				    <span class="text-danger">
                                {{ $errors->first('itemPackagingPrice') }}
                     </span>
				</div> 
				<div class="col-md-4 mb-3">
					<label for="maxOrderQty"> Max Order Quantity : <b style="color:red">*</b></label>
					<input type="number" id="maxOrderQty" name="maxOrderQty" class="form-control" required data-parsley-required-message="Max Order Qty is required">
				     <span class="text-danger">
                                {{ $errors->first('maxOrderQty') }}
                     </span>
				</div> 
			</div>		
			<div class="form-row">
				<div class="col-md-4 mb-3">
					<label for="cuisineType"> Cuisine Type : <b style="color:red">*</b></label>
					<select id="cuisineType" name="cuisineType" class="form-control" required data-parsley-required-message="Cuisine type is required">
						<option value="">Select Cuisine</option>
						<option value="veg">Veg</option>
						<option value="nonveg">Non - Veg</option>
					</select>
					<span class="text-danger">
                                {{ $errors->first('cuisineType') }}
                     </span>
				</div>
				<div class="col-md-4 mb-3">
					<label for="itemImage"> Item Image :</label>
					<div class="input-group"> 
						<div class="custom-file">
							<input type="file" class="custom-file-input" id="itemImage" name="itemImage">
							<label class="custom-file-label" for="itemImage">Choose file</label>
						</div>
					</div> 
					<span style="font-size:12px;" id="previous_error">Please upload images of 640 X 960 (width x height) size.</span>
					<span style="font-size:12px;" class="text-danger" id="err"></span>
				</div>
				<div class="col-md-4 text-center">
				 <div class="row el-element-overlay d-none" id="preview">
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
			</div>			
			<div class="form-row">
		 		<div class="col-md-12 mb-3">
					<label for="address">Item Description :</label>
					<textarea type="text" id="itemDescription" name="itemDescription" class="form-control"></textarea>
				</div> 
			</div>
			<div class="form-row">
				<div class="col-md-6 mb-3">
					<label for="menuCategoryCode">Menu Category:<b style="color:red">*</b></label>
					<select id="menuCategoryCode" name="menuCategoryCode" class="form-control" required data-parsley-required-message="Menu category is required">
						<option value="">Select Menu Category</option>
						@foreach ($menuCategory as $menu)
							<option value="{{ $menu->code }}">{{ $menu->menuCategoryName }}</option>
						@endforeach
					</select>
                    <span class="text-danger">
                                {{ $errors->first('cuisineType') }}
                     </span>					
				</div> 
				<div class="col-md-6 mb-3">
					<label for="menuSubCategoryCode">Menu Sub Category:</label>
					<select id="menuSubCategoryCode" name="menuSubCategoryCode" class="form-control">
					</select>									 
				</div> 
			</div>
			<div class="row">
				<div class="form-group col-md-4">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" value="1" class="custom-control-input" id="itemActiveStatus" name="itemActiveStatus">  
						<label class="custom-control-label" for="itemActiveStatus">Item Status</label>
					</div>
				</div>
				<div class="form-group col-md-4">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" value="1" class="custom-control-input" id="isAdminApproved" name="isAdminApproved">
						<label class="custom-control-label" for="isAdminApproved">Approved</label>
					</div>
				</div>
				<div class="form-group col-md-4">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" value="1" class="custom-control-input" id="isActive" name="isActive">  
						<label class="custom-control-label" for="isActive">Active</label>
					</div>
				</div>
			</div>
				@if($tags->count()>0)
					<div class="form-group">
						<div class="row">
							<div class="col-sm-2 mb-1">
								<h6><b> Tags:</b></h6>
							</div>
						</div>
						<div class="row" style="margin-left:35px;">
							
							<div class="col-sm-3 mb-3">
								 <div class="custom-control custom-radio custom-control-inline">
									<input type="radio" class="custom-control-input" id="tagSection" checked name="tagCode" value="1">
									<label class="custom-control-label"  for="tagSection"><b>No Tag</b></label>
								</div>
							</div>
							@foreach($tags as $tagItem)
							<div class="col-sm-3 mb-3">
								 <div class="custom-control custom-radio custom-control-inline">
									<input type="radio" class="custom-control-input" id="tagSection{{$tagItem->code}}" name="tagCode" value="{{$tagItem->code}}">
									<label class="custom-control-label" style="color:{{$tagItem->tagColor}}" for="tagSection{{$tagItem->code}}"><b>{{$tagItem->tagTitle}}</b></label> 
									
								</div>
							</div>
							@endforeach
						</div>
					   </div>
						@endif
			<div class="text-xs-right">							
				<button type="submit" class="btn btn-success">Submit</button>
				<button type="Reset" class="btn btn-danger">Reset</button>
			</div>
        </form>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
	 <script type="text/javascript" src="{{ asset('assets/init_site/admin/restaurantitem/index.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
  @if (session('error'))
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
