@extends('admin.layout.master', ['pageTitle'=>"Welcome"])
@push('styles')
<link href="{{ asset('assets/theme/assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
<link href="{{ asset('assets/theme/assets/libs/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/theme/dist/css/style.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/init_site/admin/dashboard/index.css') }}" rel="stylesheet">
@endpush
@section('content')
  <div class="page-breadcrumb">
    <div class="row">
      <div class="col-7 align-self-center">
        <h4 class="page-title">Dashboard (Updates Every 30 sec)</h4>
        <div class="d-flex align-items-center">

        </div>
      </div>
     <div class="col-5 text-right">
		<h4><span id="timeOut"></span>'s</h4>
	</div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
	    <div class="col-sm-4">
		     <div class="card">
			   <div class="card-body">
			        <h5 class="p-2">Delivered Restaurant Orders</h5>
					<canvas id="chart_1"></canvas>
			   </div>
			 </div>
		</div> 
		<div class="col-sm-8">
		     <div class="card">
			   <div class="card-body">
			        <h5 class="p-2">Monthly Customer Orders</h5>
					<canvas id="chart_2"></canvas> 
			   </div>
			 </div>
		</div> 
	</div>
    <div class="row">
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-hotel text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Total Restaurants</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="totalRestaurants"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="rests">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-account-multiple text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Total Customers</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="totalCustomers"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="custs">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-account-switch text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7">Total Delivery Boys</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="totalDeliveryBoys"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="dbs">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-account-multiple-plus text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7">Present Delivery Boys</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="presentDeliveryBoys"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="pdbs">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-account-multiple-minus text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7">Absent Delivery Boys</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="absentDeliveryBoys"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="adbs">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-account-edit text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7">Order Assigned Delivery Boys</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="orderAssignedDeliveryBoys"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="odbs">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-cart text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Total Orders</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="totalOrders"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="allOrds">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-cart text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Today's orders</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="todaysOrders"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="todaysOds">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-network-question text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Pending Orders</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="pendingOrders"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="pendOds">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-food-off text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Cancelled / Rejected Orders</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="cancelledOrders"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="canOds">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-food text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Confirmed Orders</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="confirmedOrders"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="confOds">View</a></div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-md-4 col-lg-3">
			<div class="card bg-dash">
				<div class="card-body">
					<div class="d-flex align-items-center">
						<div class="m-r-10"><h1 class="m-b-0"><i class="mdi mdi-check-all text-white"></i></h1></div>
						<div><h6 class="font-12 text-white m-b-5 op-7"> Delivered Orders</h6></div>
						<div class="ml-auto">
							<div class="crypto"><h4 class="text-white font-medium m-b-0" id="deliveredOrders"></h4></div>
						</div>
					</div>
					<div class="row text-right text-white" id="contractInfo">
						<div class="col-12"><a class="loadOrders" data-id="delOds">View</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="delboy_div" style="display:none">
			<div class="row">
				<div class="col-sm-12">
					<div class="card">
						<div class="card-body">
							<h5 class="p-2">Delivery Boy List</h5>
							<div class="table-responsive">
								<table id="datatable_delBoy" class="table table-bordered table-stripped" width="100%">
									<thead>
										<tr>
											<th>Sr. No.</th>
											<th>Full Name</th>
											<th>Designation</th>
											<th>City</th>
											<th>Mobile Number</th>
											<th>Email</th>
											<th>Status</th>
											<th>Operations</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="order_div" style="display:none">
			<div class="row">
				<div class="col-sm-12">
					<div class="card">
						<div class="card-body">
							<h5 class="p-2">Orders</h5>
							<div class="table-responsive">
								<table id="datatable_Food" class="table table-bordered table-stripped" width="100%">
									<thead>
										<tr>
											 <th>Sr. No</th>
											<th>Code</th>
											<th>Client Name</th>
											<th>Restaurant</th>
											<th>Address</th>
											<th>Mobile No</th>
											<th>Order Status</th>
											<th>Amount</th>
											<th>Order Date</th>
											<th>Delivery Boy</th>
											<th>Operations</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
			<div id="rest_div" style="display:none">
			<div class="row">
				<div class="col-sm-12">
					<div class="card">
						<div class="card-body">
							<h5 class="p-2">Restaurants</h5>
							<div class="table-responsive">
								<table id="datatable_Rests" class="table table-bordered table-stripped" width="100%">
									<thead>
										<tr>
											 <th>Sr. No. </th>
											<th>Restaurant Code</th>
											<th>Owner Name</th>
											<th>Restaurant Name</th>
											<th>Owner Contact</th>
											<th>Serviceable</th>
											<th>Status</th>
											<th>Operations</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
			<div id="cust_div" style="display:none">
			<div class="row">
				<div class="col-sm-12">
					<div class="card">
						<div class="card-body">
							<h5 class="p-2">Customers</h5>
							<div class="table-responsive">
								<table id="datatable_Custs" class="table table-bordered table-stripped" width="100%">
									<thead>
										<tr>
											<th>Sr.No</th>
											<th>Code</th>
											<th>Client Name</th>
											<th>City</th>
											<th>Mobile</th>
											<th>Email ID</th>
											<th>Status</th>
											<th>Operations</th>
										</tr>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
 </div>
@endsection
@push('scripts')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
<script type="text/javascript" src="{{ asset('assets/theme/assets/extra-libs/DataTables/datatables.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/dist/js/pages/datatable/datatable-basic.init.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/theme/assets/libs/bootstrap-switch/dist/js/bootstrap-switch.min.js') }}"></script>
<script src="{{ asset('assets/init_site/admin/dashboard/index.js') }}"></script>
@endpush
