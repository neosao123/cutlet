const baseUrl = document.getElementsByTagName("meta").baseurl.content;

const btnSubmit = $(".btnsubmit");
$(document).ready(function () {
    function resetForm() {
        $("#cuisineform").removeClass("was-invalid");
        $(".invalid-feedback").remove();
        $("#cuisineform")[0].reset();
    }
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    btnSubmit.on("click", function () {
        var formData = new FormData($("#cuisineform")[0]);
        $.ajax({
            type: "post",
            url: baseUrl + "/cuisine/store",
            data: formData,
            dataType: "JSON",
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function () {
                btnSubmit.prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $("#cuisineform").addClass("was-invalid");
                    $(".invalid-feedback").remove();
                    $.each(response.errors, function (i, v) {
                        let spnerr = '<div class="invalid-feedback">' + v[0] + "</div>";
                        $("[name='" + i + "']").after(spnerr);
                    });
                    return false;
                } else {
                    if (response.status == 200) {
                        getDataTable();
                        resetForm();
                        Swal.fire({
                            icon: "success",
                            text: response.msg,
                            showConfirmButton: true,
                        });
                        $("#code").val("");
                    } else {
                        //alert(response.msg);
                        Swal.fire({
                            icon: "warning",
                            title: "Oops...",
                            text: response.msg,
                        });
                        return false;
                    }
                }
            },
            error: function () {
                //alert("Something went wroong");
                Swal.fire({
                    icon: "warning",
                    title: "Oops...",
                    text: "Something went wrong",
                });
            },
            complete: function () {
                btnSubmit.removeAttr("disabled");
            },
        });
    });

    $("#cuisineform").parsley({
        excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden",
    });

    getDataTable();

    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-cuisine")) {
            $("#dataTable-cuisine").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-cuisine").DataTable({
            dom: "Blfrtip",
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            buttons: ["copy", "csv", "excel", "pdf", "print"],
            processing: true,
            serverSide: true,
            ordering: true,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getCuisineList",
                type: "GET",
                complete: function (response) {
                    operations();
                },
            },
        });
    }

    function operations() {
        $(".delete_id").on("click", function (e) {
            e.preventDefault();
            var code = $(this).attr("id");
            //var href = $(this).attr("href");
            //var message = $(this).data("confirm");
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
                        url: baseUrl + "/cuisine/delete",
                        type: "get",
                        data: {
                            code: code,
                        },
                        success: function (data) {
                            if (data) {
                                Swal.fire({
                                    icon: "success",
                                    text: "Your record is deleted",
                                });
								
                                getDataTable();
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
        $(".edit").on("click", function (e) {
            e.preventDefault();
            $("#cuisineform").removeClass("was-invalid");
            $(".invalid-feedback").remove();
            var code = $(this).data("id");
            $.ajax({
                type: "get",
                url: baseUrl + "/cuisine/edit",
                data: {
                    code: code,
                },
                dataType: "JSON",
                success: function (response) {
					debugger;
                    if (response.status == 200) {
                        $("input[name='cuisineName']").val(response.data.cuisineName);
                        $("input[name='code']").val(response.data.code);
						if(response.data.cuisinePhoto!='' && response.data.cuisinePhoto!=null){
							$('#preview').removeClass("d-none")
							$("#src_id").attr("src","../uploads/restaurant/cuisine/" +response.data.cuisinePhoto);
							$("#href_id").attr("href","../uploads/restaurant/cuisine/" +response.data.cuisinePhoto);
						}else{
							$('#preview').addClass("d-none")
							$("#src_id").attr("src","");
							$("#href_id").attr("href","");
						}
                        if (response.data.isActive == 1) {
                            $("#isActive").prop("checked", true);
                        }
                    }
                },
            });
        });
    }
});
