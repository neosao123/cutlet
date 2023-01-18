@extends('admin.layout.master', ['pageTitle'=>"Setting"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
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
              <li class="breadcrumb-item"><a href="#">Setting View </a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">View Setting </h5>
          </div>
		   <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('setting/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
        @if ($setting)
          <div class="row">
            <div class="col-sm-12 form-group">
              <label for="settingName">Setting Name : </label>
              <input type="text" id="settingName" name="settingName" class="form-control" readonly value="{{ $setting->settingName }}">
            </div>
            <div class="col-sm-12 form-group">
              <label for="settingValue">Setting Value:</label>
              <input type="text" id="settingValue" name="settingValue" class="form-control" readonly value="{{ $setting->settingValue }}">
            </div>
            <div class="col-sm-12 form-group">
              <label for="messageTitle">Message Title : </label>
              <input type="text" id="messageTitle" name="messageTitle" class="form-control" readonly value="{{ $setting->messageTitle }}">
            </div>
			<div class="col-sm-12 form-group">
              <label for="messageDescription">Message Description: </label>
               <textarea id="messageDescription" name="messageDescription" class="form-control" readonly rows="2" cols="50" >{{ $setting->messageDescription}}</textarea>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection
@push('scripts')
@endpush
