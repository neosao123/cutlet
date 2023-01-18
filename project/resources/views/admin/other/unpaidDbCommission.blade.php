@php
$i=1;
$points = 0;
@endphp
@if(!empty($commissionData) && count($commissionData)>0)
<table class="table table-bordered table-sm" style="width:100%;">
	<tr>
		<th>Sr.No.</th>
		<th>Payment Status</th>
		<th>Order Code</th>
		<th>Commission</th>
		<th>Order Price</th>
		<th>Return Price</th>
		<th>Date</th>
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
		<td>{{ $r->commissionAmount }}</td>
		<td>{{ $r->totalPrice }}</td>
		<td>{{ $r->totalPrice - $r->commissionAmount}}</td>
		<td>{{ date('d-m-Y h:i A',strtotime($r->addDate)) }}</td>
		</tr>
		@if($r->isPaid==1)@else
			@php $points+= $r->commissionAmount; @endphp
		@endif
		@php $i++ @endphp
	@endforeach
</table>

<h4  class="text-left float-left mt-1">Total Delivery Boy Commission: {{ $points }}</h4>
@if($paidCount==$totalOrders)
@else
	<button class="btn btn-primary float-right paybtn" data-datesearch="{{ $dateSearch}}" data-seq="{{ $userCode }}">Pay Now</button>
@endif
@else
	No Records Found
@endif
<script>
$(document).ready(function () {
 $('.paybtn').on("click", function() {
		var code=$(this).attr('data-seq');
		var dateSearch=$(this).attr('data-datesearch');
		 Swal.fire({
				title: "Are you sure?",
				text: "You want to confirm to pay commission of "+code,
				icon: "warning",
				showCancelButton: !0,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, Pay Now!",
				cancelButtonText: "No, cancel it!",
				closeOnConfirm: !1,
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({ 
						url: baseUrl+"/other/save",
						type: 'POST',
						data:{
						  'code':code,
						  'dateSearch':dateSearch
						},
						success: function(data) {
							if(data=='true'){
								Swal.fire("Successful", "Commission Paid Successfully!", "success");
								$('#responsive-modal').modal('hide');
								getDataTable(code,dateSearch)
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
 