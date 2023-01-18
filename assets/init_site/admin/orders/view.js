const baseUrl = document.getElementsByTagName("meta").baseurl.content;
function numberToWords(number) {  
        var digit = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];  
        var elevenSeries = ['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];  
        var countingByTens = ['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];  
        var shortScale = ['', 'thousand', 'million', 'billion', 'trillion'];  
  
       number = number.toString(); number = number.replace(/[\, ]/g, ''); if (number != parseFloat(number)) return 'not a number'; var x = number.indexOf('.'); if (x == -1) x = number.length; if (x > 15) return 'too big'; var n = number.split(''); var str = ''; var sk = 0; for (var i = 0; i < x; i++) { if ((x - i) % 3 == 2) { if (n[i] == '1') { str += elevenSeries[Number(n[i + 1])] + ' '; i++; sk = 1; } else if (n[i] != 0) { str += countingByTens[n[i] - 2] + ' '; sk = 1; } } else if (n[i] != 0) { str += digit[n[i]] + ' '; if ((x - i) % 3 == 0) str += 'hundred '; sk = 1; } if ((x - i) % 3 == 1) { if (sk) str += shortScale[(x - i - 1) / 3] + ' '; sk = 0; } } if (x != number.length) { var y = number.length; str += 'point '; for (var i = x + 1; i < y; i++) str += digit[n[i]] + ' '; } str = str.replace(/\number+/g, ' '); return str.trim() + ".";  
} 
$( document ).ready(function() {
	 $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
	loadTable();
    var noToWords = $('#payable').text();
		var word = numberToWords(noToWords);
		word = word.toLowerCase().replace(/\b[a-z]/g, function(letter) {
			return letter.toUpperCase();
		});
	$('#inwords').text(word);	
	getPendingDeliveryBoy();
	function loadTable(){
		if ($.fn.DataTable.isDataTable("#datatableOrderDetailsVendor")) {
			$('#datatableOrderDetailsVendor').DataTable().clear().destroy();
		}
		var orderCode=$('#orderCode').val();	 
		var dataTable = $('#datatableOrderDetailsVendor').DataTable({  
			"processing":true,  
			"serverSide":true,  
			"order":[],
			"searching": false,
			"ajax":{  
				url: baseUrl+"/order/getOrderDetails",  
				type:"GET" , 
				data:{'orderCode':orderCode},
				"complete": function(response) {
				
    	        }
			}
        });
            
		if ($.fn.DataTable.isDataTable("#datatable_orderStatus")) {
			$('#datatable_orderStatus').DataTable().clear().destroy();
		}
	    var orderCode=$('#orderCode').val();	 
	    var dataTable = $('#datatable_orderStatus').DataTable({  
    		"processing":true,  
            "serverSide":true,  
			"order":[],
    	    "searching": false,
			"ajax":{  
				url: baseUrl+"/order/getOrderStatusList",  
                type:"GET" , 
    			data:{'orderCode':orderCode},
                   "complete": function(response) {              
    	        }
	        }
        });
	}
	    
	$("#pendingDBGet").on("click",function() {
		$(this).addClass("fa-spin");
		var deliveryBoyCode = $('#deliveryBoyCode').val();
		var cityCode = $('#cityCode').val();
		$.ajax({
			url:baseUrl+"/order/getPendingDeliveryBoys",
			method:"GET",
			data:{
				'deliveryBoyCode':deliveryBoyCode,
				'cityCode':cityCode,
			},
			datatype:"text",
			success: function(data){
				if(data){
					$("#pendingDBGet").removeClass("fa-spin");
					$("#transferDeliveryBoy").html(data);
				}else{
					$("#pendingDBGet").removeClass("fa-spin");
					swal({
					title: "warning!",
					text: "no delivery boy found!",
					type: "warning",
					button: "ok",
					});
				}
			}
		});
	});
	   
	$('#transferDeliveryBoy').on('change', function() {
		var fromDeliveryBoy=$('#deliveryBoyCode').val();	
        var toDeliveryBoy=$(this).val();
		if(toDeliveryBoy=="")return false;
        var orderCode=$('#orderCode').val();
        var orderStatus=$('#orderStatus').val();
		var resCode=$('#resCode').val();
        var orderType="food";
		 Swal.fire({
                title: "Confirmation",
                text: "Do you want to transfer this order to another delivery boy?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes",
				closeOnConfirm: false 
            }).then((result) => {
                if (result.isConfirmed) {
					$.ajax({
						url:baseUrl+"/order/transferOrder",
						method:"POST",
						data:{
							fromDeliveryBoy,toDeliveryBoy,orderCode,orderStatus,orderType,resCode
						},
						datatype:"text",
						success: function(data){
							var obj=JSON.parse(data);
							if(obj.status){
								Swal.fire("Transfered!", obj.message, "success"); 
								window.location.reload();
							}else{
								Swal.fire("Transfered!", obj.message, "error"); 
							}
						}
					});
			}
		});					
    });
}); 
	function getPendingDeliveryBoy(){
		var deliveryBoyCode = $('#deliveryBoyCode').val();
		var cityCode = $('#cityCode').val();
		$.ajax({
			url:baseUrl+"/order/getPendingDeliveryBoys",
			method:"GET",
			data:{
				'deliveryBoyCode':deliveryBoyCode,
				'cityCode':cityCode,
			},
			datatype:"text",
			success: function(data){
				if(data){
					$("#transferDeliveryBoy").html(data);
				}
			}
		});
	}
	$('#isExpired').on('click', function() {         
        var isExpired = $("#isExpired").val();
		if($("#isExpired").prop('checked') == true){
			var isExpired=$(this).val();
			var orderCode=$('#orderCode').val();
			var orderStatus=$('#orderStatus').val();
			$.ajax({
				url: baseUrl+"/order/checkDeliveryBoyOrders",
				method: "GET",
				data: {
					"code": orderCode
				},
				datatype: "text",
				success: function(data) {
					console.log(data);
					var obj1=JSON.parse(data);
					if (obj1.status) {
						 Swal.fire({
							title: "Are you sure?",
							text: "The Delivery Boy has Orders, Click OK to expire this order.",
							icon: "warning",
							button:"Ok",
							showCancelButton: !0,
							closeOnConfirm: false 
						}).then((result) => {
							if (result.isConfirmed) {
								expireOrder(isExpired,orderCode,orderStatus,obj1.dbCode); 
							}else{
								$('#isExpired').prop('checked',false);	
							}
							}
						);	
					}else{
						 Swal.fire({
							title: "Confirmation",
							text: "Do you want to Make This Action?",
							icon: "warning",
							showCancelButton: true,
							confirmButtonColor: "#DD6B55",   
							confirmButtonText: "yes",   
							closeOnConfirm: false 
						}).then((result) => {
							if (result.isConfirmed) {
								expireOrder(isExpired,orderCode,orderStatus,""); 
							}else{
								$('#isExpired').prop('checked',false);	
							}
						});
					}
				}
			});
		}				
	});

    function expireOrder(isExpired,orderCode,orderStatus,dbCode) {
		$.ajax({
			url:baseUrl+"/order/expiredByAdmin",
			method:"POST",
			data:{
				isExpired,orderCode,orderStatus,dbCode
			},
			datatype:"text",
			success: function(data){
				var obj=JSON.parse(data);
				if(obj.status){
					Swal.fire("Expired!", obj.message, "success"); 
					//location.reload();
					window.location.href = baseUrl + "/order/pendingList"; 
				}else{
					Swal.fire("Expired!", obj.message, "error"); 
				}
			}
		});					
	}