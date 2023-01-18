const baseUrl = document.getElementsByTagName("meta").baseurl.content;

const btnSubmit = $(".btnsubmit");
$(document).ready(function () {
    function resetForm() {
        $("#categoryform").removeClass("was-invalid");
        $(".invalid-feedback").remove();
        $("#categoryform")[0].reset();
    }
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    btnSubmit.on("click", function () {
        var formData = new FormData($("#categoryform")[0]);
        $.ajax({
            type: "post",
            url: baseUrl + "/menuCategory/store",
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
                    $("#categoryform").addClass("was-invalid");
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

    $("#categoryform").parsley({
        excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden",
    });

    getDataTable();

    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-category")) {
            $("#dataTable-category").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-category").DataTable({
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
                url: baseUrl + "/getCategoryList",
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
                        url: baseUrl + "/menuCategory/delete",
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
            $("#categoryform").removeClass("was-invalid");
            $(".invalid-feedback").remove();
            var code = $(this).data("id");
            $.ajax({
                type: "get",
                url: baseUrl + "/menuCategory/edit",
                data: {
                    code: code,
                },
                dataType: "JSON",
                success: function (response) {
                    if (response.status == 200) {
                        $("input[name='menuCategoryName']").val(response.data.menuCategoryName);
                        $("input[name='priority']").val(response.data.priority);
                        $("input[name='code']").val(response.data.code);
                        if (response.data.isActive == 1) {
                            $("#isActive").prop("checked", true);
                        }
                    }
                },
            });
        });
    }
});
