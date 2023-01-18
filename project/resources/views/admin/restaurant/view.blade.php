@extends('admin.layout.master', ['pageTitle' => 'Restaurant View'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/admin/restaurant/index.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Restaurant View</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Restaurant View</a></li>
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
                        <h5 class="mb-0" data-anchor="data-anchor">Restaurant View</h5>
                    </div>
                    <div class="col-sm-2">
                        <a href="{{ url('partner/list') }}" class="btn btn-outline-primary btn-sm"> Back </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form>
                    <input type="hidden" name="code" value="{{ $restaurant->code }}" id="code" readonly>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="firstName">First Name :</label>
                            <input type="text" id="firstName" name="firstName" value="{{ $restaurant->firstName }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="middleName">Middle Name : </label>
                            <input type="text" id="middleName" name="middleName" value="{{ $restaurant->middleName }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="lastName">Last Name :</label>
                            <input type="text" id="lastName" name="lastName" value="{{ $restaurant->lastName }}" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="entityName">Restaurant Name : </label>
                            <input type="text" id="entityName" name="entityName" value="{{ $restaurant->entityName }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="entityName">Restaurant Email :</label>
                            <input type="text" id="entityEmail" name="entityEmail" value="{{ $restaurant->email }}" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="ownerContact">Owner Contact :</label>
                            <input type="text" id="ownerContact" name="ownerContact" value="{{ $restaurant->ownerContact }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="entityContact">Restaurant Contact : </label>
                            <input type="text" id="entityContact" name="entityContact" value="{{ $restaurant->entityContact }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="entitycategoryCode">Restaurant Category :</label>
                            <select id="entitycategoryCode" name="entitycategoryCode" class="select2 form-control" style="width: 100%; height:36px;" disabled>
                                <option value="">Select</option>
                                @if ($entitycategory->count() > 0)
                                    @foreach ($entitycategory as $entitycategoryItem)
                                        <option value="{{ $entitycategoryItem->code }}" @if ($restaurant->entitycategoryCode == $entitycategoryItem->code) selected @endif>{{ $entitycategoryItem->entityCategoryName }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-4 mb-3">
                            <label for="businessCode">Business Type:<b style="color:red">*</b></label>
                            <select class="form-control" style="width: 100%; height:36px;" name="businesstype" id="businesstype"disabled>
                                <option value="">Select</option>
                                @if ($businesstype->count() > 0)
                                    @foreach ($businesstype as $businesstypeItem)
                                        <option value="{{ $businesstypeItem->code }}" @if ($restaurant->businessTypeCode == $businesstypeItem->code) selected @endif>{{ $businesstypeItem->businessType }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="cityCode">City:</label>
                            <select class="form-control" style="width: 100%; height:36px;" name="city" id="city" disabled>
                                <option value="">Select</option>
                                @if ($city->count() > 0)
                                    @foreach ($city as $cityItem)
                                        <option value="{{ $cityItem->code }}" @if ($restaurant->cityCode == $cityItem->code) selected @endif>{{ $cityItem->cityName }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="addressCode">Area:</label>
                            <select id="addressCode" name="addressCode" class="form-control" disabled>
                                <option value="">Select</option>
                                @if ($address->count() > 0)
                                    @foreach ($address as $addressItem)
                                        <option value="{{ $addressItem->code }}" @if ($restaurant->addressCode == $addressItem->code) selected @endif>{{ $addressItem->place }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-12 mb-3">
                            <label for="address">Address : </label>
                            <textarea type="text" id="address" name="address" class="form-control" rows="4" readonly>{{ trim($restaurant->address) }}</textarea>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="fssaiNumber"> FSSAI Number : </label>
                            <input type="text" id="fssaiNumber" name="fssaiNumber" value="{{ $restaurant->fssaiNumber }}" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="form-row mb-3">
                        <div id="myMap">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="latitude"> Latitude : </label>
                            <input type="number" id="latitude" name="latitude" class="form-control" step="any" readonly value="{{ $restaurant->latitude }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="longitude"> Longitude : </label>
                            <input type="number" id="longitude" name="longitude" class="form-control" step="any" readonly value="{{ $restaurant->longitude }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="cuisineCode">Cuisines you serve : </label>
                            <select class="form-control select2" name="cuisineCode[]" required id="cuisineCode" multiple="multiple" disabled data-border-color="primary"
                                data-border-variation="accent-2" style="width:100%">
                                @if ($cuisines->count() > 0)
                                    @foreach ($cuisines as $cuisinesItem)
                                        @if (strpos($cuisines_entry, $cuisinesItem->code) !== false)
                                            <option value="{{ $cuisinesItem->code }}" selected>{{ $cuisinesItem->cuisineName }}</option>
                                        @else
                                            <option value="{{ $cuisinesItem->code }}">{{ $cuisinesItem->cuisineName }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        @if (!empty($restaurant->entityImage))
                            <div class="col-md-4 mb-3">
                                <div class="el-element-overlay">
                                    <div class="card">
                                        <div class="el-card-item">
                                            <div class="el-card-avatar el-overlay-1"> <img id="entityImageShow" src="{{ url('uploads/restaurant/restaurantimage/' . $restaurant->entityImage) }}"
                                                    alt="product photo File">
                                                <label for="entityImage" class="mt-3"> Restauarnt Image :</label>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if (!empty($restaurant->fssaiImage))
                            <div class="col-md-4 mb-3">
                                <div class="el-element-overlay">
                                    <div class="card">
                                        <div class="el-card-item">
                                            <div class="el-card-avatar el-overlay-1"> <img id="fssaiImageShow" src="{{ url('uploads/restaurant/fssaiimage/' . $restaurant->fssaiImage) }}"
                                                    alt="product photo File">
                                                <label for="fssaiImage" class="mt-3"> FSSAI Image :</label>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <h4> Configuration Settings </h4>
                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label>Delivery Packaging Type : </label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="productPacking" onclick="return false"; name="packagingType" value="PRODUCT" class="custom-control-input"
                                    {{ $restaurant->packagingType == 'PRODUCT' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="productPacking">Product Wise</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="cartPacking" onclick="return false"; name="packagingType" value="CART" class="custom-control-input"
                                    {{ $restaurant->packagingType == 'CART' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="cartPacking">Cart Wise</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3" id="cartPack" style="display:{{ $restaurant->packagingType == 'CART' ? 'block' : 'none' }}">
                            <label for="cartPackagingPrice">Delivery Packaging Price </label>
                            <input type="number" id="cartPackagingPrice" maxlength="3" name="cartPackagingPrice" value="{{ $restaurant->cartPackagingPrice }}" class="form-control" readonly>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-7 mb-3">
                            <label>GST Applicable Type : </label>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="gstApplicableNo" name="gstApplicable" value="NO" class="custom-control-input" {{ $restaurant->gstApplicable == 'NO' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="gstApplicableNo">No (Not Applicable)</label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="gstApplicableYes" name="gstApplicable" value="YES" class="custom-control-input"
                                    {{ $restaurant->gstApplicable == 'YES' ? 'checked' : '' }}>
                                <label class="custom-control-label" for="gstApplicableYes">Yes (Applicable)</label>
                            </div>
                        </div>
                        <div class="col-md-5 mb-3" id="gstPercentDiv" style="display:{{ $restaurant->gstApplicable == 'YES' ? 'block' : 'none' }}">
                            <label for="gstPercent">GST (%): </label>
                            <input type="number" step="0.01" maxlength="4" readonly id="gstPercent" maxlength="3" name="gstPercent" class="form-control"
                                value="{{ $restaurant->gstPercent }}" {{ $restaurant->gstApplicable == 'YES' ? 'required' : '' }}>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="panNumber"> PAN Number :</label>
                            <div class="controls">
                                <input type="text" id="panNumber" name="panNumber" value="{{ $restaurant->panNumber }}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gstNumber"> GST Number :</label>
                            <div class="controls">
                                <input type="text" id="gstNumber" name="gstNumber" value="{{ $restaurant->gstNumber }}" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="commission"> Commission(%):</label>
                            <div class="controls">
                                <input type="number" step="0.01" maxlength="4" id="commission" name="commission" value="{{ $restaurant->commission }}" class="form-control" readonly>
                            </div>
                        </div>
                        @if (!empty($restaurant->gstImage))
                            <div class="el-element-overlay">
                                <div class="col-md-4 mb-3">
                                    <div class="card">
                                        <div class="el-card-item">
                                            <div class="el-card-avatar el-overlay-1"> <img id="gstImageShow" src="{{ url('uploads/restaurant/gstimage/' . $restaurant->gstImage) }}"
                                                    alt="product photo File">
                                                <label for="gstImage" class="mt-3"> GST Image :</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <?php
                    $bankDetails = $restaurant->bankDetails;
                    $beneficiaryName = $bankName = $accountNumber = $ifscCode = '';
                    if ($bankDetails != '') {
                        $bankDetails = json_decode($bankDetails);
                        $beneficiaryName = $bankDetails->beneficiaryName;
                        $bankName = $bankDetails->bankName;
                        $accountNumber = $bankDetails->accountNumber;
                        $ifscCode = $bankDetails->ifscCode;
                    }
                    ?>
                    <h4> Bank Details </h4>
                    <div class="form-row">
                        <div class="col-md-6 mb-3">
                            <label for="beneficiaryName"> Beneficiary Name : </label>
                            <input type="text" id="beneficiaryName" name="beneficiaryName" value="{{ $beneficiaryName }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="bankName"> Name of Bank :</label>
                            <input type="text" id="bankName" name="bankName" value="{{ $bankName }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="accountNumber"> Account Number :</label>
                            <input type="number" id="accountNumber" name="accountNumber" value="{{ $accountNumber }}" class="form-control" readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="ifscCode"> IFSC Code :</label>
                            <input type="text" id="ifscCode" name="ifscCode" value="{{ $ifscCode }}" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="custom-control custom-checkbox mr-sm-2">
                            <label>Active Status</label>
                            <input type="checkbox" value="1" class="custom-control-input" id="isActive" name="isActive" onclick="return false;" value="1"
                                {{ $restaurant->isActive == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isActive">Active</label>
                        </div>
                        <div class="custom-control custom-checkbox mr-sm-2">
                            <label>Serviceable Status</label>
                            <input type="checkbox" value="1" class="custom-control-input" id="isServiceable" onclick="return false;" name="isServiceable" value="1"
                                {{ $restaurant->isServiceable == 1 ? 'checked' : '' }}>
                            <label class="custom-control-label" for="isServiceable">Serviceable ?</label>
                        </div>
                    </div>
                    <!--@if ($tags->count() > 0)
    @php
        $str1 = '';
        if ($restaurant->tagCode == null || $restaurant->tagCode == '') {
            $str1 = 'checked';
        }
    @endphp
                  <!--<div class="form-group">
                   <div class="row">
                    <div class="col-sm-2 mb-1">
                     <h6><b> Tags:</b></h6>
                    </div>
                   </div>
                   <div class="row" style="margin-left:35px;">
                    <div class="col-sm-3 mb-3">
                     <div class="custom-control custom-radio custom-control-inline">
                      <input type="radio" class="custom-control-input" {{ $str1 }} onclick="return false";  id="tagSection" name="tagCode" value="1">
                      <label class="custom-control-label"  for="tagSection"><b>No Tag</b></label>
                     </div>
                    </div>
                    @foreach ($tags as $tagItem)
    @php
        $str = '';
        if ($tagItem->code == $restaurant->tagCode) {
            $str = 'checked';
        }
    @endphp
                    <div class="col-sm-3 mb-3">
                     <div class="custom-control custom-radio custom-control-inline">
                      <input type="radio" onclick="return false"; class="custom-control-input" id="tagSection{{ $tagItem->code }}" {{ $str }} name="tagCode" value="{{ $tagItem->code }}">
                     <label class="custom-control-label" style="color:{{ $tagItem->tagColor }}" for="tagSection{{ $tagItem->code }}"><b>{{ $tagItem->tagTitle }}</b></label>
                     </div>
                    </div>
    @endforeach
                   </div>
                  </div>
    @endif		-->
            </div>
            </form>
        </div>
    </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/init_site/admin/restaurant/view.js') }}"></script>
@endpush
