<aside class="left-sidebar">
  <div class="scroll-sidebar">
    <nav class="sidebar-nav">
      <ul id="sidebarnav">
	    <li class="sidebar-item"><a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ url('recentorders') }}" aria-expanded="false"><i class="mdi mdi-food-fork-drink"></i><span class="hide-menu">Recent Orders</span></a></li>
        <li class="sidebar-item"><a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ url('dashboard') }}" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><span class="hide-menu">Dashboard</span></a></li>
		<li class="sidebar-item"> <a class="sidebar-link  waves-effect waves-dark" href="{{ url('restaurantItems/list') }}" aria-expanded="false"><i class="mdi mdi-food-fork-drink"></i><span class="hide-menu">Restaurant Items</span></a>
		<li class="sidebar-item"> <a class="sidebar-link  waves-effect waves-dark" href="{{ url('restaurantPendingOrder/list') }}" aria-expanded="false"><i class="mdi mdi-pot"></i><span class="hide-menu">Pending Orders</span></a>
		<li class="sidebar-item"> <a class="sidebar-link  waves-effect waves-dark" href="{{ url('restaurantConfirmOrder/list') }}" aria-expanded="false"><i class="mdi mdi-pot-mix"></i><span class="hide-menu">Confirm Orders</span></a>
        <li class="sidebar-item"> <a class="sidebar-link  waves-effect waves-dark" href="{{ url('restaurantoffer/list') }}" aria-expanded="false"><i class="mdi mdi-percent"></i><span class="hide-menu">Offer</span></a>
		
		<li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-library-plus"></i><span class="hide-menu">Reports</span></a>
			<ul aria-expanded="false" class="collapse  first-level">
			    <li class="sidebar-item"><a href="{{ url('orderreport/list') }}" class="sidebar-link"><i class="mdi mdi-library-plus"></i><span class="hide-menu">Order Report</span></a></li>
				<li class="sidebar-item"><a href="{{ url('restaurantcommission/list') }}" class="sidebar-link"><i class="mdi mdi-currency-usd"></i><span class="hide-menu">Billing Report</span></a></li>
			</ul>
		</li> 
	  </ul>
    </nav>
  </div>
</aside> 