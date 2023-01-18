const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    
	$("#btnSearch").on("click", function (e) {
        var restname = $("#restname").val();
        getDataTable(restname);
    });
	
	$("#btnClear").click(function () {
        $("#restname").val('').trigger('change');
		getDataTable("");
    });
	getDataTable("");
	
    function getDataTable(restname_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-Rating")) {
            $("#dataTable-Rating").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-Rating").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getRatingList",
                type: "GET",
				data: {
                    restname: restname_p
                },
                complete: function (response) {
					operations();
				},
            },
        });
    }
	
	function operations()
	{
		 $(document).on("click", ".accept", function (e) {
			e.preventDefault();
			var id = $(this).data("id");
			var type = $(this).data("type");
			Swal.fire({
				title: "Are you sure to approve this rating?",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: "Yes, approve it!",
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: baseUrl + "/rating/changestatus",
						type: "get",
						data: {
							id: id,type:type
						},
						success: function (data) {
							if (data) {
								Swal.fire({
									icon: "success",
									text: "Rating Status has beed approved.",
								}).then(() => {
									getDataTable();
								});
							} else {
								Swal.fire({
									icon: "success",
									text: "Cancel change status of rating",
								});
							}
						},
						error: function (xhr, ajaxOptions, thrownError) {
							var errorMsg = "Ajax request failed: " + xhr.responseText;
							console.log("Ajax Request for patient data failed : " + errorMsg);
						},
					});
				} else {
					Swal.fire({
						icon: "success",
						text: "Cancel to change status of rating",
					});
				}
			});
		});
		$(document).on("click", ".reject", function (e) {
			e.preventDefault();
			var id = $(this).data("id");
			var type = $(this).data("type");
			Swal.fire({
				title: "Are you sure to Reject this rating?",
				icon: "warning",
				showCancelButton: true,
				confirmButtonColor: "#3085d6",
				cancelButtonColor: "#d33",
				confirmButtonText: "Yes, reject it!",
			}).then((result) => {
				if (result.isConfirmed) {
					$.ajax({
						url: baseUrl + "/rating/changestatus",
						type: "get",
						data: {
							id: id,type:type
						},
						success: function (data) {
							if (data) {
								Swal.fire({
									icon: "success",
									text: "Rating Status has beed reject.",
								}).then(() => {
									getDataTable();
								});
							} else {
								Swal.fire({
									icon: "success",
									text: "Cancel change status of rating",
								});
							}
						},
						error: function (xhr, ajaxOptions, thrownError) {
							var errorMsg = "Ajax request failed: " + xhr.responseText;
							console.log("Ajax Request for patient data failed : " + errorMsg);
						},
					});
				} else {
					Swal.fire({
						icon: "success",
						text: "Cancel to change status of rating",
					});
				}
			});
		});
	}

	
});