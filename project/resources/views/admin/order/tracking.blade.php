@extends('admin.layout.master', ['pageTitle' => 'Order Details'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/admin/orders/index.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Order Tracking Details</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">View</a></li>
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
                        <h5 class="mb-0" data-anchor="data-anchor">Tracking Details</h5>
                    </div>
                    <div class="col-sm-2">
                        <a href="{{ url('order/list') }}" class="btn btn-outline-primary btn-sm"> Back </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
 				<div class="form-row">
 					<div class="col-md-4 mb-3">
 						<label for="orderCode"> Order Code : </label>
 						<input type="text" id="orderCode" name="orderCode" class="form-control-line" value="{{ $query->code }}" readonly>
 						<input type="hidden" id="latitude" name="latitude" class="form-control-line" value="{{ $latitude }}" readonly>
 						<input type="hidden" id="longitude" name="longitude" class="form-control-line" value="{{ $longitude }}" readonly>
						<input type="hidden" id="ResLatitude" name="ResLatitude" class="form-control-line" value="{{ $ResLatitude }}" readonly>
 						<input type="hidden" id="ResLongitude" name="ResLongitude" class="form-control-line" value="{{ $ResLongitude }}" readonly>
 						<input type="hidden" id="clLatitude" name="clLatitude" class="form-control-line" value="{{ $clLatitude }}" readonly>
 						<input type="hidden" id="clLongitude" name="clLongitude" class="form-control-line" value="{{ $clLongitude }}" readonly>
 						<input type="hidden" id="clLabel" name="clLabel" class="form-control-line" value="{{ '<b>'.$clientName.'<br>'.$clientMobile.'</b>' }}" readonly>
 						<input type="hidden" id="dlbLabel" name="dlbLabel" class="form-control-line" value="{{ '<b>'.$dlbName.'<br>'.$dlbMobile.'</b>' }}" readonly>
 						<input type="hidden" id="resLabel" name="resLabel" class="form-control-line" value="{{ '<b>'.$ResName.'<br>'.$ResMobile.'</b>' }}" readonly>
 						<input type="hidden" id="dlbProfilePic" name="dlbProfilePic" class="form-control-line" value="{{ $dlbPic }}" readonly>
 						<input type="hidden" id="customerpng" name="customerpng" class="form-control-line" value="{{ url('assets/order_tracking/user_1.png') }}" readonly>
 						<input type="hidden" id="deliverypng" name="deliverypng" class="form-control-line" value="{{ url('assets/order_tracking/delivery.png') }}" readonly>
					</div>
 					<div class="col-md-3 mb-3">
 						<label for="deliveryBoyCode"> Delivery Boy: </label>
 						<input type="text" id="deliveryBoyCode" name="deliveryBoyCode" value="{{ $dlbName }}" class="form-control-line" readonly>
 					</div>
 					<div class="col-md-5 mb-3">
 						<label for="mobile"> Mobile: </label>
 						<input type="text" id="mobile" name="mobile" value="{{ $dlbMobile }}" class="form-control-line" disabled>
 					</div>
 				</div>
				<div class="form-row mb-3">
					<div id="myMap">
					</div>
				</div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
	<script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key={{ Config::get('constants.PLACE_API_KEY');}}"></script>
	 <script type="text/javascript" src="{{ asset('assets/init_site/admin/orders/tracking.js') }}"></script>
@endpush