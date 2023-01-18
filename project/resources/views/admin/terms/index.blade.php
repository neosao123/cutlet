@extends('admin.layout.master', ['pageTitle'=>"Terms And Conditions"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/terms/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Terms And Conditions</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Terms And Conditions List</a></li>
            </ol>
          </nav>
        </div>
      </div>
      <div class="col-7 align-self-center">
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row g-3 mb-3">
      <div class="col-lg-5">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0" data-anchor="data-anchor">Terms And Conditions</h5>
          </div>
          <div class="card-body pt-0">
            <form id="termsForm" class="" data-parsley-validate="">
              @csrf
              <input type="hidden" value="" name="code" id="code">
              <div class="mb-3">
                <label class="form-label" for="termsType">Type</label>
                <select class="form-control" id="termsType" name="termsType" required="" data-parsley-required-message="Type is required." />
					<option value="cart">Cart</option>
				 <span class="text-danger">
					{{ $errors->first('termsType') }}
                </span>
				</select>
              </div>
			  <div class="mb-3">
                <label class="form-label" for="termsText">Text</label>
                <textarea class="form-control" type="text" id="termsText" name="termsText" value="" placeholder="Write here.." rows="4"  required data-parsley-required-message="Terms and condition is required." data-parsley-trigger="keyup" ></textarea>
				 <span class="text-danger">
					{{ $errors->first('termsText') }}
                </span>
              </div>
              <div class="mb-3">
                <div class="custom-control custom-checkbox mr-sm-2">
                  <input class="custom-control-input" name="isActive" id="isActive" type="checkbox" value="1" />
                  <label class="custom-control-label" for="isActive">Active</label>
                </div>
              </div>
              <div class="mb-3">
                <button type="button" class="btn btn-success btnsubmit">Submit</button>
                <button type="reset" class="btn btn-danger">Reset</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-7">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0" data-anchor="data-anchor">Terms And Conditions List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-terms" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No. </th>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Text</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
              </table>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/terms/index.js') }}"></script>
@endpush
