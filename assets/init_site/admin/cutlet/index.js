const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
   $("#btnSearch").on("click", function (e) {
		var clientCode = $("#clientCode").val();
		var cutletStatus = $("#status").val();
        getDataTable(clientCode, cutletStatus);
    });

    $("#btnClear").click(function () {
		$('#clientCode').val(null).trigger('change');
		$('#status').val(null).trigger('change');
        getDataTable('','');
    });
    getDataTable('','','','');
    function getDataTable(p_clientCode,p_status) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-cutlet")) {
            $("#dataTable-cutlet").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-cutlet").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getCutletList",
                type: "GET",
				data:{
					'clientCode':p_clientCode,
					'status':p_status,
				},
                complete: function (response) {
                },
            },
        });
    }
});
