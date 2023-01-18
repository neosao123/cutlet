const baseUrl = document.getElementsByTagName("meta").baseurl.content;
 function isDecimal(evt) {
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if ((charCode >= 48 && charCode <= 57) || (charCode >= 96 && charCode <= 105) || charCode == 8 || charCode == 9 || charCode == 37 ||
		charCode == 39 || charCode == 46 || charCode == 190) {
		return true;
	} else {
		return false;
	}
}

function checkPrice() {
	 var itemPrice = Number($('#salePrice').val());
	 var result = isNaN(itemPrice);
	 if(result==true){
		 $('#salePrice').val('');
            toastr.error('Sale Price is not valid', 'Restaurant Item', {
                "progressBar": false
            });
            return false;

	 }
	 else{
	   $('#salePrice').val(itemPrice.toFixed(2));
	 }
}

function checkPackPrice() {
	 var itemPrice = Number($('#itemPackagingPrice').val());
	 var result = isNaN(itemPrice);
	 if(result==true){
		 $('#itemPackagingPrice').val('');
            toastr.error('Packaging Price is not valid', 'Restaurant Item', {
                "progressBar": false
            });
            return false;

	 }
	 else{
	   $('#itemPackagingPrice').val(itemPrice.toFixed(2));
	 }
}

$(document).ready(function () {
	var restitem = $("#restitem").val();
    var restname = $("#restname").val();
    var menucategory = $("#menucategory").val();
    var approvedstatus = $("#status").val();
	
	$("#btnSearch").on("click", function (e) {
        var restitem = $("#restitem").val();
		var restname = $("#restname").val();
		var menucategory = $("#menucategory").val();
		var approvedstatus = $("#status").val();
        getDataTable(restitem, restname, menucategory, approvedstatus);
    });
	
	$("#btnClear").click(function () {
         $('#restitem').val('').trigger('change');
         $('#menucategory').val('').trigger('change');
         $('#status').val('').trigger('change');
         $('#restname').val('').trigger('change');
         getDataTable('','','','');
        //location.reload();
    });
	
    getDataTable('','','','');
	$("#restaurantCode").select2({});
	
    function getDataTable(restitem_p, restname_p, menucategory_p, approvedstatus_p) {
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
                url: baseUrl + "/getItemList",
                type: "GET",
				data: {
                    restitem: restitem_p,
                    restname: restname_p,
					menucategory:menucategory_p,
					approvedstatus:approvedstatus_p
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
	
	 $("body").on("change", "#menuCategoryCode", function(e) {
        var menuCategoryCode = $(this).val();
		$("#menuSubCategoryCode").empty();
        if (menuCategoryCode != "") {
            $.ajax({
                type: "GET",
				data: {
					'menuCategoryCode': menuCategoryCode
				},
                url: baseUrl + "/getSubcategoryDetails",
                success: function (res) {
					debugger;
                    if (res != undefined || res != "") {
						$("#menuSubCategoryCode").append(res);
					} 
                },
            });
        }
    });
	
	var reader = new FileReader();
		$("#itemImage").change(function(){
			var file =  $('#itemImage')[0].files[0];
			fileType= file.type.split('/');
			if(fileType[0]=='image'){
				$('#err').html("");
			    fileDiamensionsValidate(this,640,960);  
			} else {
				$(this).val("");
				$('#err').html("Please upload an image (jpeg,jpg,png,bmp)!");
				return false;
			}
		});
		function readURL(input, image_ctrl_id) {
			if (input.files && input.files[0]) {
				var reader = new FileReader();
				reader.onload = function (e) {
					$('#preview').removeClass("d-none")
					$('#' + image_ctrl_id).attr('src', e.target.result);
					$('#href_id').attr('src', e.target.result);
				}
				reader.readAsDataURL(input.files[0]);
			}
		}
		var _URL = window.URL || window.webkitURL;
		function fileDiamensionsValidate(fdata,width,height){
			var file, img;
			if ((file = fdata.files[0])) {
				img = new Image();
				img.onload = function() {                
					if(this.width < width || this.height < height){
						$("#previous_error").html('');
						$('#err').html('Please upload image of at least '+ width+'(w) X '+height+'(h) dimension');
						$("#itemImage").val("");
						$('#preview').addClass("d-none")
						$("#src_id").attr("src","");
						$("#href_id").attr("href","");
						return false;
					} else {
						$('#err').html('');
						var image_ctrl_id = "src_id";
						readURL(fdata, image_ctrl_id);
					}
				};
				img.onerror = function() {
					$('#err').html('Please upload restaurant image');
					$("#itemImage").val("");
					return false;
				};
				img.src = _URL.createObjectURL(file);
			} else {
				return false;
			}
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
	

