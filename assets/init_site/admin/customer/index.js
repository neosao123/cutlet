const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
   $("#btnSearch").on("click", function (e) {
		var clientCode = $("#clientCode").val();
		var cityCode = $("#cityCode").val();
		var mobile = $("#mobile").val();
		var email = $("#email").val();
        getDataTable(clientCode, cityCode,mobile,email);
    });

    $("#btnClear").click(function () {
		$('#clientCode').val(null).trigger('change');
		$('#cityCode').val(null).trigger('change');
		$('#mobile').val(null).trigger('change');
		$('#email').val(null).trigger('change');
        getDataTable('','','','');
    });
    getDataTable('','','','');
    function getDataTable(p_clientCode,p_cityCode,p_mobile,p_email) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-customer")) {
            $("#dataTable-customer").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-customer").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getCustomerList",
                type: "GET",
				data:{
					'clientCode':p_clientCode,
					'cityCode':p_cityCode,
					'mobile':p_mobile,
					'email':p_email,
				},
                complete: function (response) {
                    operations();
                },
            },
        });
    }

    function operations() {
        $(document).on("click", ".delbtn", function (e) {
            e.preventDefault();
            var code = $(this).data("id");
            Swal.fire({
                title: "Are you sure?",
                text: "You want to delete this record",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: baseUrl + "/customer/delete",
                        type: "get",
                        data: {
                            code:code
                        },
                        success: function (data) {
                            if (data) {
                                Swal.fire({
                                    icon: "success",
                                    text: "Your record is deleted",
                                }).then(() => {
                                    getDataTable();
                                });
                            } else {
                                Swal.fire({
                                    icon: "success",
                                    text: "Your record is safe",
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
