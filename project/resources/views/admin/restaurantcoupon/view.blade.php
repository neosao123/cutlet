@extends('admin.layout.master', ['pageTitle'=>"View Restaurant Offer"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/restaurantcoupon/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/summernote/dist/summernote-bs4.css') }}" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
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
              <li class="breadcrumb-item"><a href="#">Restaurant Offer View </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">View Restaurant Offer</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('restaurantCoupon/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        @if ($restaurantcoupons)
			@foreach($restaurantcoupons as $restaurantcoupon)
          <form action="{{ url('restaurantOffer/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
            @csrf
            <input type="hidden" name="code" value="{{ $restaurantcoupon->code }}" readonly>
				<div class="form-row">
					<div class="col-md-12 mb-3">
						<input value="{{ $restaurantcoupon->code}}" id="code" name="code" readonly hidden>
						<label for="restaurantCode">Restaurant:</label>
						<input type="hidden" id="restaurantCode" name="restaurantCode" value="{{ $restaurantcoupon->restaurantCode }}"  readonly>
						<input type="text" id="entityName" name="entityName" value="{{ $restaurantcoupon->entityName }}" readonly class="form-control">
					</div> 
				</div>
				<div class="form-row">
					<div class="col-md-4 mb-3">
						<label for="coupanCode">Coupon Code :<b style="color:red">*</b></label>
						<input type="text" id="couponCode" name="couponCode" value="{{ $restaurantcoupon->couponCode }}" readonly class="form-control" required readonly>
					</div> 
					<div class="col-md-4 mb-3">
						<label for="offerType">Offer Type :<b style="color:red">*</b></label>
						<select id="offerType" name="offerType" class="form-control" required readonly>
							<option value="">Select type</option>
							<option value="flat" {{ $restaurantcoupon->offerType == 'flat' ? 'selected':'' }}>Flat</option>
							<option value="cap" {{ $restaurantcoupon->offerType == 'cap' ? 'selected':'' }}>Cap</option>
						</select>
					</div>
				    <div class="col-md-4 mb-3">
						<label for="perUserLimit">Per User Limit : <b style="color:red">*</b></label>
						<input type="number" id="perUserLimit" name="perUserLimit" readonly value="{{ $restaurantcoupon->perUserLimit }}" class="form-control" required>
					</div> 
				</div>
				<div class="form-row">
					<div class="col-md-4 mb-3">
						<label for="minimumAmount"> Minimum Amount : <b style="color:red">*</b></label>
						<input type="number" id="minimumAmount" readonly value="{{ $restaurantcoupon->minimumAmount }}" name="minimumAmount" class="form-control" required>
					</div> 
					<div class="col-md-4 mb-3 d-none" id="discountDiv">
						<label for="discount"> Discount (%): <b style="color:red">*</b></label>
						<input type="number" id="discount" readonly value="{{ $restaurantcoupon->discount }}" name="discount" class="form-control" required>
					</div> 
					<div class="col-md-4 mb-3" id="capDiv">
						<label for="capLimit"> Cap Limit : <b style="color:red">*</b></label>
						<input type="number" id="capLimit" readonly value="{{ $restaurantcoupon->capLimit }}" name="capLimit" class="form-control">
					</div> 	
					<div class="col-md-4 mb-3 d-none" id="flatAmountDiv">
                        <label for="flatAmount"> Flat Amount: <b style="color:red">*</b></label>
                        <input type="number" id="flatAmount" readonly name="flatAmount" class="form-control" value="{{ $restaurantcoupon->flatAmount }}">
                    </div>
				</div>
				<div class="form-row">
    				<div class="col-md-12 mb-3">
    					<label for="termsAndConditions">Terms & Conditions : </label>
    					<textarea class="form-control" id="termsAndConditions" readonly name="termsAndConditions" place-holder="Terms and Conditions..">{{ $restaurantcoupon->termsAndConditions }}</textarea>
    				</div>
				</div>
				@php 
					$startDate='';
					$endDate='';
					if($restaurantcoupon->startDate!='' &&  $restaurantcoupon->startDate!=NULL){
						$startDate = date('d/m/Y',strtotime($restaurantcoupon->startDate));
					}
					if($restaurantcoupon->endDate!='' &&  $restaurantcoupon->endDate!=NULL){
						$endDate = date('d/m/Y',strtotime($restaurantcoupon->endDate));
					}
				@endphp 
				<div class="form-row">
					<div class="col-md-12 mb-3">
						<div class="input-daterange input-group">
						<span> <label> Offer Dates :</label> </span>
							<div class="input-daterange input-group" id="productDateRange">
								<input type="text" class="form-control date-inputmask col-sm-5" name="startDate" readonly id="startDate" placeholder="dd/mm/yyyy" value="{{ $startDate }}" required>
								<div class="input-group-append">
								<span class="input-group-text bg-myvegiz b-0 text-white">TO</span>
							  </div>
							<input type="text" class="form-control date-inputmask toDate" name="endDate" readonly id="endDate" placeholder="dd/mm/yyyy" value="{{ $endDate }}" required>
							</div>
						</div>
					</div>
				</div>
				<div class="form-row">
					<div class="col-md-2 mb-3">
						<label for="applyOn">Apply On:<b style="color:red">*</b></label>
						<select id="applyOn" name="applyOn" class="form-control" readonly >
							<option value="cart" {{ $restaurantcoupon->applyOn == 'cart' ? 'selected':'' }}>Cart</option>
							<option value="item" {{ $restaurantcoupon->applyOn == 'item' ? 'selected':'' }}>Item</option>
						</select>
					</div>
					<div class="col-md-10 mb-3 d-none" id="itemDiv">
						<label for="offerItems">Restaurant Items: <b style="color:red">*</b> </label>
						<select class="form-control select2" name="offerItems[]" disabled id="offerItems" multiple="multiple" data-parsley-required-message="" data-border-color="primary" data-border-variation="accent-2" style="width:100%">
								@if($items->count() > 0)
									@foreach ($items as $item)
										@if(strpos($itemEntries, $item->code) !== false)
											<option value="{{ $item->code }}" selected {{ (is_array(old('offerItems')) and in_array($item->code, old('offerItems'))) ? 'selected' : '' }}>{{$item->itemName }}</option>
										@else
											<option value="{{ $item->code }}" {{ (is_array(old('offerItems')) and in_array($item->code, old('offerItems'))) ? 'selected' : '' }}>{{$item->itemName }}</option>
										@endif
									@endforeach
								@endif
						</select>
					</div>
				</div>
				<div class="row">
					<div class="form-group col-md-3">
                            @if($restaurantcoupon->isActive == 1) 
								<span class="badge badge-success">Active</span>
							@else
								<span class="badge badge-danger"> InActive </span>
                            @endif
                            @if($restaurantcoupon->isAdminApproved == 1)
                               <span class='badge badge-success'>Approved</span>							   
						    @else
								<span class="badge badge-danger">Unapproved</span>
						    @endif
													
					</div>
				</div>
              
            </div>
          </form>
		  @endforeach
        @endif
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/restaurantcoupon/index.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/summernote/dist/summernote-bs4.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
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
