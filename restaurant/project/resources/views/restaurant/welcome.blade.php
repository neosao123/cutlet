@extends('restaurant.layout.master', ['pageTitle'=>"Recent Orders"])
@push('styles')
 <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
 <link href="{{ asset('assets/init_site/restaurant/recentorders/index.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="page-breadcrumb">
		<div class="row">
			<div class="col-5 align-self-center">
				<h4 class="page-title">Recent Orders...</h4>
				<span class="breadcrumb-item active" aria-current="page">Update in every 1 min. </span> 
			</div>
			<div class="col-7 align-self-center">
				<div class="d-flex no-block justify-content-end align-items-center">
					<div class=""><small>Refresh Time</small>
						<h4 class="text-info m-b-0 font-medium"><span id="timeOut"></span>'s</h4>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="container-fluid"> 
		<div class="row" id="orders">
             		
		</div>
	</div>
@endsection
@push('scripts')
 <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
 <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
 <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
 <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
 <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
 <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
 
 <script src="{{ asset('assets/init_site/restaurant/recentorders/index.js') }}"></script>
@endpush