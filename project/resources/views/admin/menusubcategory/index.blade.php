@extends('admin.layout.master', ['pageTitle'=>"Menu Subcategory"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/menusubcategory/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Menu Subcategory</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Menu Subcategory List</a></li>
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
            <h5 class="mb-0" data-anchor="data-anchor">Menu Subcategory</h5>
          </div>
          <div class="card-body pt-0">
            <form id="subcategoryform" class="" data-parsley-validate="">
              @csrf
              <input type="hidden" value="" name="code" id="code">
              <div class="mb-3">
                <label class="form-label" for="menuCategoryCode">Category</label>
				<select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="menuCategoryCode" id="menuCategoryCode" required data-parsley-required-message="Menu category is required">
					<option value="">Select Menu Category</option>
                  @foreach ($menuCategory as $catg)
                    <option value="{{ $catg->code }}">{{ $catg->menuCategoryName }}</option>
                  @endforeach
                </select>
				 <span class="text-danger">
					{{ $errors->first('menuCategoryCode') }}
                </span>
              </div>
			   <div class="mb-3">
                <label class="form-label" for="menuSubCategoryName">Subcategory Name</label>
                <input class="form-control" type="text" id="menuSubCategoryName" name="menuSubCategoryName" value="" placeholder="Subcategory Name" required="" data-parsley-required-message="Subcategory Name is required." />
				 <span class="text-danger">
					{{ $errors->first('menuSubCategoryName') }}
                </span>
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
            <h5 class="mb-0" data-anchor="data-anchor">Menu Subcategory List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-subcategory" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr. No. </th>
                    <th>Code</th>
                    <th>Menu Category</th>
                    <th>Sub Category</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/menusubcategory/index.js') }}"></script>
@endpush
