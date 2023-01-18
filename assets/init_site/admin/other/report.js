const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
	$('#fromDate').datepicker({
		format: "dd-mm-yyyy",
		showOtherMonths: true,
		selectOtherMonths: true,
		autoclose: true,
		changeMonth: true,
		changeYear: true,
		todayHighlight: true,
		orientation: "top",
	});
	$('#toDate').datepicker({ 
		format: "dd-mm-yyyy",
		showOtherMonths: true,
		selectOtherMonths: true,
		autoclose: true,
		changeMonth: true,
		changeYear: true,
		todayHighlight: true,
		orientation: "top"
	});	
    var fromdate = $("#fromDate").val();
	var todate= $("#toDate").val();
	$("#btnSearch").on("click", function (e) {
        var fromdate = $("#fromDate").val();
		var restaurantCode = $("#restaurantCode").val();
	    var todate=$("#toDate").val();
		var orderCode = $("#orderCode").val();
		var statusCode = $("#status").val();
		var customer = $("#customerCode").val();
        getDataTable(restaurantCode,statusCode,orderCode,fromdate,todate,customer);
    });
	
	$("#btnClear").click(function () {
         $('#restaurantCode').val(null).trigger('change');
         $('#orderCode').val(null).trigger('change');
         $('#status').val(null).trigger('change');
		 $('#customerCode').val(null).trigger('change');
		 $("#fromDate").val('');
		 $("#toDate").val('');
         getDataTable('','','',fromdate,todate,'');
    });
    getDataTable('','','','','');
    function getDataTable(restaurant_p,status_p,ordercode_p,fromdate_p,todate_p,customer_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#orderreport")) {
            $("#orderreport").DataTable().clear().destroy();
        }
        var dataTable = $("#orderreport").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: ({
                url: baseUrl + "/other/getOrderReportList",
                type: "GET",
                data: {
                    restaurant: restaurant_p,
                    statusCode: status_p,
					ordercode:ordercode_p,
					fromdate:fromdate_p,
					todate:todate_p,
					customer:customer_p
                },
                complete: function (response) {
                    //operations();
                },
            }),
        });
    }

});
