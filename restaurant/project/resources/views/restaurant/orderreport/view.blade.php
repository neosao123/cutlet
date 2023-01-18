@extends('restaurant.layout.master', ['pageTitle' => 'Order Details'])
@push('styles')
	<link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
     <link href="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Order Details</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">Order Details</a></li>
                        </ol>
                    </nav>
                </div>
            </div>
            <div class="col-7 align-self-center">  
			
            </div>
        </div>
    </div>
	
	 <div class="container-fluid col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-sm-10">
                        <h5 class="mb-0" data-anchor="data-anchor">Order Details</h5>
                    </div>
                    <div class="col-sm-2">
                        <a href="{{ url('restaurantConfirmOrder/list') }}" class="btn btn-outline-primary btn-sm"> Back </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
				@if($query)
					@foreach($query as $row)
					<div class="form-row">
    					<div class="col-md-3 mb-3">
                               <label for="orderCode"> Order Code  : </label>
                               <input type="text" id="orderCode" name="orderCode" class="form-control-line" value="{{ $row->code}}" readonly>
                               <input type="hidden" id="cityCode" name="cityCode" class="form-control-line" value="{{ $row->cityCode }}" readonly>
    					 </div>
    					<div class="col-md-3 mb-3">
							<label for="clientCode"> Client Code  : </label>
							<input type="text" id="clientCode" name="clientCode" value="{{ $row->clientCode }}" class="form-control-line" readonly>
    					</div>
    					<div class="col-md-3 mb-3">
    						<label for="clientName"> Client Name  : </label>
    						<input type="text" id="clientName" name="clientName" value="{{ $row->Clientname }}" class="form-control-line" disabled>
    					</div>
    					<div class="col-md-3 mb-3">
    						<label for="phone"> Phone  : </label>
    						<input type="number" id="phone" name="phone" class="form-control-line" value="{{ $row->mobile }}" readonly>
    						 </div>						 
    					</div>
    					 <div class="form-row">
    						<div class="col-md-3 mb-3">
                                <label for="paymentStatus"> Payment Status  : </label>
    							<input type="hidden"  id="paymentStatus" name="paymentStatus" value="{{ $row->paymentStatus }}"/>
    							<input type="hidden"  id="deliveryBoyCode" name="deliveryBoyCode" value="{{ $row->deliveryBoyCode }}"/>
                                <select  id="paymentStatusl" name="paymentStatusl" class="form-control-line" value="{{ $row->paymentStatus }}" disabled>
									<option value="">Select option</option>
									@if($paymentStatus)
										@foreach($paymentStatus as $pay){
											@if($pay->statusSName==$row->paymentStatus)
												<option value="{{ $pay->statusSName }}" selected>{{ $pay->statusName}}</option>
											@else
												<option value="{{ $pay->statusSName }}">{{ $pay->statusName}}</option>
											@endif
										@endforeach
									@endif
    							</select>
    						 </div>
    						 <div class="col-md-3 mb-3">
                                <label for="orderStatus"> Order Status  : </label>
                                <select id="orderStatus" name="orderStatus" class="form-control-line" disabled>
									<option value="">Select option</option>
									@if($orderStatus)
										@foreach($orderStatus as $status){
											@if($status->statusSName==$row->orderStatus)
												<option value="{{ $status->statusSName }}" selected>{{ $status->statusName }}</option>
											@else
												<option value="{{ $status->statusSName }}">{{ $status->statusName }}</option>
											@endif
										@endforeach
									@endif
    						   </select>
    						 </div>
    						 <div class="col-md-3 mb-3">
                                <label for="paymentmode"> Payment Mode  : </label>
                                <input type="text" id="paymentmode" name="paymentmode" class="form-control-line" value="{{ $row->paymentMode }}" readonly>
                                
    						 </div>
							 <div class="col-md-3 mb-3">
    							<label for="coupanCode"> Coupon Code  : </label>
    							<input type="text" id="coupanCode" name="coupanCode" class="form-control-line" value="{{ $row->couponCode }}" readonly>
    						 </div>
    					 </div>
    					 
    					<div class="form-row">
    						<div class="col-md-2 mb-3 d-none">
    							<label for="discount"> Discount Amount : </label>
    							<input type="text" id="discount" name="discount" class="form-control-line" value="{{ $row->discount }}" readonly>
    						 </div>
    						<div class="col-md-12 mb-3">
    							<label for="address"> Address  : </label>
										<input type="text" id="address" name="address" class="form-control-line" value="{{ $row->address }}" readonly>
    						 </div>
    					</div>
						<label for="clientName" ><h2> </h2> </label>		
						<h4 class="card-title">Item List</h4>
						<hr>
							  <div class="table-responsive">
                                    <table id="datatableOrderDetailsRestaurant" class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Sr.No.</th>
												 <th>Item Code</th>
                                                 <th>Item Name</th> 
												 <th>Item Price</th>
												 <th>Quantity </th> 
												 <th>Total Price</th> 
                                             </tr>
                                        </thead>
                                    </table>
                                </div>
							   <div class="form-row mt-3">
									<div class="col-md-6"></div>
    								<div class="col-md-6">
										<div class="col-md-6 offset-md-6">
											<b style="width:100%"><label>Item Total: </label> <span class="float-right">{{ number_format($row->subTotal+$row->discount, 2, '.', '') }}</span></b>
										</div>
										<div class="col-md-6 offset-md-6">
											<b style="width:100%"><label>Discount (-): </label><span class="float-right">{{ number_format($row->discount, 2, '.', '') }}</span></b>
										</div>
										<div class="col-md-6 offset-md-6">
											<div style="border-bottom:2px dashed;margin:10px 0"></div>
										</div>
										<div class="col-md-6 offset-md-6">
											<b style="width:100%"><label>Sub Total: </label><span class="float-right">{{ number_format($row->subTotal, 2, '.', '') }}</span></b>
										</div>
										<div class="col-md-6 offset-md-6">
											<b style="width:100%"><label>Tax (+): </label><span class="float-right">{{ number_format($row->tax, 2, '.', '') }}</span></b>
										</div>
										<div class="col-md-6 offset-md-6">
											<b style="width:100%;"><label>Packaging Charges (+): </label><span class="float-right"><?= number_format($row->totalPackgingCharges, 2, '.', '') ?></span></b>
										</div>
										<div class="col-md-6 offset-md-6">
											<b style="width:100%;"><label>Shipping Charges (+): </label><span class="float-right"><?= number_format($row->shippingCharges, 2, '.', '') ?></span></b>
										</div>
										<div class="col-md-6 offset-md-6">
											<div style="border-bottom:2px dashed;margin:10px 0"></div>
										</div>
										<div class="col-md-6 offset-md-6">
											<b style="width:100%;"><label>Grand Total: </label><span class="float-right"><?= number_format($row->grandTotal, 2, '.', '') ?></span></b>
										</div>
							       </div>
    							</div>
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
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/toastr/build/toastr.min.js') }}"></script>
@endpush