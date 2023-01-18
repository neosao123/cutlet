@extends('admin.layout.master', ['pageTitle'=>"Push Notification"])
@push('styles')
  <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/notification/index.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Push Notification</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Push Notification Process</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid col-md-12">
     <input type="hidden" id="userCount" value="<?= $listLength ?>">
	    @if(!empty($firebaseIds))
		@php $i = 0 @endphp
           @foreach ($firebaseIds as $rowData) 
               <input type="hidden" id="firebaseIds" name="firebaseIds[]" value="<?= $rowData->firebaseId ?>">
         @php $i++ @endphp
         @endforeach
	    @endif
        <input type="hidden" id="cityCodes" value="<?= $cityCodes ?>">

        <input type="hidden" id="title" value="<?= $notificationData['title'] ?>">
        <input type="hidden" id="message" value="<?= $notificationData['message'] ?>">
        <input type="hidden" id="image" value="<?= $notificationData['image'] ?>">
        <input type="hidden" id="product_id" value="<?= $notificationData['product_id'] ?>">
        <input type="hidden" id="type" value="<?= $notificationData['type'] ?>">
        <input type="hidden" id="clientCode" value="<?= $notificationData['clientCode'] ?>">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Send Notification...........</h3>
            </div>
            <div class="panel-body">
                <span id="success_message"></span>
                <div class="form-group" id="process" style="display:block;">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="">
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
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
	 <script type="text/javascript" src="{{ asset('assets/init_site/admin/notification/process.js') }}"></script>
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
