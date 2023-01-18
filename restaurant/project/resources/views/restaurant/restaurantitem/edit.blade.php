@extends('restaurant.layout.master', ['pageTitle'=>"Edit Restaurant Item"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/restaurant/restaurantitem/index.css') }}" rel="stylesheet">
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
              <li class="breadcrumb-item"><a href="#">Restaurant Item Update </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Update Restaurant Item</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('restaurantItems/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        @if ($itemDetails)
          <form action="{{ url('restaurantItems/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
            @csrf
            <input type="hidden" name="code" value="{{ $itemDetails->code }}" readonly >
            <div class="form-row">
				<div class="col-md-7 mb-3">
					<label for="itemName">Item Name :<b style="color:red">*</b></label>
					<input type="text" id="itemName" name="itemName" class="form-control" value="{{ $itemDetails->itemName }}" required data-parsley-required-message="Item Name is required">
					<span class="text-danger">
                                {{ $errors->first('itemName') }}
                     </span>
				</div> 
			    <div class="col-md-5 mb-3">
					<label for="salePrice"> Sale Price : <b style="color:red">*</b></label>
					<input type="number" id="salePrice" name="salePrice" class="form-control" value="{{ $itemDetails->salePrice }}" required data-parsley-required-message="Sale Price is required">
				     <span class="text-danger">
                                {{ $errors->first('salePrice') }}
                     </span>
				</div>
			</div>
			<div class="form-row">
				  
				<div class="col-md-6 mb-3">
					<label for="itemPackagingPrice"> Packing Charges : <b style="color:red">*</b></label>
					<input type="number" id="itemPackagingPrice" name="itemPackagingPrice" class="form-control" value="{{ $itemDetails->itemPackagingPrice }}" required data-parsley-required-message="Packaging Charges are required">
				     <span class="text-danger">
                                {{ $errors->first('itemPackagingPrice') }}
                     </span>
				</div> 
				<div class="col-md-6 mb-3">
					<label for="maxOrderQty"> Max Order Quantity : <b style="color:red">*</b></label>
					<input type="number" id="maxOrderQty" name="maxOrderQty" class="form-control" required  value="{{ $itemDetails->maxOrderQty }}" data-parsley-required-message="Max Order Qty is required">
				     <span class="text-danger">
                                {{ $errors->first('maxOrderQty') }}
                     </span>
				</div> 
			</div>
			<div class="form-row">
				<div class="col-md-4 mb-3">
					<label for="cuisineType"> Cuisine Type : <b style="color:red">*</b></label>
					<select id="cuisineType" name="cuisineType" class="form-control" required data-parsley-required-message="Cuisine Type is required">
						<option value="">Select Cuisine</option>
						<option value="veg" @if($itemDetails->cuisineType == 'veg') selected @endif>Veg</option>
						<option value="nonveg"  @if($itemDetails->cuisineType == 'nonveg') selected @endif>Non - Veg</option>
					</select>
					<span class="text-danger">
                                {{ $errors->first('cuisineType') }}
                     </span>
				</div>
				<div class="col-md-8 mb-3">
					<label for="itemImage"> Item Image :</label>
					<input type="file" id="itemImage" name="itemImage" class="form-control" onchange="document.getElementById('itemImageShow').src = window.URL.createObjectURL(this.files[0])">
					<span>Please upload images of 640 X 960 (width x height) size.</span>
					<span id="photoError" class="text-danger"></span>
				</div>
			</div>
			@if($itemDetails->itemPhoto!='' && $itemDetails->itemPhoto!=NULL)
			<div class="form-row">
				<div class="col-md-3 el-element-overlay">
					<div class="card">
						<div class="el-card-item">
						
							<div class="el-card-avatar el-overlay-1"> <img id="itemImageShow" src="{{env('IMG_URL').'uploads/restaurant/restaurantitemimage/'.$itemDetails->restaurantCode.'/'.$itemDetails->itemPhoto}}" alt="category File">
								<div class="el-overlay">
									<ul class="list-style-none el-info">
										<li class="el-item"><a class="btn default btn-outline image-popup-vertical-fit el-link" href="{{env('IMG_URL')}}uploads/restaurant/restaurantitemimage/{{$itemDetails->restaurantCode}}/{{$itemDetails->itemPhoto}}" target="_blank"><i class="icon-magnifier"></i></a></li>
										<li class="el-item"><a class="btn default btn-outline el-link" onclick="deleteButton('{{ $itemDetails->code}}','{{ $itemDetails->restaurantCode}}','{{ $itemDetails->itemPhoto}}')"><i class="icon-trash"></i></a></li>
									</ul>
								</div>
							</div>
							<div class="el-card-content">
								<h4 class="m-b-0">Item Image</h4> 
							</div>
						</div>
					</div>
				</div>  
				<span id="photoError" class="text-danger"></span>
			</div>
			@endif						
			<div class="form-row">
		 		<div class="col-md-12 mb-3">
					<label for="address">Item Description :</label>
					<textarea type="text" id="itemDescription" name="itemDescription" class="form-control">{{ $itemDetails->itemDescription}}</textarea>
				</div> 
			</div>
			<div class="form-row">
				<div class="col-md-6 mb-3">
					<label for="menuCategoryCode">Menu Category:<b style="color:red">*</b></label>
					<select id="menuCategoryCode" name="menuCategoryCode" class="form-control" required data-parsley-required-message="Menu category is required">
						<option value="">Select Menu Category</option>
						@foreach ($menuCategory as $menu)
							@if($itemDetails->menuCategoryCode==$menu->code)
								<option value="{{ $menu->code }}" selected>{{ $menu->menuCategoryName }}</option>
							@else 
								<option value="{{ $menu->code }}">{{ $menu->menuCategoryName }}</option>
							@endif
						@endforeach
					</select>
                     <span class="text-danger">
                                {{ $errors->first('menuCategoryCode') }}
                     </span>					
				</div> 
				<div class="col-md-6 mb-3">
					<label for="menuSubCategoryCode">Menu Sub Category:</label>
					<select id="menuSubCategoryCode" name="menuSubCategoryCode" class="form-control">
						<option value="">Select Menu Subcategory</option>
						@foreach ($menuSubcategory as $smenu)
							@if($itemDetails->menuSubCategoryCode==$smenu->code)
								<option value="{{ $smenu->code }}" selected>{{ $smenu->menuSubCategoryName }}</option>
							@else 
								<option value="{{ $smenu->code }}">{{ $smenu->menuSubCategoryName }}</option>
							@endif
						@endforeach
					</select>									 
				</div> 
			</div>
			<div class="row">
				<div class="form-group col-md-4">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" value="1" class="custom-control-input" id="itemActiveStatus" name="itemActiveStatus" {{$itemDetails->itemActiveStatus == 1 ? 'checked' : ''}}>  
						<label class="custom-control-label" for="itemActiveStatus">Item Status</label>
					</div>
				</div>
				<div class="form-group col-md-4">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" value="1" class="custom-control-input" id="isActive" name="isActive" {{$itemDetails->isActive == 1 ? 'checked' : ''}}>  
						<label class="custom-control-label" for="isActive">Active</label>
					</div>
				</div>
			</div>
			<div class="text-xs-right">							
				<button type="submit" class="btn btn-success">Submit</button>
			</div>
          </form>
        @endif
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
	 <script type="text/javascript" src="{{ asset('assets/init_site/restaurant/restaurantitem/index.js') }}"></script>
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
