const baseUrl = document.getElementsByTagName("meta").baseurl.content;

const btnSubmit = $(".btnsubmit");
$(document).ready(function () {
    function resetForm() {
        $("#sliderform").removeClass("was-invalid");
        $(".invalid-feedback").remove();
        $("#sliderform")[0].reset();
    }
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });

    btnSubmit.on("click", function () {
		var sliderImage = $('#sliderImage').val().trim(); 
		var code=$('#code').val();
		var mode=1;
		if(code!='' && code!=null){
			mode=2;
		}
		if(mode==1 && (sliderImage=='' || sliderimage==null)){
			$('#err').html('Please select slider image first!');
		}else{ 
			var sliderImage='';
			if($("#sliderImage").val()!=''){
				var sliderImage = $("#sliderImage")[0].files[0];
			}
			var isActive='';
			if($("#isActive").is(':checked')){
				isActive=1;
			}
			var formData = new FormData();
			formData.append('sliderImage', sliderImage);
			formData.append('code', code);
			formData.append('isActive', isActive);
			
			$.ajax({
				type: "post",
				url: baseUrl + "/foodSlider/store",
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
						$("#sliderform").addClass("was-invalid");
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
							$('#preview').addClass("d-none")
							$("#src_id").attr("src","");
							$("#href_id").attr("href","");
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
		}
    });

    $("#sliderform").parsley({
        excluded: "input[type=button], input[type=submit], input[type=reset], input[type=hidden], [disabled], :hidden",
    });

    getDataTable();

    function getDataTable() {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-slider")) {
            $("#dataTable-slider").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-slider").DataTable({
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
                url: baseUrl + "/getSliderList",
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
                        url: baseUrl + "/foodSlider/delete",
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
            $("#sliderform").removeClass("was-invalid");
            $(".invalid-feedback").remove();
            var code = $(this).data("id");
            $.ajax({
                type: "get",
                url: baseUrl + "/foodSlider/edit",
                data: {
                    code: code,
                },
                dataType: "JSON",
                success: function (response) {
                    if (response.status == 200) {
                        $("input[name='code']").val(response.data.code);
						if(response.data.sliderPhoto!='' && response.data.sliderPhoto!=null){
							$('#preview').removeClass("d-none")
							$("#src_id").attr("src","../uploads/restaurant/sliderimage/" +response.data.sliderPhoto);
							$("#href_id").attr("href","../uploads/restaurant/sliderimage/" +response.data.sliderPhoto);
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
	
	var reader = new FileReader();
		$("#sliderImage").change(function(){
			var file =  $('#sliderImage')[0].files[0];
			fileType= file.type.split('/');
			if(fileType[0]=='image'){
				$('#err').html("");
			    fileDiamensionsValidate(this,480,320);  
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
						$('#err').html('Please upload image of at least '+ width+'(w) X '+height+'(h) dimension');
						$("#sliderImage").val("");
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
					$('#err').html('Please upload home slider image');
					$("#sliderImage").val("");
					return false;
				};
				img.src = _URL.createObjectURL(file);
			} else {
				return false;
			}
		}
});
