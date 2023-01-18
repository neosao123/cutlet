@extends('admin.layout.master', ['pageTitle'=>"Edit Customer"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/Customer/index.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Customer</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Customer Update </a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid col-md-6">
    <div class="card">
      <div class="card-header">
        <div class="row">
         <div class="col-sm-10">
            <h5 class="mb-0" data-anchor="data-anchor">Update Customer</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('customer/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        @if ($customer)
          <form action="{{ url('customer/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
            @csrf
			<div class="form-row">
				<div class="col-md-6 mb-3">
					<label for="name">Client Code:</label>
					<input type="text" name="code" class="form-control" value="{{ $customer->code }}" readonly> 
				</div>
				<div class="col-md-6 mb-3">
					<label for="mobile">Mobile Number :</label>
						<input type="text" id="mobile" disabled onkeypress="return validateFloatKeyPress(this, event, 9, -1);" name="mobile" value="{{ $customer->mobile }}" class="form-control">
						
				</div>   
			</div> 
            <div class="form-row">
                <div class="col-md-12 mb-3">
                    <label for="name">Client Name:</label>
                     <input type="text" id="name" name="name" value="{{ $customer->name }}" class="form-control"> 
					 <span class="text-danger">
						{{ $errors->first('name') }}
					</span> 
                </div>
            </div>   
              <div class="col-sm-12 form-group">
                <button class="btn btn-success"> Submit </button>
              </div>
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
