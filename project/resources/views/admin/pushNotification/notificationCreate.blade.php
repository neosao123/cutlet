@extends('admin.layout.master', ['pageTitle'=>"Push Notification"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/notification/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Notification</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Create Notification</a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Custom Notification Panel</h5>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif

          <form action="{{ url('notification/process') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
            @csrf
            <div class="form-row">	
				<div class="form-group col-sm-4">
				    <label for="inputEmail3" class="control-label">Title</label>
					<input type="text" class="form-control" id="inputEmail3" placeholder="Title" name="title" required data-parsley-required-message="Title is required">
					<span class="text-danger">
                       {{ $errors->first('title') }}
                    </span>
				</div>										
				<div class="form-group col-sm-8">
					<label for="" class=" control-label">Message</label>
					<input type="text" class="form-control" id="msg" placeholder="Message"  name="msg" required data-parsley-required-message="Message is required">
					<span class="text-danger">
                       {{ $errors->first('msg') }}
                    </span>
				</div>
  				<div class="form-group col-sm-4">
  					<label for="cityCode" class="control-label">City</label>
					<select class="form-control select2" multiple="multiple" id="cityCode2" name="cityCode2" style="width:100%">
  						<option value="">Select City</option>
  						@foreach ($city as $c)
							<option value="{{ $c->code }}">{{ $c->cityName }}</option>
						@endforeach
  					</select>
					<span class="text-danger">
                       {{ $errors->first('cityCode2') }}
                    </span>
  				</div>
  				<div class="form-group col-md-4">
  					<label for="" class="control-label">Client Name</label>
  					<select class="form-control select2" id="client" placeholder="Client Name" name="client"  style="width:100%">
  					</select>
  				</div>
  				<div class="form-group col-md-4">
					<label for="" class="control-label">File</label>
  					<input type="file" id="exampleInputFile" class="form-control" name="catimg">
  					<center><br><img id="immg" src="" height="200" width="150" class="d-none"> </center> <br> 
  					<input type="hidden" class="form-control" id="imgHide2" placeholder="Message"  name="img"  >
    			</div>
    			<div class="col-sm-4">
    			    <input type="submit" class="btn btn-primary pull-right" value="Send Notification" name="custom">
				</div>
				</div>
          </form>
        
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
	 <script type="text/javascript" src="{{ asset('assets/init_site/admin/notification/index.js') }}"></script>
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
