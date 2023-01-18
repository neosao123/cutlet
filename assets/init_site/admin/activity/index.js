const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
	$("#btnSearch").on("click", function (e) {
       var keyDate = $('#date').val();
       getDataTable(keyDate);
    });
	
	$("#btnClear").click(function () {
      document.getElementById("date").value = "<?= date('Y-m-d') ?>";
      keyDate = $('#date').val();
      getDataTable(keyDate);
    });
	var keyDate = $('#date').val();
    getDataTable(keyDate);
    function getDataTable(p_keyDate) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-activitylog")) {
            $("#dataTable-activitylog").DataTable().clear().destroy(); 
        }
        var dataTable = $("#dataTable-activitylog").DataTable({
            stateSave: false,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: false, 
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getActivityList",
                type: "GET",
				data:{
					'date': p_keyDate,
				},
                complete: function (response) {
                    //operations();
                },
            },
        });
    }


});
