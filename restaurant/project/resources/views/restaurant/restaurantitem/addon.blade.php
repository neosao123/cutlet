@extends('restaurant.layout.master', ['pageTitle'=>"Add Restaurant Item Addon"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/restaurant/restaurantitem/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
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
              <li class="breadcrumb-item"><a href="#">Customize addon </a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid col-md-10">
    <div class="card">
      <div class="card-header">
        <div class="row">
         <div class="col-sm-10">
            <h5 class="mb-0" data-anchor="data-anchor">Item Details</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('restaurantItems/list')}}"> Back </a>
          </div>
        </div>
      </div>
	  <form>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        @if ($itemDetails)
            <div class="form-row">
				<div class="col-md-7 mb-3">
					<label for="itemName">Item Name :</label>
					<input type="text" id="itemName" name="itemName" value="{{ $itemDetails->itemName}}" class="form-control"  readonly>
					<input type="hidden" id="itemCode" name="itemCode" value="{{ $itemDetails->code}}" class="form-control"  readonly>
				</div> 
				<div class="col-md-5 mb-3">
					<label for="salePrice">Sale Price:</label>
					<input type="text" id="salePrice" name="salePrice" value="{{ $itemDetails->salePrice}}" class="form-control"  readonly>	
				</div> 
			</div>
		@endif
	</div>
	</form>
</div>
<div class="card">
      <div class="card-header">
        <div class="row">
         <div class="col-sm-10">
            <h5 class="mb-0" data-anchor="data-anchor">Customize Addons</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('restaurantItems/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        <div class="row">
		    <div class="col-sm-12">
		        <div class="row">
   					<div class="col-sm-5 mb-3">
   						<label for="categoryTitle">Addon Category:</label>
   						<input id="categoryTitle" name="categoryTitle" class="form-control">
   						<input readonly type="hidden" id="customizedCategoryCode" name="customizedCategoryCode">
   						</select>	
   					</div>
   					<div class="col-sm-5  mb-3"> 
						<label for="categoryType">Addon/Choice:</label>
						<select id="categoryType" name="categoryType" class="form-control">
							<option value="">Select Type</option>
							<option value="choice">Choice</option>
							<option value="addon">Add On</option>
						</select>	
					</div>
					<div class="col-sm-2  mb-3"> 
						<label for="categoryType">Enabled:</label>
						<div class="custom-control custom-checkbox"> 
							<input type="checkbox" value="1" class="custom-control-input" id="isCateEnabled" name="isCateEnabled">
    						<label class="custom-control-label" for="isCateEnabled">Enabled</label>
    					</div>
    				</div>
    				<div class="col-sm-4  mb-3"> 
    					<button type="button" class="btn btn-info btn-sm" id="addCustomizedCategory"><I class="fa fa-plus"></i> Category</button>
    				</div>
    			</div>
		    </div>    
		    <div class="col-sm-12">   
				<div class="row">
					<div class="col-lg-4 col-xl-3">
						<div class="nav flex-column nav-pills"  id="v-pills-tab" role="tablist" aria-orientation="vertical">
						@if($categories)
							@foreach($categories as $c)
								@if($c->categoryType=='choice')
									@php $categoryType = 'Choice' @endphp
								@else
									@php $categoryType = 'Add On' @endphp
								@endif
								 @php $enabled = "Disabled"; @endphp
								 @if($c->isEnabled == 1)
										@php $enabled = "Enabled"; @endphp
								 @endif
								<a class="nav-link" id="{{ $c->code}}-tab" data-toggle="pill" href="#{{ $c->code}}" role="tab" aria-controls="{{ $c->code}}" aria-selected="true"><div class="row"><div class="col-sm-7">{{ $c->categoryTitle}} - {{ $categoryType}} - {{ $enabled}}</div><div class="col-sm-5"><span class="mr-1 ml-2 btn btn-warning btn-xs deleteCustomizedCategory" id="deltab_{{ $c->code}}"><i class="fa fa-trash"></i></span><span class="mr-1 btn btn-danger btn-xs editCustomizedCategory" id="edttab_{{ $c->code}}"><i class="ti-pencil-alt"></i></span></div></div></a>
							@endforeach 
						@endif
						</div>
                    </div>
                    <div class="col-lg-8 col-xl-9">
                        <div class="tab-content" id="v-pills-tabContent"> 
                        @if($categories && $categories->count() > 0)
                           @foreach($categories as $c)
                                <div class="tab-pane fade" id="{{ $c->code}}" role="tabpanel" aria-labelledby="{{ $c->code}}-tab">';
                                <div class="row">
									<div class="col-sm-5 mb-3">
										<label for="subTitle{{ $c->code}}">Addon Title:</label>
										<input id="subTitle{{ $c->code}}" name="subTitle" class="form-control subAddonTitle">  	
									</div>
									<div class="col-sm-5  mb-3"> 
										<label for="price{{ $c->code}}">Price:</label>
										<input type="number" id="price{{ $c->code}}" name="price" class="form-control subAddonPrice"> 	
                            		</div>
                           			<div class="col-sm-2  mb-3"> 
                           				<label for="categoryType">Enabled:</label>
                           				<div class="custom-control custom-checkbox"> 
                           					<input type="checkbox" value="1" class="custom-control-input subAddonEnabled" id="isEnabled{{ $c->code}}" name="isEnabled">
                           					<label class="custom-control-label" for="isEnabled{{ $c->code}}">Addon Enabled</label>
                           				</div>
                           			</div>
                           			<div class="col-sm-4  mb-3"> 
                           				<button type="button" class="btn btn-info btn-sm addSubCategory" data-id="{{ $c->code}}"><I class="fa fa-plus"></i> Sub Category</button>
                           			</div>
                                </div>
                                <table style="width:100%" class="table table-bordered" id="tbl{{$c->code}}"><thead><tr><th>Subtitle</th><th>Price</th><th>Enabled</th><th>#</th></tr></thead><tbody id="tbd{{ $c->code}}">';
                                    @if($categoriesline && $categories->count() > 0)
                                       @foreach($categoriesline as $line)
                                            @if($line->customizedCategoryCode==$c->code)
												@php $enabled = $line->isEnabled==1 ? 'Yes':'No' @endphp
                                                
                                                <tr id="row_{{ $line->code}}"><td>{{ $line->subCategoryTitle }}</td><td>{{ $line->price}}</td>
												<td>
												@if($enabled=='Yes') 
													<span class="badge badge-success">Yes</span>
                                                @else  
													<span class="badge badge-danger">No</span>
												@endif
												</td><td><a class="btn btn-sm btn-danger text-white lineDelete" data-id="{{ $line->code}}"><i class="fa fa-trash"></i></a></td></tr>
                                            @endif
										@endforeach
									@endif
                                    </tbody></table>
                                </div>
                                @endforeach
							@endif
                                </div>
                            </div>
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
	 <script type="text/javascript" src="{{ asset('assets/init_site/restaurant/restaurantitem/addon.js') }}"></script>
	 <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
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
