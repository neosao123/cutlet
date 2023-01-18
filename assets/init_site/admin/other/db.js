const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {

	 $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
	var deliveryboyCode = "",
		orgaddDate = $("#addDate").val(); 
		$('#addDate').datepicker({
		format: "dd-mm-yyyy",
		showOtherMonths: true,
		selectOtherMonths: true,
		autoclose: true,
		changeMonth: true,
		changeYear: true,
		todayHighlight: true,
		orientation: "bottom left",
	});
	$("#btnSearch").on("click", function (e) {
        var deliveryboyCode = $("#deliveryboyCode").val();
        var addDate = $('#addDate').val(); 
        getDataTable(deliveryboyCode, addDate);
    });

    $("#btnClear").click(function () {
       $("#deliveryboyCode").val('').trigger('change');
       $("#addDate").val(orgaddDate);
        getDataTable("", orgaddDate);
    });
	$('#dataTable-commission').DataTable();
    function getDataTable(p_deliveryboyCode, p_addDate) {
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
				url: baseUrl + "/other/getDeliveryBoyCommissionList",
				data: {
					'deliveryboyCode': p_deliveryboyCode,
					'date': p_addDate
				},
				type: "GET", 
				complete: function(json) {
					$(".blue").click(function() {
						var code = $(this).data('seq');
						var order = $(this).data('order');
						var  p_addDate = $('#addDate').val(); 
						$(".modal-body").empty();
						$.ajax({
							url: baseUrl + "/other/viewCurrentHistory",
							type: "GET",
							data: {
								code: code,date:p_addDate,order:order
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