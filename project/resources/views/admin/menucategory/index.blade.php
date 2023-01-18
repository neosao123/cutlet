@extends('admin.layout.master', ['pageTitle'=>"Menu Category"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/menucategory/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Menu Category</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Menu Category List</a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Menu Category</h5>
          </div>
          <div class="card-body pt-0">
            <form id="categoryform" class="" data-parsley-validate="">
              @csrf
              <input type="hidden" value="" name="code" id="code">
              <div class="mb-3">
                <label class="form-label" for="menuCategoryName">Category Name</label>
                <input class="form-control" type="text" id="menuCategoryName" name="menuCategoryName" value="" placeholder="Category Name" required="" data-parsley-required-message="Category Name is required." />
				 <span class="text-danger">
					{{ $errors->first('menuCategoryName') }}
                </span>
              </div>
			   <div class="mb-3">
                <label class="form-label" for="priority">Priority</label>
                <input class="form-control" type="text" id="priority" name="priority" value="" placeholder="Priority">
              </div>
              <div class="mb-3">
                <div class="custom-control custom-checkbox">
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
            <h5 class="mb-0" data-anchor="data-anchor">Menu Category List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-category" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No. </th>
                    <th>Code</th>
                    <th>Category</th>
                    <th>Priority</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/menucategory/index.js') }}"></script>
@endpush
