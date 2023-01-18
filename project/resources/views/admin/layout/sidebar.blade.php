<aside class="left-sidebar">
  <div class="scroll-sidebar">
    <nav class="sidebar-nav">
      <ul id="sidebarnav">
        <li class="sidebar-item"><a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ url('/dashboard') }}" aria-expanded="false"><i class="mdi mdi-view-dashboard"></i><span class="hide-menu">Dashboard</span></a></li>
        <li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-settings"></i><span class="hide-menu">Configuration</span></a>
			<ul aria-expanded="false" class="collapse  first-level">
				<li class="sidebar-item"><a href="{{ url('designation/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Designation List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('city/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> City List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('address/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Address List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('users/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> User List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('businesstype/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Business Type List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('deliveryCharges/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Delivery Charges Slot List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('setting/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Setting List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('activity/index') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu">Activity Log List</span></a></li>
			</ul>
		</li>
		<li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-wrench"></i><span class="hide-menu">Restaurant Configuration</span></a>
			<ul aria-expanded="false" class="collapse  first-level">
			    <li class="sidebar-item"><a href="{{ url('restaurant-category/list') }}" class="sidebar-link"><i class="mdi mdi-checkbox-marked-circle-outline"></i><span class="hide-menu"> Restaurant Category</span></a></li>
			    <li class="sidebar-item"><a href="{{ url('menuCategory/list') }}" class="sidebar-link"><i class="mdi mdi-checkbox-marked-circle-outline"></i><span class="hide-menu"> Menu Category</span></a></li>
			    <li class="sidebar-item"><a href="{{ url('menuSubcategory/list') }}" class="sidebar-link"><i class="mdi mdi mdi-checkbox-marked-circle-outline"></i><span class="hide-menu"> Menu Subcategory</span></a></li>
				<li class="sidebar-item"><a href="{{ url('cuisine/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Cuisine List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('partner/list') }}" class="sidebar-link"><i class="mdi mdi-hotel"></i><span class="hide-menu"> Restaurant List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('restaurantItem/list') }}" class="sidebar-link"><i class="mdi mdi-food-fork-drink"></i><span class="hide-menu"> Restaurant Item List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('foodSlider/list') }}" class="sidebar-link"><i class="mdi mdi-image"></i><span class="hide-menu"> Restaurant Slider List</span></a></li>
				<li class="sidebar-item"><a href="{{ url('restaurantCoupon/list') }}" class="sidebar-link"><i class="mdi mdi-pocket"></i><span class="hide-menu"> Restaurant Offer List</span></a></li>
			</ul>
		</li>
        <li class="sidebar-item"><a href="{{ url('customer/list') }}" class="sidebar-link waves-effect waves-dark sidebar-link" href="#" aria-expanded="false"><i class="mdi mdi-account-multiple"></i><span class="hide-menu"> Customer List</span></a></li>
        <li class="sidebar-item"><a href="{{ url('homeSlider/list') }}" class="sidebar-link waves-effect waves-dark sidebar-link" href="#" aria-expanded="false"><i class="mdi mdi-arrange-send-backward"></i><span class="hide-menu"> Home Slider</span></a></li>
		<li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-lock"></i><span class="hide-menu"> Reset Password</span></a>
			<ul aria-expanded="false" class="collapse  first-level">
			    <li class="sidebar-item"><a href="{{ url('resetPassword/deliveryBoyList') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Delivery Boy </span></a></li>
			</ul>
		</li>
		<li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi mdi-cart"></i><span class="hide-menu">Order List</span></a>
			<ul aria-expanded="false" class="collapse  first-level">
			    <li class="sidebar-item"><a href="{{ url('restaurant-category/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Pending List</span></a></li>
			    <li class="sidebar-item"><a href="{{ url('menuCategory/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Placed List</span></a></li>
			    <li class="sidebar-item"><a href="{{ url('menuSubcategory/list') }}" class="sidebar-link"><i class="mdi mdi-adjust"></i><span class="hide-menu"> Service Unavailable List</span></a></li>
			</ul>
		</li>
		<li class="sidebar-item"><a href="{{ url('notification/create') }}" class="sidebar-link waves-effect waves-dark sidebar-link"><i class="mdi mdi-bell"></i><span class="hide-menu"> Push Notification</span></a></li>
      </ul>
    </nav>
  </div>
</aside>