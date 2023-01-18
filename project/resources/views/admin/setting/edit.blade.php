@extends('admin.layout.master', ['pageTitle'=>"Edit Setting"])
@push('styles')
   <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/setting/index.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Settings</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Setting Udpate </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Update Setting</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('setting/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if (session('error'))
          <div class="alert alert-danger">
            {{ session('error') }}
          </div>
        @endif
        @if ($setting)
          <form action="{{ url('setting/update') }}" method="post" enctype="multipart/form-data" data-parsley-validate="">
            @csrf
            <input type="hidden" name="code" value="{{ $setting->code }}" readonly>
            <div class="row">
              <div class="col-sm-12 form-group">
                <label for="settingName">Setting Name <span style="color:red">*</span></label>
                <input type="text" id="settingName" name="settingName" class="form-control" required value="{{ $setting->settingName }}" data-parsley-required-message="Setting Name Name is required">
                <span class="text-danger">
                  @error('settingName')
                    {{ $message }}
                  @enderror
                </span>
              </div>
              <div class="col-sm-12 form-group">
                <label for="settingValue">Setting Value <span style="color:red">*</span></label>
                <input type="text" id="settingValue" name="settingValue" class="form-control" required value="{{ $setting->settingValue }}" data-parsley-required-message="Setting value is required">
                <span class="text-danger">
                  @error('settingValue')
                    {{ $message }}
                  @enderror
                </span>
              </div>
			   <div class="col-sm-12 form-group">
                <label for="settingValue">Message Title</label>
                <input type="text" id="messageTitle" name="messageTitle" class="form-control" value="{{ $setting->messageTitle }}">
                
              </div>
			 <div class="col-sm-12 form-group">
             <label for="messageDescription">Message Description </label>
             <textarea class="form-control" id="messageDescription" name="messageDescription">{{ $setting->messageDescription }}</textarea>
              
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
