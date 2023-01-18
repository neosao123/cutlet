@extends('restaurant.layout.master', ['pageTitle'=>"Restaurant Item"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/restaurant/restaurantitem/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
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
              <li class="breadcrumb-item"><a href="#">Restaurant Item View </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">View Restaurant Item </h5>
          </div>
		   <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('restaurantItems/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if ($itemDetails)
          <div class="form-row">
				<div class="col-md-7 mb-3">
					<label for="itemName">Item Name :<b style="color:red">*</b></label>
					<input type="text" id="itemName" name="itemName" class="form-control-line" value="{{ $itemDetails->itemName }}" readonly>
					
				</div> 
				<div class="col-md-5 mb-3">
					<label for="salePrice"> Sale Price : <b style="color:red">*</b></label>
					<input type="number" id="salePrice" name="salePrice" class="form-control-line" value="{{ $itemDetails->salePrice }}" readonly>
				</div>
			</div>
			<div class="form-row">
				  
				<div class="col-md-4 mb-3">
					<label for="itemPackagingPrice"> Packing Charges : <b style="color:red">*</b></label>
					<input type="number" id="itemPackagingPrice" name="itemPackagingPrice" class="form-control-line" value="{{ $itemDetails->itemPackagingPrice }}" readonly>
				</div> 
				<div class="col-md-4 mb-3">
					<label for="maxOrderQty"> Maximum Order Quantity : <b style="color:red">*</b></label>
					<input type="number" id="maxOrderQty" name="maxOrderQty" class="form-control-line"  value="{{ $itemDetails->maxOrderQty }}" readonly>
				</div> 
				<div class="col-md-4 mb-3">
					<label for="cuisineType"> Cuisine Type : <b style="color:red">*</b></label>
					<select id="cuisineType" name="cuisineType" class="form-control-line" disabled>
						<option value="">Select Cuisine</option>
						<option value="veg" {{ $itemDetails->cuisineType}}=='veg' ? 'selected' >Veg</option>
						<option value="nonveg"  {{ $itemDetails->cuisineType}}=='nonveg' ? 'selected'>Non - Veg</option>
					</select>
				</div>
			</div>		
			@if($itemDetails->itemPhoto!='' && $itemDetails->itemPhoto!=NULL)
			<div class="form-row">
				<div class="col-md-3 el-element-overlay">
					<div class="card">
						<div class="el-card-item">
						
							<div class="el-card-avatar el-overlay-1"> <img id="itemImageShow" src="{{env('IMG_URL').'uploads/restaurant/restaurantitemimage/'.$itemDetails->restaurantCode.'/'.$itemDetails->itemPhoto}}" alt="category File">
								
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
					<label for="Restaurant Item">Item Description :</label>
					<textarea type="text" id="itemDescription" name="itemDescription" readonly class="form-control-line">{{ $itemDetails->itemDescription}}</textarea>
				</div> 
			</div>
			<div class="form-row">
				<div class="col-md-6 mb-3">
					<label for="menuCategoryCode">Menu Category:<b style="color:red">*</b></label>
					<select id="menuCategoryCode" name="menuCategoryCode" readonly class="form-control-line">
						<option value="">Select Menu Category</option>
						@foreach ($menuCategory as $menu)
							@if($itemDetails->menuCategoryCode==$menu->code)
								<option value="{{ $menu->code }}" selected>{{ $menu->menuCategoryName }}</option>
							@else 
								<option value="{{ $menu->code }}">{{ $menu->menuCategoryName }}</option>
							@endif
						@endforeach
					</select>	
				</div> 
				<div class="col-md-6 mb-3">
					<label for="menuSubCategoryCode">Menu Sub Category:</label>
					<select id="menuSubCategoryCode" name="menuSubCategoryCode" readonly class="form-control-line">
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
						<input type="checkbox" value="1" class="custom-control-input" id="itemActiveStatus" readonly name="itemActiveStatus" onclick="return false;"{{$itemDetails->itemActiveStatus == 1 ? 'checked' : ''}}>  
						<label class="custom-control-label" for="itemActiveStatus">Item Status</label>
					</div>
				</div>
				<div class="form-group col-md-4">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" value="1" class="custom-control-input" id="isAdminApproved" readonly name="isAdminApproved" onclick="return false;" {{$itemDetails->isAdminApproved == 1 ? 'checked' : ''}}>
						<label class="custom-control-label" for="isAdminApproved">Approved</label>
					</div>
				</div>
				<div class="form-group col-md-4">
					<div class="custom-control custom-checkbox mr-sm-2">
						<input type="checkbox" value="1" class="custom-control-input" id="isActive" readonly name="isActive" onclick="return false;" {{$itemDetails->isActive == 1 ? 'checked' : ''}}>  
						<label class="custom-control-label" for="isActive">Active</label>
					</div>
				</div>
			</div>
        @endif
      </div>
    </div>
  </div>
@endsection
@push('scripts')
@endpush
