@extends('admin.layout.master', ['pageTitle'=>"Reward Slot List"])
@push('styles')
  <link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/init_site/admin/rewardSlot/index.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-5 align-self-center">
        <h4 class="page-title">Reward Slot List</h4>
        <div class="d-flex align-items-center">
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item"><a href="#">Reward Slot List</a></li>
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
      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0" data-anchor="data-anchor">Add Reward Slot</h5>
          </div>
          <div class="card-body pt-0 mt-1">
            <form id="slotForm" class="" data-parsley-validate="">
              @csrf
              <input type="hidden" value="" name="code" id="code">
              <div class="mb-3">
                <label class="form-label" for="from" style="width:100%">From :<span class="float-right d-none">
					<div class="custom-control custom-checkbox mr-sm-2">
					  <input class="custom-control-input" name="includingFrom" id="includingFrom" type="checkbox" value="1" />
					  <label class="custom-control-label" for="includingFrom">Including From</label>
					</div>
				</span>
				</label>
                <input class="form-control number_only" id="from" name="from" required="" onchange="validateTo()" data-parsley-required-message="From is required." />
				 <span class="text-danger">
					{{ $errors->first('from') }}
                </span>
              </div>
			  <div class="mb-3">
                <label class="form-label" for="to" style="width:100%">To :<span class="float-right d-none">
					<div class="custom-control custom-checkbox mr-sm-2">
					  <input class="custom-control-input" name="includingTo" id="includingTo" type="checkbox" value="1" />
					  <label class="custom-control-label" for="includingTo">Including To</label>
					</div>
				</span></label>
                <input class="form-control number_only" id="to" name="to" onchange="validateTo()">
				 <span class="text-danger">
					{{ $errors->first('to') }}
                </span>
              </div>
			  <div class="mb-3">
                <label class="form-label" for="minusValue" style="width:100%">Percentage / Minus Value : <span class="float-right">
					<div class="custom-control custom-checkbox mr-sm-2">
					  <input class="custom-control-input" name="isMinus" id="isMinus" type="checkbox" value="1" />
					  <label class="custom-control-label" for="isMinus">Is Minus</label>
					</div>
				</span></label>
                <input class="form-control number_only" id="minusValue" name="minusValue">
				 <span class="text-danger">
					{{ $errors->first('minusValue') }}
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
                <button type="button" onclick="resetForm()" class="btn btn-danger">Reset</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header">
            <h5 class="mb-0" data-anchor="data-anchor">Reward Slot List</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table id="dataTable-slot" class="table table-striped table-bordered nowrap">
                <thead>
                  <tr>
                    <th>Sr.No.</th>
                    <th>Code</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Percentage / Minus Value (%)</th>
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
  <script type="text/javascript" src="{{ asset('assets/init_site/admin/rewardSlot/index.js') }}"></script>
@endpush
