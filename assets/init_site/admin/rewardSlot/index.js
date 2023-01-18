const baseUrl = document.getElementsByTagName("meta").baseurl.content;

const btnSubmit = $(".btnsubmit");
$(document).ready(function () {
	$('.number_only').keypress(function (e) {
		if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
			return false;
		}
	});
    function resetForm() {
        $("#slotForm").removeClass("was-invalid");
        $(".invalid-feedback").remove();
        $("#slotForm")[0].reset();
    }
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

    btnSubmit.on("click", function () {
		var code = $('#code').val();
		var from = $('#from').val();
		var to = $('#to').val();
		var minusValue = $('#minusValue').val();
		var includingFrom=includingTo=isActive= isMinus=0;
		/*if($("#includingFrom").is(':checked')){
			includingFrom=1;
		}
		if($("#includingTo").is(':checked')){
			includingTo=1;
		}*/
		if($("#isActive").is(':checked')){
			isActive=1;
		}
		if($("#isMinus").is(':checked')){
			isMinus=1;
		}
        $.ajax({
            type: "post",
            url: baseUrl + "/rewardSlots/store",
            data: {
				'code':code,
				'from':from,
				'to':to,
				//'includingFrom':includingFrom,
				//'includingTo':includingTo,
				'minusValue':minusValue,
				'isMinus':isMinus,
				'isActive':isActive
			},
            dataType: "JSON",
            beforeSend: function () {
                btnSubmit.prop("disabled", true);
            },
            success: function (response) {
                if (response.hasOwnProperty("errors")) {
                    $("#slotForm").addClass("was-invalid");
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
                        $("#from").val("");
                        $("#to").val("");
                        $("#minusValue").val("");
						//$("#includingFrom").prop('checked',false);
						//$("#includingTo").prop('checked',false);
						$("#isActive").prop('checked',false);
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

    $("#slotForm").parsley({
        excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden",
    });

    getDataTable();

    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-slot")) {
            $("#dataTable-slot").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-slot").DataTable({
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
                url: baseUrl + "/getRewardSlotList",
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
                        url: baseUrl + "/rewardSlots/delete",
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
            $("#slotForm").removeClass("was-invalid");
            $(".invalid-feedback").remove();
            var code = $(this).data("id");
            $.ajax({
                type: "get",
                url: baseUrl + "/rewardSlots/edit",
                data: {
                    code: code,
                },
                dataType: "JSON",
                success: function (response) {
                    if (response.status == 200) {
                        $("#from").val(response.data.from);
                        $("#to").val(response.data.to);
                        $("#minusValue").val(response.data.minusValue);
						/*if (response.data.includingFrom == 1) {
                            $("#includingFrom").prop("checked", true);
                        }
						 if (response.data.includingTo == 1) {
                            $("#includingTo").prop("checked", true);
                        }*/
						 if (response.data.isMinus == 1) {
                            $("#isMinus").prop("checked", true);
                        }
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
function validateTo(){
	var from =$('#from').val();
	var to = $('#to').val();
	if(from!='' && to!='' && from>=to){
		Swal.fire("warning","To Value must be greater than from Value");
		$('#to').val('');
		$('#to').focus();
		return;
	}
}
