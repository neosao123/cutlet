@php
$i=1;
$points = 0;
@endphp
@if(!empty($commissionData) && count($commissionData)>0)
<table class="table table-bordered table-sm" style="width:100%">
	<tr>
		<th>Sr.No.</th>
		<th>Payment Status</th>
		<th>Order Code</th>
		<th>Order Date</th>
		<th>Total Order Amount</th>
		<th>Sub Total Amount</th> 
	    <th>Commission Amount</th>									
	    <th>Commission Percentage (%)</th>									
		<th>Restaurant Amount</th>
	</tr>
@php $paidCount=0;
	$totalOrders = count($commissionData);
@endphp
	@foreach($commissionData as $r)
		<tr>
		<td>{{ $i }}</td>
		@php $paidStatus = ''; @endphp
		@if($r->isPaid == 1) 
			@php $paidCount++; @endphp
			<td><span class='label label-sm label-success'>Paid</span></td>
		@else
			<td><span class='label label-sm label-warning'>Unpaid</span></td>
		@endif
		<td>{{ $r->orderCode }}</td>
		<td>{{ date('d/m/Y h:i A',strtotime($r->addDate)) }}</td>
		<td>{{ $r->grandTotal }}</td>
		<td>{{ $r->subTotal }}</td>
		<td>{{ $r->comissionAmount}}</td>
		<td>{{ $r->comissionPercentage }}(%)</td>
		<td>{{ $r->restaurantAmount }}</td>
		
		</tr>
		@if($r->isPaid==1)@else
			@php $points+= $r->restaurantAmount; @endphp
		@endif
		@php $i++ @endphp
	@endforeach
</table>

<h4  class="text-left float-left mt-1">Total Restaurant Commission: {{ $points }}</h4>
@if($paidCount==$totalOrders)
@else
	<button class="btn btn-primary float-right paybtn" data-fromdate="{{ $fromDate}}" data-todate="{{ $toDate }}" data-seq="{{ $restaurantCode }}">Pay Now</button>
@endif
@else
	No Records Found
@endif
<script>
//const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
 $('.paybtn').on("click", function() {
		var restaurantCode=$(this).attr('data-seq');
		var fromDate=$(this).attr('data-fromdate');
		var toDate=$(this).attr('data-todate');
		 Swal.fire({
				title: "Are you sure?",
				text: "You want to confirm to pay commission of "+restaurantCode,
				icon: "warning",
				showCancelButton: !0,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, Pay Now!",
				cancelButtonText: "No, cancel it!",
				closeOnConfirm: !1,
				closeOnCancel: !1
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({ 
						url: baseUrl+"/other/saveRes",
						type: 'POST',
						data:{
						  'restaurantCode':restaurantCode,
						  'fromDate':fromDate,
						  'toDate':toDate
						},
						success: function(data) {
							if(data=='true'){
								Swal.fire("Successful", "Commission Paid Successfully!", "success");
								$('#responsive-modal').modal('hide');
								getDataTable(restaurantCode,fromDate,toDate)
							} else {
								Swal.fire("Cancelled", "Some Error Occured! Please try again later..", "error");
							}
						}
					});
				} 
				else
				{
					Swal.fire("Cancelled", "Your Commission Records are safe.)", "error");
				}
		});
	 });
});
					 
</script>
 