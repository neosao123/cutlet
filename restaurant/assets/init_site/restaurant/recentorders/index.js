var i = 0;
$("#timeOut").text(i);

function getOrderCounts(){
		$.ajax({
			type: "get",
			url: baseUrl + "/getDashboardOrders",
			data: {},
			success: function(response) {
				$("#orders").empty();
				//response = $.parseHTML(response);
				if (response) {
					$("#orders").html(response);
				}
			}
		});
	}

$(document).ready(function () {
//update preparing time
  $("body").on("click", "#btn_increasePreparingTime", function(e) {
	var preparingTime = $("#addPrepareTime").val();
	var previousTime = $("#previousTime").val();
	var orderCode = $("#orderCode").val();
	//alert(preparingTime);
		$.ajax({
			url: baseUrl + "/updatePreparingTime",
			type: 'get',
			data: {
				'orderCode': orderCode,
				'preparingTime':preparingTime,
				'previousTime':previousTime
			},
			success: function(response) {
				var res = JSON.parse(response); 
				if (res.status) {
					  Swal.fire({
							title: "Preparing Time",
							text: res.message,
							icon: "success"
						}).then(() => {
							getOrderCounts();
						});
					getOrderCounts();
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				var errorMsg = 'Ajax request failed: ' + xhr.responseText;
				alert(errorMsg);
			}
		});
      });

  	$("body").on("click", ".actionBtn", function(e) {
		var orderCode = $(this).data("id");
		var orderStatus = $(this).data("status");
		var dataAction = $(this).data("action");
		Swal.fire({
                title: "Are you sure?",
                text: dataAction,
                icon: "warning",
                showCancelButton: !0,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Ok",
				cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl+ "/updateOrderStatus",
						type: 'get',
						data: {
							'orderCode': orderCode,
							'orderStatus':orderStatus
						},
                        success: function (data) {
							var res = JSON.parse(data); 
							if (res.status) {
								
								Swal.fire({
                                    title: "Order",
									text: res.message,
									icon: "success"
                                }).then(() => {
                                    getOrderCounts();
                                });
								getOrderCounts();
							} else {
								toastr.error(res.message, 'Order', {
									"progressBar": true
								});
								getOrderCounts();
							}
							
                        },
                        error: function (xhr, ajaxOptions, thrownError) {
                            var errorMsg = "Ajax request failed: " + xhr.responseText;
                            console.log("Ajax Request for patient data failed : " + errorMsg);
                        },
                    });
                } else {
                    Swal.fire({
                        icon: "success",
                        text: "Your record is safe",
                    });
                }
            });
	});
	
	
  //recent order dashboard
        setInterval(function(e) {
			//console.log("Time = ", i);
			if (i == 60) {
				i = 0;
				$("#timeOut").text(i);
				getOrderCounts();
			} else {
				i++;
				$("#timeOut").text(i);
			}
		}, 1000);
		
		getOrderCounts();

});