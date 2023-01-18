@extends('admin.layout.master', ['pageTitle'=>"Upload Restaurant Item"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/restaurantitem/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Upload Restaurant Item Excel</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Upload Restaurant Item Excel</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-right">
      </div>
    </div>
  </div>
  <div class="container-fluid col-md-8">
    <div class="card">
      <div class="card-header">
        <div class="row">
          <div class="col-sm-10">
            <h5 class="mb-0" data-anchor="data-anchor">Import Item Excel</h5>
          </div>
		  <div class="col-sm-2">
            <a class="btn btn-outline-primary btn-sm" href="{{ url('restaurantItem/list')}}"> Back </a>
          </div>
        </div>
      </div>
      <div class="card-body">
		<span id="message"></span>
		<form id="sample_form" method="POST" enctype="multipart/form-data" class="form-horizontal">
			<div class="form-group">
				<label class="col-md-4">Select Excel File</label>
				<input type="file" class="form-control" name="uploadFile" id="uploadFile" />
			</div>
			<div class="form-group" align="center">
				<input type="hidden" name="hidden_field" value="1" />
				<input class="btn btn-success" style="margin-right:5px;" type="button" id="upload" value="Import" onclick="Upload()" />
			</div>
		</form>
      </div>
    </div>
	<div class="modal fade bd-example-modal-lg" id="modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content" style="width:980px;">
				<div class="modal-header">
					<h5 class="modal-title"><b>Excel Preview</b></h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				</div>
                <div class="card-body">
					<form id="modalForm" method="post" name="s" enctype="multipart/form-data">
						<div class="row1" id="tableContent">                            
							<div class="form-group col-sm-12" id="dvExcel">
							</div> 
							<div id="hiddenHtml"></div>    
							<div id="successMsg"></div>
							<div id="message1" style="display:none"><h4 style="color:#DA4A07">The highlighted rows having following issues.Please check</h4><p id="validMsgs"></p></div>
							<input class="form-control" id="rowExcepts" name="rowExcepts" type="hidden" value="{}">
						</div>
						<div class="form-group" id="process" style="display:none;">
								<div class="progress" style="height:16px;">
									<div id="file-progress-bar" class="progress-bar progress-bar-striped active"></div>
								</div>
							</div>
						<div class="modal-footer">
							<button type="submit" name="submit" id="cutomerSubmit" class="btn btn-success">Save</button>
							<button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
						</div>
					</form>
                </div>                
			</div>
        </div>
	</div>
  </div>
@endsection
@push('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/xlsx.full.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.13.5/jszip.js"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/restaurantitem/upload.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.full.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/forms/select2/select2.init.js') }}"></script>
  @if (session('success'))
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
