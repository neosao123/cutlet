@php $srno = 0; @endphp
@if($ordersData)    
    @foreach($ordersData as $r)
		@php $srno++; $cardbg = ""; $buttons = ""; $orderCode="" ; @endphp
		 @if($r['orderStatus']=='PLC')
			 @php $cardbg="bg-danger"; @endphp
		 @elseif($r['orderStatus']=='PRE')
		      @php $cardbg="bg-warning"; @endphp
		 @elseif($r['orderStatus']=='RCH')
		      @php $cardbg="bg-warning"; @endphp
		 @else
			  @php $cardbg = "bg-success";@endphp
		 @endif 
		<div class="col-sm-6 col-md-4 col-sm-12 mb-3">   
          <div class="card card-hover">
		        <div class="card-header {{ $cardbg }}">
				   <div class="row">
						<div class="col-5 col-sm-5">
							 <h6 class="m-b-1 text-white">
								<span class="btn btn-sm btn-dark mr-1">Order No. {{ $srno }}</span> 
								<span class="oder_code mt-1 mb-1">{{ $r['orderCode'] }}</span>
							</h6>
						</div>
						<div class="col-7 col-sm-7">
							<h6 class="card-title text-light"> {{ $r['orderDate']}}</h6>
						</div>
					   <input type="hidden" id="previousTime" value="{{ $r['preparingMinutes']}}"/>
					   <input type="hidden" id="orderCode" value="{{ $r['orderCode'] }} "/> 
					</div>
					 @if($r['orderStatus']=='PRE')
						<script> 
					        
							var elementId = "order_<?=$r['orderCode']?>";
							var orderAcceptTime= new Date('<?= $r['prepareDateTime']?>');
							var preparingTime= parseInt('<?= $r['preparingMinutes']?>');
							var mi = orderAcceptTime.getMinutes();
							var countDownDate = orderAcceptTime.setMinutes(mi + preparingTime);
							var x = setInterval(function() {
							  var now = new Date().getTime();
							 
							  var distance = countDownDate - now;
							  var days = Math.floor(distance / (1000 * 60 * 60 * 24));
							  var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
							  var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
							  var seconds = Math.floor((distance % (1000 * 60)) / 1000);
							  document.getElementById(elementId).innerHTML =  minutes + "m " + seconds + "s ";
							  if (distance < 0) {
								clearInterval(x);
								document.getElementById(elementId).innerHTML = "EXP"; 
								document.getElementById(elementId).style.backgroundColor = "red";
							  }
							}, 1000);

						</script>
						@endif
					   <div class="row price-row">
							<div class="col-2 col-sm-3">
								<h6 class="card-title text-light">Items -</h6>
								<span><b class="badge badge-light" style="font-weight:bold;font-size:14px">{{ $r['noofItems']}}</b></span>
							</div>
							<div class="col-4 col-sm4">
								<h6 class="card-title text-light">Amount - </h6>
								<span><b class="badge badge-light" style="font-weight:bold;font-size:14px">{{ $r['totalAmount'] }}</b></span>
								@if(!empty($r['coupanCode']))
								<i class="mdi mdi-tag-multiple"></i> 
								@endif
								<span class="float-right" data-toggle="collapse" data-target="#multiCollapse{{ $srno }}" aria-expanded="false" aria-controls="multiCollapse {{ $srno }}"><b class="badge badge-dark" style="font-weight:bold;font-size:14px"><i class="mdi mdi-chevron-down"></i></b></span>
								
							</div>
							@if($cardbg!="bg-danger")
							 <div class="col-6 col-sm-5">
								<h6 class="card-title text-light">Time -  {{$r['preparingMinutes']}} m </h6>
								 @if($r['orderStatus']=='PRE')
									<span><b class="time badge badge-light" style="font-weight:bold;font-size:14px" id="order_{{$r['orderCode']}}">00:00</b></span>
									<span class="float-right" data-toggle="collapse" data-target="#multiCollapseExample{{$srno}}" aria-expanded="false" aria-controls="multiCollapseExample{{$srno}}"><b class="badge badge-dark" style="font-weight:bold;font-size:14px"><i class="mdi mdi-chevron-down"></i></b></span>
								 @endif
							</div>
							@endif 
					    </div> 
						<div class="row">
							  <div class="col">
								<div class="collapse multi-collapse" id="multiCollapseExample<?=$srno?>">
								  <div class="card card-body mt-3">
										<div class="input-group mb-3">
										  <input type="number" class="form-control" min="5" max="180" step="2" id="addPrepareTime" name="addPrepareTime" placeholder="Increase preparing time in minutes 5 to 180" aria-label="Increase preparing time in minutes" aria-describedby="basic-addon2">
										  <div class="input-group-append">
											<button id="btn_increasePreparingTime" class="btn btn-outline-secondary" type="button">Add</button>
										  </div>
										</div>
										
										<script>
										 $( "#addPrepareTime" ).change(function() {
											  var max = parseInt($(this).attr('max'));
											  var min = parseInt($(this).attr('min'));
											  if ($(this).val() > max)
											  {
												  $(this).val(max);
											  }
											  else if ($(this).val() < min)
											  {
												  $(this).val(min);
											  }       
											}); 
										</script>
								  </div>
								</div>
								<div class="collapse multi-collapse" id="multiCollapse{{$srno}}">
								  <div class="card card-body mt-3">
										<div class=" mb-0">
												<p class="card-title">SubTotal - {{$r['actualAmount']}}  </p><br>
												  @if(!empty($r['coupanCode']))
														<p class="card-title">Discount - {{$r['discount']}} ({{ $r['coupanCode'] }}) </p><br>
														<p class="card-title">Discount Price - {{ $r['actualAmount']-$r['discount'] }} </p><br>									
												  @endif
										        <p class="card-title">Shipping Charges - {{ $r['shippingCharges'] }} </p><br>
												<p class="card-title">Tax - {{ $r['tax'] }} </p><br>
												 @if(!empty($r['totalPackgingCharges']) || $r['totalPackgingCharges']!="0.00" )
												 <p class="card-title">Packaging Charges - {{ $r['totalPackgingCharges'] }}</p><br>
												@endif
													
										</div>
								  </div>
								</div>
							  
							  </div>
							</div>
					   
				   </div>
		        <div class="card-body">    
				        <p class="card-title">Customer -  {{$r['clientName']}}</p>
						<p class="card-title">Delivery Boy - {{$r['deliveryBoy'] }}</p> 
						<p class="card-title">Delivery Boy Contact - {{$r['deliveryBoyContact']}}</p> 
						<hr>
						 <div class="col-12 m-1" style="height:180px;overflow-y:scroll">
							<ul class="list-unstyled">
									@if(!empty($r['particulars']))
										@foreach($r['particulars'] as $p)
										<li class="media">
											@if(!empty($p['itemPhoto'])) 
												<img class="d-flex m-r-15" src="{{ $p['itemPhoto'] }}" class="circle" width="30" alt="{{ $p['itemName'] }}">
											@endif
											<div class="media-body">
												<h5 class="mt-0 mb-1">{{ $p['itemName'] }}</h5> 
												<label>Qty := <b>{{ $p['quantity'] }} </b></label>
												@if($p['addOns']!='' && $p['addOns']!=NULL)
												@php
											    $addon=rtrim($p['addOns'],', ');
                                                $addons=explode(', ',$addon);
												@endphp        
												<ul>
												@foreach($addons as $add)
												   <li><b>{{$add}}</b></li>
                                                @endforeach												
												</ul>
												
												@endif
											</div>
										</li> 
										@endforeach
									   @endif
									  
							</ul>
						</div>
				   </div>
				 <hr>
				  <div class="row mb-2">
				 @if($r['orderStatus']=='PLC')
					
				          <div class="d-flex ml-5">
						       <button data-id="{{$r['orderCode']}}" data-action="Update order to preparing food?" data-status="PRE" class="actionBtn waves-effect waves-light btn btn-success mr-1">Set Preparing</button>
						 
						       <button data-id="{{$r['orderCode']}}" data-action="Are you sure to reject order?" data-status="RJT" class="actionBtn waves-effect waves-light btn btn-danger mr-1">Reject</button>
						  </div>
				   
				 @elseif($r['orderStatus']=='PRE')
					  <div class="d-flex ml-5"><button data-id="{{$r['orderCode']}}" data-action="Update order to ready for pickup?" data-status="RFP" class="actionBtn waves-effect waves-light btn btn-success mr-1">Ready For Pickup?</button></div>
					  
				 @elseif($r['orderStatus']=='RCH')
					   <div class="d-flex ml-5"><button data-id="{{$r['orderCode']" data-action="Update order to ready for pickup?" data-status="RFP" class="actionBtn waves-effect waves-light btn btn-success mr-1">Ready For Pickup?</button></div>
				 @else
						<div class="d-flex ml-5"><button class="btn btn-success">Waiting for delivery boy pickup...</button></div>
				 @endif 
                   </div>				  				 
		        </div>
			</div>
        </div> 
	@endforeach
@endif

