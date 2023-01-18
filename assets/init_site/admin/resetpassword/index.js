const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
		$("#btnSearch").on("click", function (e) {
		var city = $("#city").val();
        getDataTable(city);
    });

    $("#btnClear").click(function () {
        $("#city").val('');
        getDataTable('');
    });
    getDataTable('');
	$('#cityCode').select2();
    function getDataTable(city_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-deliveryBoy")) {
            $("#dataTable-deliveryBoy").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-deliveryBoy").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getDeliveryBoyList",
                type: "GET",
				 data: {
					city:city_p,
                },
                complete: function (response) {
                    operations();
                },
            },
        });
    }

    function operations() {
        $(document).on("click", ".resetBtn", function (e) {
            e.preventDefault();
            var code = $(this).attr('id');
            Swal.fire({
               title: "Can you confirm to reset the password for the selected user?",
				text: "Password will be reset to '123456'!",
				icon: "warning",
				showCancelButton: !0,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, Reset It!",
				cancelButtonText: "No",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl + "/resetPassword/resetDeliveryPassword",
                        type: "get",
                        data: {
                            code:code
                        },
						beforeSend: function (xhr){ 
							$('swal2-confirm').attr('disabled','disabled');
							$('swal2-cancel').attr('disabled','disabled');
						},
                        success: function (data) {
							$('swal2-confirm').removeAttr('disabled');
							$('swal2-cancel').removeAttr('disabled');
                            if (data) {
                                Swal.fire({
                                    icon: "success",
                                    text: "Successfully Reset the Password to '123456'!",
                                }).then(() => {
                                    getDataTable();
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    text: "Failed to reset password! Please try later.",
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
                        text: "Your record is safe",
                    });
                }
            });
        });
    }
});
