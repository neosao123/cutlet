const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    var username = $("#username").val();
    var designation = $("#designation").val();
    var city = $("#city").val();
    var role = $("#role").val();
	
	$("#btnSearch").on("click", function (e) {
        var username = $("#username").val();
		var designation = $("#designation").val();
		var city = $("#city").val();
		var role = $("#role").val();
        getDataTable(username, designation, city, role);
    });

    $("#btnClear").click(function () {
        location.reload();
    });
	
    getDataTable(username, designation, city, role);
    function getDataTable(username_p, designation_p, city_p, role_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-Users")) {
            $("#dataTable-Users").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-Users").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getuserslist",
                type: "GET",
				 data: {
                    username: username_p,
                    designation: designation_p,
					city:city_p,
					role:role_p
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
			debugger
            var id = $(this).data("id");
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
					debugger
                    $.ajax({
                        url: baseUrl + "/users/delete",
                        type: "get",
                        data: {
                            id: id,
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
