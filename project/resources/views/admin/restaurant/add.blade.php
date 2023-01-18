@extends('admin.layout.master', ['pageTitle' => 'Restaurant Add'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/admin/restaurant/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Restaurant Add</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Restaurant Add</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">

            </div>
        </div>
    </div>

    <div class="container-fluid ">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h5 class="mb-0" data-anchor="data-anchor">Restaurant Add</h5>
                    </div>
                    <div class="col-sm-2">
                        <a href="{{ url('partner/list') }}" class="btn btn-outline-primary btn-sm"> Back </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif
                <form action="{{ url('partner/store') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
                    @csrf

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="firstName">First Name :<b style="color:red">*</b></label>
                            <input type="text" id="firstName" name="firstName" value="{{ old('firstName') }}" class="form-control" required data-parsley-required-message="First Name is required"
                                onkeypress="return ValidateAlpha(event)">
                            <span class="text-danger">
                                {{ $errors->first('firstName') }}
                            </span>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="middleName">Middle Name : </label>
                            <input type="text" id="middleName" name="middleName" value="{{ old('middleName') }}" class="form-control" onkeypress="return ValidateAlpha(event)">
                            <span class="text-danger">
                                {{ $errors->first('middleName') }}
                            </span>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="lastName">Last Name :<b style="color:red">*</b></label>
                            <input type="text" id="lastName" name="lastName" value="{{ old('lastName') }}" class="form-control" required data-parsley-required-message="Last Name is required"
                                onkeypress="return ValidateAlpha(event)">
                            <span class="text-danger">
                                {{ $errors->first('lastName') }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-3 mb-3">
                            <label for="entityName">Restaurant Name : <b style="color:red">*</b></label>
                            <input type="text" id="entityName" name="entityName" value="{{ old('entityName') }}" class="form-control" required
                                data-parsley-required-message="Restaurant Name is required" onkeypress="return ValidateAlpha(event)">
                            <span class="text-danger">
                                {{ $errors->first('entityName') }}
                            </span>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="entityName">Restaurant Email : <b style="color:red">*</b></label>
                            <input type="text" id="entityEmail" name="entityEmail" value="{{ old('entityEmail') }}" class="form-control verify_email"
                                pattern="^([a-zA-Z0-9_.-])+@(([a-zA-Z0-9-])+.)+([a-zA-Z0-9]{2,4})+$" data-parsley-type="email" required="" data-parsley-required-message="Email id is required"
                                data-parsley-type-message="Valid Email is required">
                            <span class="text-danger">
                                {{ $errors->first('entityEmail') }}
                            </span>
                            <div class="parsley-errors-list filled" id="shEmail"></div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="entityImage">Restaurant Image :</label>
                            <input type="file" id="entityImage" name="entityImage" class="form-control" accept=".jpg, .jpeg, .png">
                        </div>
                        <div class="col-md-3 mb-3 d-none" id="eImage">
                            <img class="img-thumbnail mb-2" width="100" height="100" id="showEntityImg" src="" data-src="">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="ownerContact">Owner Contact :<b style="color:red">*</b></label>
                            <input type="text" id="ownerContact" name="ownerContact" value="{{ old('ownerContact') }}" class="form-control verify_mobile" onkeypress="return isNumberKey(event)"
                                maxlength="10" required="" data-parsley-required-message="Contact Number is required" pattern="[789][0-9]{9}"
                                data-parsley-pattern-message="Required Valid Mobile Number">
                            <span class="text-danger">
                                {{ $errors->first('ownerContact') }}
                            </span>
                            <div class="parsley-errors-list filled" id="shMobile"></div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="entityContact">Restaurant Contact : </label>
                            <input type="text" id="entityContact" name="entityContact" value="{{ old('entityContact') }}" class="form-control" onkeypress="return isNumberKey(event)"
                                maxlength="10" pattern="[789][0-9]{9}" data-parsley-pattern-message="Required Valid Mobile Number">
                            <span class="text-danger">
                                {{ $errors->first('entityContact') }}
                            </span>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="entitycategoryCode">Restaurant Category :<b style="color:red">*</b></label>
                            <select id="entitycategoryCode" name="entitycategoryCode" class="form-control" style="width: 100%; height:36px;"
                                data-parsley-required-message="Restaurant Category is required" required>
                                <option value="">Select</option>
                                @if ($entitycategory->count() > 0)
                                    @foreach ($entitycategory as $entitycategoryItem)
                                        <option value="{{ $entitycategoryItem->code }}" @if (old('entitycategoryCode') == $entitycategoryItem->code) selected @endif>{{ $entitycategoryItem->entityCategoryName }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('entitycategoryCode') }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="cityCode">Business Type:<b style="color:red">*</b></label>
                            <select class="form-control" style="width: 100%; height:36px;" name="businesstype" id="businesstype" required data-parsley-required-message="Business Type is required">
                                <option value="">Select</option>
                                @if ($businesstype->count() > 0)
                                    @foreach ($businesstype as $businesstypeItem)
                                        <option value="{{ $businesstypeItem->code }}" @if (old('businesstype') == $businesstypeItem->code) selected @endif>{{ $businesstypeItem->businessType }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('businesstype') }}
                            </span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cityCode">City:<b style="color:red">*</b></label>
                            <select class="form-control" style="width: 100%; height:36px;" name="city" id="city" required data-parsley-required-message="City is required">
                                <option value="">Select</option>
                                @if ($city->count() > 0)
                                    @foreach ($city as $cityItem)
                                        <option value="{{ $cityItem->code }}" @if (old('city') == $cityItem->code) selected @endif>{{ $cityItem->cityName }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('city') }}
                            </span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="addressCode">Area:<b style="color:red">*</b></label>
                            <select id="addressCode" name="addressCode" class="form-control" required data-parsley-required-message="Area is required">
                            </select>
                            <span class="text-danger">
                                {{ $errors->first('addressCode') }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label for="address">Address : <b style="color:red">*</b></label>
                            <textarea type="text" id="address" name="address" class="form-control" rows="4" maxlength='800' data-parsley-minlength="10"
                                data-parsley-minlength-message="You need to enter at least 10 characters" data-parsley-trigger="change" required="" data-parsley-required-message="Address is required">{{ old('address') }}</textarea>
                            <span class="text-danger">
                                {{ $errors->first('address') }}
                            </span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="fssaiNumber"> FSSAI Number : <b style="color:red">*</b></label>
                            <input type="text" id="fssaiNumber" name="fssaiNumber" value="{{ old('fssaiNumber') }}" class="form-control" required=""
                                data-parsley-required-message="FSSAI Number is required">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fssaiImage"> FSSAI Image :</label>
                            <div class="controls">
                                <input type="file" id="fssaiImage" name="fssaiImage" class="form-control" accept=".jpg, .jpeg, .png">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 d-none" id="fImage">
                            <img class="img-thumbnail mb-2" width="100" height="100" id="showfssaiImage" src="" data-src="">
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div id="myMap">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="latitude"> Latitude : <b style="color:red">*</b></label>
                            <input type="number" id="latitude" name="latitude" class="form-control" value="{{ old('latitude') }}" step="any" required=""
                                data-parsley-required-message="Latitude is required">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="longitude"> Longitude : <b style="color:red">*</b></label>
                            <input type="number" id="longitude" name="longitude" class="form-control" value="{{ old('longitude') }}" step="any" required=""
                                data-parsley-required-message="Longitude is required">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="cuisineCode">Cuisines you serve : <b style="color:red">*</b> </label>
                            <select class="form-control select2" name="cuisineCode[]" required id="cuisineCode" multiple="multiple" data-parsley-required-message="Cuisine is required"
                                data-border-color="primary" data-border-variation="accent-2" style="width:100%">
                                @if ($cuisines->count() > 0)
                                    @foreach ($cuisines as $cuisinesItem)
                                        <option value="{{ $cuisinesItem->code }}" {{ (is_array(old('cuisineCode')) and in_array($cuisinesItem->code, old('cuisineCode'))) ? 'selected' : '' }}>
                                            {{ $cuisinesItem->cuisineName }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <h4> Configuration Settings </h4>
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label>Delivery Packaging Type : <b style="color:red">*</b></label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="productPacking" name="packagingType" value="PRODUCT" class="custom-control-input">
                                <label class="custom-control-label" for="productPacking">Product Wise</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="cartPacking" name="packagingType" value="CART" class="custom-control-input" checked>
                                <label class="custom-control-label" for="cartPacking">Cart Wise</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3" id="cartPack" style="display:block">
                            <label for="cartPackagingPrice">Delivery Packaging Price <b style="color:red">*</b></label>
                            <input type="number" id="cartPackagingPrice" name="cartPackagingPrice" value="{{ old('cartPackagingPrice') }}" class="form-control" required=""
                                data-parsley-required-message="Delivery Packaging Price is required">
                            <span class="text-danger">
                                {{ $errors->first('cartPackagingPrice') }}
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label>GST Applicable Type : </label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="gstApplicableNo" checked name="gstApplicable" value="NO" class="custom-control-input">
                                <label class="custom-control-label" for="gstApplicableNo">No (Not Applicable)</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="gstApplicableYes" name="gstApplicable" value="YES" class="custom-control-input">
                                <label class="custom-control-label" for="gstApplicableYes">Yes (Applicable)</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3" id="gstPercentDiv" style="display:none">
                            <label for="gstPercent">GST (%): <b style="color:red">*</b></label>
                            <input type="number" step="0.01" maxlength="5" id="gstPercent" maxlength="3" name="gstPercent" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="panNumber"> PAN Number :</label>
                            <div class="controls">
                                <input type="text" id="panNumber" name="panNumber" value="{{ old('panNumber') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gstNumber"> GST Number :</label>
                            <div class="controls">
                                <input type="text" id="gstNumber" name="gstNumber" value="{{ old('gstNumber') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="commission"> Commission(%):</label>
                            <div class="controls">
                                <input type="number" step="0.01" maxlength="5" id="commission" name="commission" value="{{ old('commission') }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <label for="gstImage"> GST Image :</label>
                            <div class="controls">
                                <input type="file" id="gstImage" name="gstImage" class="form-control" accept=".jpg, .jpeg, .png">
                            </div>
                        </div>
                        <div class="col-md-3 mb-3 d-none" id="gImage">
                            <img class="img-thumbnail mb-2" width="100" height="100" id="showgstImage" src="" data-src="">
                        </div>
                    </div>

                    <h4> Bank Details </h4>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="beneficiaryName"> Beneficiary Name : </label>
                            <input type="text" id="beneficiaryName" name="beneficiaryName" value="{{ old('beneficiaryName') }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bankName"> Name of Bank :</label>
                            <input type="text" id="bankName" name="bankName" value="{{ old('bankName') }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="accountNumber"> Account Number :</label>
                            <input type="number" id="accountNumber" name="accountNumber" value="{{ old('accountNumber') }}" class="form-control">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="ifscCode"> IFSC Code :</label>
                            <input type="text" id="ifscCode" name="ifscCode" value="{{ old('ifscCode') }}" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mr-sm-2">
                            <input type="checkbox" value="1" class="custom-control-input" id="isActive" name="isActive">
                            <label class="custom-control-label" for="isActive">Active</label>
                        </div>
                        <div class="custom-control custom-checkbox mr-sm-2">
                            <input type="checkbox" value="1" class="custom-control-input" id="isServiceable" name="isServiceable">
                            <label class="custom-control-label" for="isServiceable">Is Serviceable ?</label>
                        </div>
                    </div>
                    <!--@if ($tags->count() > 0)
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
               @foreach ($tags as $tagItem)
    <div class="col-sm-3 mb-3">
                <div class="custom-control custom-radio custom-control-inline">
                 <input type="radio" class="custom-control-input" id="tagSection{{ $tagItem->code }}" name="tagCode" value="{{ $tagItem->code }}">
                 <label class="custom-control-label" style="color:{{ $tagItem->tagColor }}" for="tagSection{{ $tagItem->code }}"><b>{{ $tagItem->tagTitle }}</b></label>
                 
                </div>
               </div>
    @endforeach
              </div>
             </div>
    @endif-->


                    <div class="col-sm-12 form-group">
                        <button class="btn btn-success" id="restaurant"> Submit </button>
                        <button type="reset" class="btn btn-danger">Reset</button>
                    </div>
            </div>
            </form>
        </div>
    </div>
    </div>


@endsection
@push('scripts')  	
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/init_site/admin/restaurant/add.js') }}" defer></script>
    <script type="text/javascript" src="{{ asset('assets/init_site/admin/restaurant/geomap.js') }}" defer></script>
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
