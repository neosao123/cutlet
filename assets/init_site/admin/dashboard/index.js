const baseUrl = document.getElementsByTagName("meta").baseurl.content;
var i = 0;
$("#timeOut").text(i);

var chart1 = null;
var chart2 = null;
function getChartData() {
    $.ajax({
        url: baseUrl+ "/dashboard/getOrdersGraphData",
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
function getBarChartData() {

    $.ajax({
        url: baseUrl + "/dashboard/getBarData",
        method: "GET",
        dataType: 'JSON',
        success: function(response) {
            drawChart_2(response['data']['xValues'], response['data']['yValues']);
        }
    });
}

function drawChart_2(x_Axis_Data, y_Axis_Data) {
        var ctx = document.getElementById("chart_2");
        if (chart2) chart2.destroy();
        if (x_Axis_Data !== null || x_Axis_Data !== undefined) {
            chart2 = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: x_Axis_Data,
                    datasets: [
                      {
                        label: 'customers',
                        data: y_Axis_Data,
                        backgroundColor: "#404E67",
                      }
                  ]
                },
                options: {
                    responsive: true,
                    legend: {
                        display: false
                    },
                    title: {
                        display: false,
                    },
                    scales: {
                        xAxes: [{
                            barThickness: 45
                        }]
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
				$("#totalRestaurants").text(res['totalRestaurants']);
				$("#totalCustomers").text(res['totalCustomers']);
				$("#totalDeliveryBoys").text(res['totalDeliveryBoys']);
				$("#presentDeliveryBoys").text(res['presentDeliveryBoys']);
				$("#absentDeliveryBoys").text(res['absentDeliveryBoys']);
				$("#orderAssignedDeliveryBoys").text(res['orderAssignedDeliveryBoys']);
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
$(document).ready(function() {
	setInterval(function(e) {
		if (i == 30) {
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
		if(action=='rests'){
			restTable();
		}else if(action=='custs'){
			custTable();
		}else if(action=='dbs'){
			dbTable()
		}else if(action=='pdbs'){
			dbTable(1)
		}else if(action=='adbs'){
			dbTable(2)
		}else if(action=='odbs'){
			dbTable(3)
		}else if(action=='allOrds'){
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
	getBarChartData()
});

function restTable(){
	$.fn.DataTable.ext.errMode = 'none';
		if ($.fn.DataTable.isDataTable("#datatable_Rests")) {
			$('#datatable_Rests').DataTable().clear().destroy();
		}
		var dataTable = $('#datatable_Rests').DataTable({
			"paging": true,
			"processing": true,
			"serverSide": true,
			"order": [],
			"searching": false,
			"ajax": {
				url: baseUrl + "/dashboard/getRestaurant",
				type: "GET",
				data: {},
				"complete": function(response) { 
				}
			}
		});
		$("#rest_div").show();
		$("#delboy_div").hide(); 
		$("#order_div").hide(); 
		$("#cust_div").hide(); 
		$("html, body").delay(500).animate({
			scrollTop: $('#rest_div').offset().top 
    }, 600);
}

function custTable(){
	$.fn.DataTable.ext.errMode = 'none';
		if ($.fn.DataTable.isDataTable("#datatable_Custs")) {
			$('#datatable_Custs').DataTable().clear().destroy();
		}
		var dataTable = $('#datatable_Custs').DataTable({
			"paging": true,
			"processing": true,
			"serverSide": true,
			"order": [],
			"searching": false,
			"ajax": {
				url: baseUrl + "/dashboard/getCustomer",
				type: "GET",
				data: {},
				"complete": function(response) { 
				}
			}
		});
		$("#cust_div").show();
		$("#delboy_div").hide(); 
		$("#order_div").hide(); 
		$("#rest_div").hide(); 
		$("html, body").delay(500).animate({
			scrollTop: $('#cust_div').offset().top 
    }, 600);
}

function dbTable(type){
	$.fn.DataTable.ext.errMode = 'none';
		if ($.fn.DataTable.isDataTable("#datatable_delBoy")) {
			$('#datatable_delBoy').DataTable().clear().destroy();
		}
		var dataTable = $('#datatable_delBoy').DataTable({
			"paging": true,
			"processing": true,
			"serverSide": true,
			"order": [],
			"searching": false,
			"ajax": {
				url: baseUrl + "/dashboard/getDeliveryBoys",
				type: "GET",
				data: {
					"type":type,
				},
				"complete": function(response) { 
				}
			}
		});
		$("#delboy_div").show();
		$("#rest_div").hide(); 
		$("#order_div").hide(); 
		$("#cust_div").hide(); 
		$("html, body").delay(500).animate({
			scrollTop: $('#delboy_div').offset().top 
    }, 600);
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
