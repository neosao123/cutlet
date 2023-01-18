$(document).ready(function () {
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
    var fromdate = $("#fromDate").val();
	var todate=$("#toDate").val();

	$("#btnSearch").on("click", function (e) {
        var fromdate = $("#fromDate").val();
	    var todate=$("#toDate").val();
        getDataTable(fromdate,todate);
    });
	
	$("#btnClear").click(function () {
		 $("#fromDate").val('');
		 $("#toDate").val('');
         getDataTable('','');
        //location.reload();
    });
    getDataTable(fromdate,todate); 
	//$("#restaurantCode").select2({});
    function getDataTable(fromdate_p,todate_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#restaurant_commission")) {
            $("#restaurant_commission").DataTable().clear().destroy();
        }
        var dataTable = $("#restaurant_commission").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: ({
                url: baseUrl + "/getRestaurantCommission",
                type: "GET",
                data: {
					fromdate:fromdate_p,
					todate:todate_p
                },
                complete: function (response) {
                    $('#total').text(response.responseJSON['restaurantAmount1']);
                },
            }),
        });
    }

});
