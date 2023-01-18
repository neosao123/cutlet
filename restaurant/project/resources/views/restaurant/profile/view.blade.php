@extends('restaurant.layout.master', ['pageTitle' => 'Restaurant Profile Update'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/restaurant/profile/index.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Restaurant Update</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Restaurant Profile Update</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">  
			
            </div>
        </div>
    </div>
	<div class="container-fluid">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body">
					<h4 class="card-title">Your Profile</h4>
					<h6 class="card-subtitle">Detailed Information, Password Update etc...</h6>
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li class="nav-item"> <a class="nav-link active show" data-toggle="tab" href="#info" role="tab" aria-selected="true"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"> Restaurant Info</span></a> </li>
						<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#pswd" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="ti-user"></i></span> <span class="hidden-xs-down"> Password Change</span></a> </li>
						<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#bnk" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"> Bank Details</span></a> </li>
						<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#doc" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"> Document Details</span></a> </li>
						<li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#setting" role="tab" aria-selected="false"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"> Packaging & GST</span></a> </li>
					</ul>
					<!-- Tab panes -->
					<div class="tab-content tabcontent-border">
						<div class="tab-pane active show" id="info" role="tabpanel">
							<div class="p-20">
								<h4 class="card-title">Profile Details</h4>
								<hr/>
									<div class="form-row">
										@if(!empty($restaurant->entityImage))
											<div class="col-md-4 mb-3 text-center">
												<label for="entityImage"> Entity Image :</label>
												<div class="controls mt-1">
													<img src="{{env('IMG_URL').'uploads/restaurant/restaurantimage/'.$restaurant->entityImage}}" id="entityImageShow" alt="Entity Image" height="120" width="120">
												</div>
											</div>
										@endif
									</div>
									<div class="form-row">
										
										<div class="col-md-4 mb-3">
											<label for="firstName">First Name :</label>
											<input type="text" id="firstName" value="{{$restaurant->firstName}}" class="form-control" required readonly>
										</div>
										
										<div class="col-md-4 mb-3">
											<label for="middleName">Middle Name : </label>
											<input type="text" id="middleName"  value="{{$restaurant->middleName}}" class="form-control" readonly>
										</div>
										
										<div class="col-md-4 mb-3">
											<label for="lastName">Last Name :</label>
											<input type="text" id="lastName" value="{{$restaurant->lastName}}" class="form-control" readonly>
										</div>
									</div>
									
									<div class="form-row">
										<div class="col-md-12 mb-3">
											<label for="entityName">Entity Name : </label>
											<input type="text" id="entityName" value="{{$restaurant->entityName}}" class="form-control" readonly>
										</div>
									</div>
									
									<div class="form-row">
										<div class="col-md-12 mb-3">
											<label for="address">Address : </label>
											<input type="text" id="address"  value="{{$restaurant->address}}" class="form-control" readonly>
										</div>
									</div>
									
									<div class="form-row">
										<div class="col-md-4 mb-3">
											<label for="ownerContact1">Owner Contact :</label>
											<input type="text" id="ownerContact1"  value="{{$restaurant->ownerContact}}" class="form-control" required readonly>
										</div>
										
										<div class="col-md-4 mb-3">
											<label for="entityContact">Entity Contact : </label>
											<input type="text" id="entityContact"  value="{{$restaurant->entityContact}}" class="form-control" readonly>
										</div>
										
										<div class="col-md-4 mb-3">
											<label for="entitycategoryCode">Entity Category :</label>
											<select id="entitycategoryCode" name="entitycategoryCode" class="select2 form-control" style="width: 100%; height:36px;" disabled>
												   <option value="">Select</option>
												   @if($entitycategory->count() > 0)
													@foreach ($entitycategory as $entitycategoryItem)
														<option value="{{ $entitycategoryItem->code }}" @if($restaurant->entitycategoryCode == $entitycategoryItem->code) selected @endif>{{$entitycategoryItem->entityCategoryName }}</option>
													@endforeach
												   @endif
											</select>
										</div>
										 
										<div class="col-md-4 mb-3">
											<label for="entitycategoryCode">Profile Active Status :</label>
											<?php 
												if($restaurant->isActive == "1"){
													 
													echo "<span class='label label-sm label-success'>Active</span>";
													}else{ 
													echo "<span class='label label-sm label-warning'>Inactive</span>";
												}
											?>
										</div>										
									</div> 
									<div class="form-row mb-3">
										<div id="myMap">
										</div>
									</div>

									<div class="form-row">
										<div class="col-md-6 mb-3">
											<label for="latitude"> Latitude : </label>
											<input type="text" id="latitude" name="latitude" value="{{$restaurant->latitude}}" class="form-control" readonly>
										</div>
										
										<div class="col-md-6 mb-3">
											<label for="longitude"> Longitude :</label>
											<input type="text" id="longitude" name="longitude" value="{{$restaurant->longitude}}" class="form-control" readonly>
										</div> 
									</div>
									
									<div class="form-row">
										<div class="col-md-12 mb-3">
											<label for="cuisineCode"> Type of cuisines served :</label></br>					            
											<select class="form-control select2" name="cuisineCode[]" required id="cuisineCode" multiple="multiple" readonly data-border-color="primary" data-border-variation="accent-2" style="width:100%">
											  @if($cuisines->count() > 0)
												@foreach ($cuisines as $cuisinesItem)
													@if(strpos($cuisines_entry, $cuisinesItem->code) !== false)
														<option value="{{ $cuisinesItem->code }}" selected >{{$cuisinesItem->cuisineName }}</option>
													@else
													<option value="{{ $cuisinesItem->code }}"  >{{$cuisinesItem->cuisineName }}</option>
													@endif
												@endforeach
											  @endif
										</select>
										</div>
									</div>
							</div>
						</div>
						<div class="tab-pane p-20" id="pswd" role="tabpanel">
							<div class="p-20">
								<h4 class="card-title">Update Credentials</h4>
								<hr/>						
								<form  method="post" id="passwordForm" enctype="multipart/form-data" action="{{url('/profile/update')}}">
									@csrf
									<div class="form-row"> 
										<input type="hidden" id="code" name="code" value="{{$restaurant->code}}" class="form-control" readonly> 
										<div class="col-md-6 mb-3">
											<label for="ownerContact">Owner Contact :<b style="color:red">*</b></label>
											<input type="number" id="ownerContact" name="ownerContact" class="form-control" value="{{$restaurant->ownerContact}}" required>
											<small class="text-danger" id="mobile_error"></small>
											<span class="text-danger text-center">{{ $errors->first('ownerContact') }}</span>
										</div>
									</div>	
									<div class="form-row">
										<div class="col-md-6 mb-3">
											<label for="password">Password : <b style="color:red">*</b></label>
											<input type="password" id="password" name="password" class="form-control" value="" required>
											<small class="text-danger" id="password_validation_error"></small>
											<span class="text-center text-danger" id="password_error" style="font-size: 75%;color: #e63757;"></span><br>
											<span class="text-danger text-center">{{ $errors->first('password') }}</span>
										</div>
										
										<div class="col-md-6 mb-3">
											<label for="confirmPassword">Confirm Password: <b style="color:red">*</b></label>
											<input type="password" id="password_confirmation" name="password_confirmation" class="form-control" value="" required>
										    <span class="text-danger text-center">{{ $errors->first('password') }}</span>
										</div>
									</div>	 
									<div class="text-xs-right">
										<button type="submit" class="btn btn-success" id="updatePassword">Submit</button> 
									</div>
								</form>
							</div>
						</div>
						<div class="tab-pane p-20" id="bnk" role="tabpanel">
							<div class="p-20">
								<h4 class="card-title">Bank Details</h4>
								<hr/>
									@php
										$bankDetails = $restaurant->bankDetails;
										$beneficiaryName = $bankName = $accountNumber = $ifscCode = "";
										if ($bankDetails != "") {
											$bankDetails = json_decode($bankDetails);
											$beneficiaryName = $bankDetails->beneficiaryName;
											$bankName = $bankDetails->bankName;
											$accountNumber = $bankDetails->accountNumber;
											$ifscCode = $bankDetails->ifscCode;
										}
									@endphp
									<div class="form-row">
										<div class="col-md-6 mb-3">
											<label for="beneficiaryName"> Beneficiary Name : </label>
											<input type="text" id="beneficiaryName" name="beneficiaryName" value="{{ $beneficiaryName }}" class="form-control" readonly required>
										</div>
										
										<div class="col-md-6 mb-3">
											<label for="bankName"> Name of Bank :</label>
											<input type="text" id="bankName" name="bankName" value="{{ $bankName }}" class="form-control" readonly>
										</div>
										
										<div class="col-md-6 mb-3">
											<label for="accountNumber"> Account Number :</label>
											<input type="text" id="accountNumber" name="accountNumber" value="{{ $accountNumber }}" class="form-control" readonly>
										</div>
										
										<div class="col-md-6 mb-3">
											<label for="ifscCode"> IFSC Code :</label>
											<input type="text" id="ifscCode" name="ifscCode" value="{{ $ifscCode }}" class="form-control" readonly>
										</div>
									</div>
							</div>
						</div>
						<div class="tab-pane p-20" id="doc" role="tabpanel">
							<div class="p-20">
								<h4 class="card-title">Uploaded Document Details</h4>
								<hr/> 
									<div class="form-row">
										<div class="col-md-6 mb-3">
											<label for="fssaiNumber"> FSSAI Number : </label>
											<input type="text" id="fssaiNumber" name="fssaiNumber" value="{{$restaurant->fssaiNumber}}" class="form-control" readonly required>
										</div>
										
										<div class="col-md-6 mb-3">
											<label for="gstNumber"> GST Number :</label>
											<input type="text" id="gstNumber" name="gstNumber" value="{{$restaurant->gstNumber}}" class="form-control" readonly>
										</div>
									</div>
									<div class="form-row"> 
										@if(!empty($restaurant->fssaiImage!=""))
											<div class="col-md-4 mb-3 text-center">
												<label for="fssaiImage"> FSSAI Image :</label>
												<div class="controls">
													<img src="{{env('IMG_URL').'uploads/restaurant/fssaiimage/'.$restaurant->fssaiImage}}" id="fssaiImageShow" alt="FSSAI Image" height="120" width="120">
												</div>
											</div>
										@endif
										@if(!empty($restaurant->gstImage!=""))
											<div class="col-md-4 mb-3 text-center">
												<label for="gstImage"> GST Image :</label> 
												<div class="controls">
													<img src="{{env('IMG_URL').'uploads/restaurant/gstimage/'.$restaurant->gstImage}}" id="gstImageShow" alt="GST Image" height="120" width="120">
												</div>
											</div>
										@endif
									</div>
							</div>
						</div>
						<div class="tab-pane p-20" id="setting" role="tabpanel">
							<div class="p-20">
								<h4 class="card-title">Configuration Settings</h4>
								<hr/>  
									<div class="row">
										<div class="col-md-7 mb-3">
											<label>GST Applicable Type : </label>
											<div class="custom-control custom-radio">
												<input type="radio" id="gstApplicableNo" readonly name="gstApplicable" value="NO" {{$restaurant->gstApplicable=="NO"?'checked':''}} class="custom-control-input">
												<label class="custom-control-label" for="gstApplicableNo">No (Not Applicable)</label>
											</div>	
											<div class="custom-control custom-radio">
												<input type="radio" id="gstApplicableYes" readonly name="gstApplicable" value="YES" {{$restaurant->gstApplicable=="YES"?'checked':''}} class="custom-control-input">
												<label class="custom-control-label" for="gstApplicableYes">Yes (Applicable)</label>
											</div>											
										</div>
										<div class="col-md-5 mb-3">
											<label for="gstPercent">GST (%)</label>
											<input type="text" id="gstPercent" maxlength="3" name="gstPercent" class="form-control" readonly value="{{$restaurant->gstPercent<0 || $restaurant->gstPercent==null  ? 0 .' %':$restaurant->gstPercent. ' %'}}">
										</div>
									</div>
									<form class="needs-validation" method="post" action="{{ url('/configUpdate')}}" enctype="multipart/form-data" novalidate>
									 @csrf
										<div class="row"> 
											<input name="configVendorCode" value="{{$restaurant->code}}" readonly type="hidden">
											<div class="col-md-7 mb-3">
												<label>Delivery Packaging Type : <b style="color:red">*</b></label>
												<div class="custom-control custom-radio">
													<input type="radio" id="productPacking" name="packagingType" value="PRODUCT" {{$restaurant->packagingType=="PRODUCT"?'checked':''}} class="custom-control-input">
													<label class="custom-control-label" for="productPacking">Product Wise</label>
												</div>
												<div class="custom-control custom-radio">
													<input type="radio" id="cartPacking" name="packagingType" value="CART" {{$restaurant->packagingType=="CART"?'checked':'' }} class="custom-control-input">
													<label class="custom-control-label" for="cartPacking">Cart Wise</label>
												</div>
											</div>
											<div class="col-md-5 mb-3" id="cartPack" style="display:{{$restaurant->packagingType=='CART' ? 'block': 'none'}}">
												<label>Delivery Packing Price</label>
												<input type="number" id="cartPackagingPrice" maxlength="3" name="cartPackagingPrice" class="form-control" {{$restaurant->packagingType=="CART"? 'required':''}} value="{{$restaurant->cartPackagingPrice}}">
												<span class="text-danger text-center">{{ $errors->first('cartPackagingPrice') }}</span>
											</div>											
										</div> 
										<div class="row"> 
											<div class="text-xs-right">
												<button type="submit" class="btn btn-success" id="updateConfig">Submit</button> 
											</div>
										</div>
									</form>
							</div>
						</div>
					</div> 
				</div>
			</div>
		</div>
	</div>
@endsection
@push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBGE-XRIa2IwnWbdbmEPM-eCmGp8AnnOik"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/restaurant/profile/index.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
  @if(Session::has('status'))
		  <script type="text/javascript">
          notification = @json(session()->pull("status"));
		   if(notification.status =='true')
			{  
				toastr.success(notification.message, 'Profile', { "progressBar": true });    
				location.reload();
			}
			else
			{
				toastr.error(notification.message, 'Profile', { "progressBar": true });
				location.reload();
			}
		 </script>
@endif
@endpush