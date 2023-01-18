const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
 $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
	var restaurantCode = "",
		orgFromDate = $("#fromDate").val(); 
		ordgToDate = $("#toDate").val(); 
		$('#fromDate').datepicker({
			dateFormat: "mm/dd/yy",
			showOtherMonths: true,
			selectOtherMonths: true,
			autoclose: true,
			changeMonth: true,
			changeYear: true,
			todayHighlight: true,
			orientation: "bottom left",
		});
		$('#toDate').datepicker({
			dateFormat: "mm/dd/yy",
			showOtherMonths: true,
			selectOtherMonths: true,
			autoclose: true,
			changeMonth: true,
			changeYear: true,
			todayHighlight: true,
			orientation: "bottom left",
		});
	$("#btnSearch").on("click", function (e) {
        var restaurantCode = $("#restaurantCode").val();
        var fromDate = $('#fromDate').val(); 
        var toDate = $('#toDate').val(); 
        getDataTable(restaurantCode, fromDate,toDate);
    });

    $("#btnClear").click(function () {
       $("#restaurantCode").val('').trigger('change');
       $("#fromDate").val(orgFromDate);
       $("#addDate").val(ordgToDate);
        getDataTable("", orgaddDate);
    });
	//getDataTable("", orgFromDate,ordgToDate)
	$('#dataTable-commission').DataTable();
    function getDataTable(p_restaurantCode, p_fromDate,p_toDate) {
        if ($.fn.DataTable.isDataTable("#dataTable-commission")) {
            $('#dataTable-commission').DataTable().clear().destroy();
        } 
        $('#dataTable-commission').DataTable({
            stateSave: true,
            "processing": true,
            "serverSide": true,
            "order": [],
			"searching": false,
			"ajax": {
				url: baseUrl + "/other/getRestaurantCommissionList",
				data: {
					'restaurantCode': p_restaurantCode,
					'fromDate': p_fromDate,
					'toDate' : p_toDate
				},
				type: "GET", 
				complete: function(json) {
					$('#total').text(json.responseJSON['totalRestaurantAmount']);
                         $(".blue").click(function() {
                             var code = $(this).data('vendor');
                             $(".modal-body").empty();
							kfromDate = moment(p_fromDate, "YYYY/MM/DD").format('DD/MM/YYYY');
							kToDate = moment(p_toDate, "YYYY/MM/DD").format('DD/MM/YYYY');
							$("#modal-title").html('View Restaurant Orders Commission :<br>'+kfromDate+' To '+kToDate);
                             $.ajax({
                                 url: baseUrl + "/other/viewResCurrentHistory",
                                 type: "GET",
                                 data: {
                                     'restaurantCode': code,
									'fromDate': p_fromDate,
									'toDate': p_toDate,
                                 },
                                 datatype: "text",
                                 success: function(data) {
                                     $(".modal-body").html(data);
                                 }
                             });
                         });
					
					}
				}
			});
	} 

	
}); 