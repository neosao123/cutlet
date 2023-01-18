const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    getDataTable();
	 $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-slots")) {
            $("#dataTable-slots").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-slots").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getSlotsList",
                type: "GET",
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
                        url: baseUrl + "/deliveryCharges/delete",
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

function validateToKm(){ 
		var toKM = $('#toKM').val();
		var fromKM = $('#fromKM').val();
		if(Number(toKM)<=Number(fromKM) && Number(fromKM)!='' && Number(fromKM)!=0 && Number(toKM)!='' && Number(toKM)!=0){
			toastr.error('To KM should be greater than From KM', 'Delivery Slots', { "progressBar": true });
			$('#toKM').val('');
			$('#toKM').focus();
			return;
		}
	}


function validateOverlappingSlots(){
	debugger;
	var fromKM = $('#fromKM').val();
	var cityCode = $('#cityCode').val();
	if(Number(fromKM)!='' && Number(fromKM)!=0 && cityCode!=''){
		$.ajax({
			url: baseUrl + "/deliveryCharges/checkOverlappingSlot",  
			method:"POST",
			data:{
				'fromKM':fromKM,
				'cityCode':cityCode,
			},
			success: function(response){
				debugger;
				if(response==1){
					toastr.error('Delivery slot should not be overlapped', 'Delivery Slots', { "progressBar": true });
					$('#fromKM').val('');
					$('#fromKM').focus();
					return;
				}
			}
		});
	}
}