$( document ).ready(function() {
	$("#orderStatus").attr("disabled",true); 
	var orderStatus=$("#orderStatus").val(); 
	$("#revoke").hide(); 
			 
	
	loadTable();
	function loadTable(){
		if ($.fn.DataTable.isDataTable("#datatableOrderDetailsRestaurant")) {
			$('#datatableOrderDetailsRestaurant').DataTable().clear().destroy();
		}
		var orderCode=$('#orderCode').val();	 
		var dataTable = $('#datatableOrderDetailsRestaurant').DataTable({  
			"processing":true,  
			"serverSide":true,  
			"order":[],
			"searching": false,
			"ajax":{  
				url: baseUrl+"/restaurantPendingOrder/getOrderDetails",  
				type:"GET" , 
				data:{'orderCode':orderCode},
				"complete": function(response) { 
				
    	        }
			}
        });
	}
	
	
	$('#discard').click(function() {
			debugger;
			orderCode=$('#orderCode').val();
			Swal.fire({
                title: "Are you sure?",
				text: "Order Will Be Rejected..",
                icon: "warning",
                showCancelButton: !0,
				confirmButtonColor: "#DD6B55",
				cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl+ "/restaurantPendingOrder/reject",
						type: 'get',
						data:{'code':orderCode},
                        success: function (data) {
							//debugger
							var res = JSON.parse(data); 
							if(res.status)
							{
								Swal.fire({
                                    title: "Order",
									text: "Successfully Rejected Order",
									icon: "success"
                                }).then(() => {
                                    window.location.href=baseUrl+"/restaurantPendingOrder/list";
                                });
							}else{
								toastr.error('Something Went to Wrong', 'Order', { "progressBar": true });
							}
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            var errorMsg = "Ajax request failed: " + xhr.responseText;
                            console.log("Ajax Request for patient data failed : " + errorMsg);
                        },
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        text: "Your Order Reject Request Failed",
                    });
                } 
				
			});
		});	
	
});