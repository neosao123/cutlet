<aside class="left-sidebar">
  <div class="scroll-sidebar">
    <nav class="sidebar-nav">
      <ul id="sidebarnav">
		  @php
		    $code = Auth::guard('admin')->user()->code;
		    $filename = asset('assets/init_site/rights/'.$code.'.json'); 
			$json = file_get_contents('assets/init_site/rights/'.$code.'.json');
			$moduledata=json_decode($json,true);
			$moduledata=json_decode($moduledata,true);
	        foreach($moduledata['ModulesData'] as $module){
	            if($module['type']==1){
		    @endphp
					<li class="sidebar-item"><a class="sidebar-link waves-effect waves-dark sidebar-link" href="{{ url($module['routeUrl']) }}" aria-expanded="false"><i class="mdi {{ $module['moduleIcon']}}"></i><span class="hide-menu">{{ $module['moduleName']}}</span></a></li>
			@php } else { @endphp
					<li class="sidebar-item"> <a class="sidebar-link has-arrow waves-effect waves-dark" href="javascript:void(0)" aria-expanded="false"><i class="mdi {{ $module['moduleIcon']}}"></i><span class="hide-menu">{{ $module['moduleName']}}</span></a>
						<ul aria-expanded="false" class="collapse  first-level">
						@php
		                    if($module['subStatus']){
								foreach($module['subModules'] as $submodule){
						@endphp
							<li class="sidebar-item"><a href="{{ url($submodule['routeUrl']) }}" class="sidebar-link"><i class="{{ $submodule['subModuleIcon']}}"></i><span class="hide-menu"> {{ $submodule['subModuleName']}}</span></a></li>
						@php }
							}
						@endphp
						</ul>
					</li>
			@php	
			}
		  }
		@endphp
      </ul>
    </nav>
  </div>
</aside>