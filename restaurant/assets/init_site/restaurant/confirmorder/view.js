function numberToWords(number) {  
        var digit = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];  
        var elevenSeries = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];  
        var countingByTens = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];  
        var shortScale = ['', 'thousand', 'million', 'billion', 'trillion'];  
  
       number = number.toString(); number = number.replace(/[\, ]/g, ''); if (number != parseFloat(number)) return 'not a number'; var x = number.indexOf('.'); if (x == -1) x = number.length; if (x > 15) return 'too big'; var n = number.split(''); var str = ''; var sk = 0; for (var i = 0; i < x; i++) { if ((x - i) % 3 == 2) { if (n[i] == '1') { str += elevenSeries[Number(n[i + 1])] + ' '; i++; sk = 1; } else if (n[i] != 0) { str += countingByTens[n[i] - 2] + ' '; sk = 1; } } else if (n[i] != 0) { str += digit[n[i]] + ' '; if ((x - i) % 3 == 0) str += 'hundred '; sk = 1; } if ((x - i) % 3 == 1) { if (sk) str += shortScale[(x - i - 1) / 3] + ' '; sk = 0; } } if (x != number.length) { var y = number.length; str += 'point '; for (var i = x + 1; i < y; i++) str += digit[n[i]] + ' '; } str = str.replace(/\number+/g, ' '); return str.trim() + ".";  
} 
$( document ).ready(function() {
	var noToWords = $('#payable').text();
		var word = numberToWords(noToWords);
		word = word.toLowerCase().replace(/\b[a-z]/g, function(letter) {
			return letter.toUpperCase();
		});
	$('#inwords').text(word);
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
				url: baseUrl+"/restaurantConfirmOrder/getOrderDetails",  
				type:"GET" , 
				data:{'orderCode':orderCode},
				"complete": function(response) { 
				
    	        }
			}
        });
	}
	
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
				cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl+ "/updateOrderStatus",
						type: 'get',
						data:{
							'orderCode': orderCode,
						    'orderStatus':orderStatus
							},
                        success: function (data) {
							//debugger
							var res = JSON.parse(data); 
							if (res.status) {
								
								Swal.fire({
                                    title: "Order",
									text: res.message,
									icon: "success"
                                }).then(() => {
                                    window.location.reload();
                                });
							} else {
								toastr.error(res.message, 'Order', {
									"progressBar": true
								});
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
                        text: "Your Order Request Cancel.",
                    });
                } 
				
			});
		});	
	
});