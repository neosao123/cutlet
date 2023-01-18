const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    $("#orderStatus option[value=SHP]").hide();
	$("#orderStatus option[value=DEL]").hide();
	$("#orderStatus option[value=PLC]").hide();
	$("#orderStatus option[value=RJT]").hide();
	$('#fromDate').datepicker({
        format: "dd-mm-yyyy",
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
	$('#btnSearch').on('click', function(e) {
		orderCode = $('#orderCode').val();
		restaurantCode = $('#restaurantCode').val();
		fromDate = $('#fromDate').val();
		toDate = $('#toDate').val();
		deliveryboy = $('#deliveryboy').val();
		getDataTable(orderCode, restaurantCode,deliveryboy, fromDate, toDate);
	});
	$("#btnClear").click(function () {
        $("#orderCode").val('').trigger('change');
		$("#restaurantCode").val('').trigger('change');
		$("#deliveryboy").val('').trigger('change');
		$("#fromDate").val('');
		$("#toDate").val('');
		getDataTable("","","","","");
    });
	getDataTable("", "", "","","");
	
    function getDataTable(p_orderCode, p_restaurantCode,p_deliveryboy, p_fromDate, p_toDate) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-pending")) {
            $("#dataTable-pending").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-pending").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/order/getcancelorderlist",
                type: "GET",
				data: {
                    "orderCode": p_orderCode,
                    "restaurantCode": p_restaurantCode,
					"deliveryBoyCode":p_deliveryboy,
					"fromDate":p_fromDate,
					"toDate":p_toDate,
                },
                complete: function (response) {
				},
            },
        });
    }
});