@extends('admin.layout.master', ['pageTitle' => 'Users View'])
@push('styles')
    <link href="{{ asset('assets/theme/dist/css/parsely.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/init_site/admin/users/index.css') }}" rel="stylesheet">
	<link href="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/theme/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet">
@endpush
@section('content')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Users View</h4>
                <div class="d-flex align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item"><a href="#">User View</a></li>
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
                        <h5 class="mb-0" data-anchor="data-anchor">User Edit</h5>
                    </div>
                    <div class="col-sm-2">
                        <a href="{{ url('users/list') }}" class="btn btn-outline-primary btn-sm"> Back </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="" method="post" enctype="multipart/form-data" data-parsley-validate="">
					<input type="hidden" name="code" value="{{ $users->code }}" readonly>
                    <div class="row">
					     <div class="col-sm-12 form-group d-none">
                            <label for="username">Users Name</label>
                            <input type="text" id="username" name="username" value="{{$users->username }}" class="form-control" readonly >
                        </div>
                        <div class="col-sm-12 form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" id="fullname" name="fullname" value="{{ $users->name }}" class="form-control" readonly>
                        </div>
						<div class="col-sm-12 form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="userEmail"  value="{{ $users->userEmail }}" class="form-control" readonly>
                        </div>
						<div class="col-sm-12 form-group">
                            <label for="mobilenumber">Mobile Number</label>
                            <input type="text" id="mobilenumber" name="mobile" value="{{ $users->mobile }}" class="form-control" readonly>
                        </div>
						<div class="col-sm-12 form-group">
                            <label for="role">Role</label>
                            <select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="role" disabled>
                                <option value="">Select Role</option>
                                <option value="USER" {{ $users->role == 'USER' ? 'selected' : '' }}>User</option>
								 <option value="DBOY" {{ $users->role == 'DBOY' ? 'selected' : '' }}>Delivery Boy</option>
                            </select>
                        </div>
                        <div class="col-sm-12 form-group">
                            <label for="designation">Designation</label>
                            <select class="select2 form-control custom-select" style="width: 100%; height:36px;" name="designation" disabled>
                                <option value="">Select Desigation</option>
                                @foreach ($designation as $desi)
                                    <option value="{{ $desi->code }}"{{ $desi->code == $users->designationCode ? 'selected' : '' }}>{{ $desi->designation }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-12 form-group">
                            <label for="city">City </label>
                            <select class="form-control" style="width: 100%; height:36px;" name="city" id="city" disabled>
                               <option value="">Select City</option>
								  @foreach ($city as $cityItem)
									<option value="{{ $cityItem->code }}" {{ $cityItem->code == $users->cityCode ? 'selected' : '' }}>{{ $cityItem->cityName }}</option>
								  @endforeach
                            </select>
                        </div>
					  @if(!empty($users->profilePhoto))
						<div class="col-sm-2">
						    <img class="img-radius" id="profile_image" src="{{ url('uploads/profile/'.$users->profilePhoto)}}" height="80" width="80" accept=".jpg,.png,.jpeg"/>
						</div>
						@endif
					<div class="col-sm-12 form-group">
						<div class="custom-control custom-checkbox">
							<input type="checkbox" class="custom-control-input" id="isActive" name="isActive" value="1" checked="" onclick="return false">
							<label class="custom-control-label" for="isActive">Active</label>
						</div>
					</div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script type="text/javascript" src="{{ asset('assets/theme/dist/js/parsely.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/dist/sweetalert/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/select2/dist/js/select2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/moment/min/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/init_site/admin/users/add.js') }}"></script>	
@endpush