@if (Auth::guard('restaurant')->check())
    @php
        $code = Auth::guard('restaurant')->user()->code;
        $entityImage = Auth::guard('restaurant')->user()->entityImage;
        $avatar = asset('assets/images/avatar.png');
        if ($entityImage != '' && $entityImage != null) {
            $avatar = env('IMG_URL').'uploads/restaurant/restaurantimage/'.$entityImage;
        }
    @endphp
<header class="topbar">
        <nav class="navbar top-navbar navbar-expand-md navbar-dark">
            <div class="navbar-header">
                <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
                <a class="navbar-brand" href="#">
                    <b class="logo-icon">
                        <!-- Dark Logo icon -->
                        <img src="{{ asset('assets/theme/assets/images/cutlett-logo.jpg') }}" alt="homepage" class="dark-logo" />
                        <!-- Light Logo icon -->
                        <img src="{{ asset('assets/theme/assets/images/cutlett-logo.jpg') }}" alt="homepage" class="light-logo"/>
                    </b>
                    <span class="logo-text">
                        <!--
            <img src="{{ asset('assets/theme/images/logo-text.png') }}" alt="homepage" class="dark-logo" />dark Logo text -->
                        <span class="text-light">CUTLETT</span>
                        <!-- Light Logo text
            <img src="{{ asset('assets/theme/images/logo-light-text.png') }}" class="light-logo" alt="homepage" /> -->
                    </span>
                </a>
                <a class="topbartoggler d-block d-md-none waves-effect waves-light" href="javascript:void(0)" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><i class="ti-more"></i></a>
            </div>
            <div class="navbar-collapse collapse" id="navbarSupportedContent">
                <ul class="navbar-nav float-left mr-auto">
                    <li class="nav-item d-none d-md-block"><a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a></li>
				   <li class="nav-item">
						<a class="nav-link">
							<div class="form-check form-check-inline">
								<div class="custom-control custom-checkbox">
								     @php
                                       $isServiceable=Auth::guard('restaurant')->user()->isServiceable;									 
									 @endphp
									<input type="checkbox" class="custom-control-input" id="maintenanceMode" value="1" data-seq="{{ Auth::guard('restaurant')->user()->isServiceable }}">
									<label class="custom-control-label cust_check" for="maintenanceMode">Online</label> 
								</div>
							</div>
						</a>
					</li>
					<li class="nav-item vendor-name">
						<h4 class="mb-0">{{ Auth::guard('restaurant')->user()->entityName }}</h4>
					</li>
                </ul>
                <ul class="navbar-nav float-right">
                    <li class="nav-item dropdown">

                    </li>
                    <li class="nav-item dropdown">

                    </li>
                    <li class="nav-item">
                        <a type="button" href="javascript:void" class="nav-link" id="change-theme" title="Change Theme">
                            <input type="checkbox" class="d-none" name="theme-view" id="theme-view">
                            <i class="fas fa-moon" id="theme-tag"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-muted waves-effect waves-dark pro-pic" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="{{ $avatar }}" alt="user" class="rounded-circle" width="31"></a>
                        <div class="dropdown-menu dropdown-menu-right user-dd animated flipInY">
                            <span class="with-arrow"><span class="bg-primary"></span></span>
                            <div class="d-flex no-block align-items-center p-15 bg-primary text-white m-b-10">
                                <div class=""><img src="{{ $avatar }}" alt="user" class="img-circle" width="60"></div>
                                <div class="ml-2">
                                    @if (session('RESTO_LOGIN'))
                                        <h4 class="m-b-0">{{ Auth::guard('restaurant')->user()->entityName }}</h4>
                                        <p class=" m-b-0">{{ Auth::guard('restaurant')->user()->firstName }}{{ Auth::guard('restaurant')->user()->LastName }}</p>
                                    @endif
                                   
                                </div>  
                            </div>
							<a class="dropdown-item" href="{{ url('/getRestaurantHours/' . $code) }}"><i class="fa fa-clock m-r-5 m-l-5"></i> Working Hours</a>
                            <a class="dropdown-item" href="{{ url('/profile/' . $code) }}"><i class="ti-user m-r-5 m-l-5"></i> My Profile</a>
                            <a class="dropdown-item" href="{{ url('/logout') }}"><i class="fa fa-power-off m-r-5 m-l-5"></i> Logout</a>
                            <div class="dropdown-divider"></div>
                            <div class="px-2"><a href="{{ url('/profileshow/' . $code) }}" class="btn btn-sm btn-success btn-rounded">View Profile</a></div>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
@endif