const baseUrl = document.getElementsByTagName("meta").baseurl.content;
var i = 0;
$("#timeOut").text(i);
var chart1 = null;
function getChartData() {
    $.ajax({
        url: baseUrl+ "/dashboard/getOrdersDoughnutChartData",
        method: "GET",
        dataType: 'JSON',
        success: function(response) {
            drawChart_1(response['data']['label'], response['data']['data'],response['data']['color']);
        }
    });
}
function drawChart_1(label, data,color) {
        var ctx = document.getElementById("chart_1");
        if (chart1) chart1.destroy();
        if (label !== null || data !== undefined) {
            chart1 = new Chart(ctx, {
                type: "pie",
                data: {
                    labels: label,
                    datasets: [{
                        data: data,
                        backgroundColor: color,
                    }]
                },
                options: {
                    responsive: true,
                    legend: {
                        display: false
                    },
                    title: {
                        display: false,
                    }
                }
            });
        }
    }
 
function getOrderCounts() {
	$.ajax({
		type: "get",
		url: baseUrl + "/dashboard/getOrderCounts",
		data: {},
		success: function(response) {
			if (response) {
				var res = JSON.parse(response);
				$("#totalOrders").text(res['totalOrders']);
				$("#todaysOrders").text(res['todaysOrders']);
				$("#pendingOrders").text(res['pendingOrders']);
				$("#cancelledOrders").text(res['cancelledOrders']);
				$("#confirmedOrders").text(res['confirmedOrders']);
				$("#deliveredOrders").text(res['deliveredOrders']);
				
			}
		}
	});
}
function orderTable(type){
	$.fn.DataTable.ext.errMode = 'none';
		if ($.fn.DataTable.isDataTable("#datatable_Food")) {
			$('#datatable_Food').DataTable().clear().destroy();
		}
		var dataTable = $('#datatable_Food').DataTable({
			"paging": true,
			"processing": true,
			"serverSide": true,
			"order": [],
			"searching": false,
			"ajax": {
				url: baseUrl + "/dashboard/getOrders",
				type: "GET",
				data: {
					"type":type,
				},
				"complete": function(response) { 
				}
			}
		});
		$("#order_div").show();
		$("#rest_div").hide(); 
		$("#delboy_div").hide(); 
		$("#cust_div").hide(); 
		$("html, body").delay(500).animate({
			scrollTop: $('#order_div').offset().top 
    }, 600);
}

$(document).ready(function () {
			function getRestaurantStatus(){
			    $.ajax({
					url:baseUrl+"/getRestaurantStatus",
					method:"GET",
					data:{},
					datatype:"text",
					success: function(data)
					{
						var da = JSON.parse(data);
						if(da['settingValue']==1){
							$("#maintenanceMode").prop("checked",true);
							$(".cust_check").text("Offline");
						} else {
							$("#maintenanceMode").prop("checked",false);
						    $(".cust_check").text("Online");
						}  
					},
				});
			}
			getRestaurantStatus();
			$("#maintenanceMode").change(function(){
			    var settingValue= 0;
				debugger
				var isServiceable = $(this).data('seq');
				if(isServiceable==1){
					if($(this).is(":checked")){
						settingValue= 1;
					}  
					$.ajax({					
						url: baseUrl+"/updateRestaurantStatus",
						method:"get",
						data:{"settingValue":settingValue}, 
						datatype:"text",
						success: function(data)
						{
							 getRestaurantStatus();
						}
					});
				}else{
					$("input#maintenanceMode").attr("disabled", true);
					toastr.success("As Per Serviceable Restaurant is closed. You can not change status.", 'Restaurant', {
												"progressBar": true
											});	 
                    return false;
				}
			});
               
			setInterval(function(e) {
				if (i == 60) {
					i = 0;
					$("#timeOut").text(i);
					//getOrderCounts();
				} else {
					i++;
					$("#timeOut").text(i);
				}
			}, 1000);

            getOrderCounts();
			$("body").on("click", ".loadOrders", function() {
				var action = $(this).data("id");
			   if(action=='allOrds'){
					orderTable(1)
				}else if(action=='todaysOds'){
					orderTable(2)
				}else if(action=='pendOds'){
					orderTable(3)
				}else if(action=='canOds'){
					orderTable(4)
				}else if(action=='confOds'){
					orderTable(5)
				}else if(action=='delOds'){
					orderTable(6)
				}
			});
			
			getChartData();
	
});