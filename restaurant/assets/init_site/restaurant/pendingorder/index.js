$(document).ready(function () {
	$('#fromDate').datepicker({
			format:"dd-mm-yyyy",
			showOtherMonths: true,
			selectOtherMonths: true,
			autoclose: true,
			changeMonth: true,
			changeYear: true,
			todayHighlight: true,
			orientation: "bottom left",
	 });
	$('#toDate').datepicker({
		format: "dd-mm-yyyy",
		showOtherMonths: true,
		selectOtherMonths: true,
		autoclose: true,
		changeMonth: true,
		changeYear: true,
		todayHighlight: true,
		orientation: "bottom left",
	});		
    var fromdate = $("#fromDate").val();
	var todate=$("#toDate").val();
    var orderCode = $("#orderCode").val();
    var status = $("#status").val();
    
	$("#btnSearch").on("click", function (e) {
        var fromdate = $("#fromDate").val();
	    var todate=$("#toDate").val();
		var orderCode = $("#orderCode").val();
		var status = $("#status").val();
        getDataTable(status,orderCode,fromdate,todate);
    });
	
	$("#btnClear").click(function () {
         //$('#restitem').val(null).trigger('change');
         $('#orderCode').val(null).trigger('change');
         $('#status').val(null).trigger('change');
		 $("#fromDate").val('');
		 $("#toDate").val('');
         getDataTable('','','','');
        //location.reload();
    });
    getDataTable('','','','');
	//$("#restaurantCode").select2({});
    function getDataTable(status_p,ordercode_p,fromdate_p,todate_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#orderpending")) {
            $("#orderpending").DataTable().clear().destroy();
        }
        var dataTable = $("#orderpending").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: ({
                url: baseUrl + "/getRestaurantPendingOrder",
                type: "GET",
                data: {
                    status: status_p,
					ordercode:ordercode_p,
					fromdate:fromdate_p,
					todate:todate_p
                },
                complete: function (response) {
                    //operations();
                },
            }),
        });
    }
	

});
