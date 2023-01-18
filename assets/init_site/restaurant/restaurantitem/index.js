const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    getDataTable();
	$("#restaurantCode").select2({});
    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-item")) {
            $("#dataTable-item").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-item").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getRestaurantItemList",
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
                        url: baseUrl + "restaurantItem/delete",
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

	$('input[type=number]').on('mousewheel',function(e){ $(this).blur(); });
		$('input[type=number]').on('keydown',function(e) {
		var key = e.charCode || e.keyCode;
		if(key == 38 || key == 40 ) {
			e.preventDefault();
		} else {
			return;
		}
	});
	
	$("body").on("change","#itemImage",function(e){ 
		$("#photoError").empty();
		var file =  $('#itemImage')[0].files[0];
		fileType= file.type.split('/');
		if(fileType[0]=='image'){
			var filename = $(this).val();
			var lastIndex = filename.lastIndexOf("\\");
			if (lastIndex >= 0) {
				filename = filename.substring(lastIndex + 1);
			}
			$(this).next("label").text(filename); 
		} else {
			$(this).val("");
			$("#photoError").html("Please upload an image (jpeg,jpg,png,bmp)!");
			return false;
		} 
	});

	$("body").on("change","#menuCategoryCode",function(e){
		var this_code = $(this).val().trim();
		if (this_code!=undefined || this_code!="")
		{ 
			$.ajax({
				url:base_path+'index.php/Food/Vendoritem/getsubCategoryItems',
				data:{
					'menuCategoryCode':this_code
				},
				type:'get',
				success:function(response){
					if(response!=undefined|| response!=""){
						$("#menuSubCategoryCode").empty();
						$("#menuSubCategoryCode").append(response);
					}
				}	
			}) ;
		}
	});
