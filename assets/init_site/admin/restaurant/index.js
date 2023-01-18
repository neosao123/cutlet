const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
    
	$("#btnSearch").on("click", function (e) {
        var restname = $("#restname").val();
		var ownername = $("#ownername").val();
		var city = $("#city").val();
		var serviceable = $("#serviceable").val();
        getDataTable(restname, ownername, city, serviceable);
    });
	
	$("#btnClear").click(function () {
        $("#restname").val('').trigger('change');
		$("#ownername").val('').trigger('change');
		$("#city").val('').trigger('change');
		$("#serviceable").val('').trigger('change');
		getDataTable("","","","");
    });
	getDataTable("", "", "", "");
	
    function getDataTable(restname_p, ownername_p, city_p, serviceable_p) {
        $.fn.DataTable.ext.errMode = "none";
        if ($.fn.DataTable.isDataTable("#dataTable-Restaurant")) {
            $("#dataTable-Restaurant").DataTable().clear().destroy();
        }
        var dataTable = $("#dataTable-Restaurant").DataTable({
            stateSave: true,
            lengthMenu: [10, 25, 50, 200, 500, 700, 1000],
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            paging: true,
            ajax: {
                url: baseUrl + "/getRestaurantList",
                type: "GET",
				data: {
                    restname: restname_p,
                    ownername: ownername_p,
					city:city_p,
					serviceable:serviceable_p
                },
                complete: function (response) {
					operations();
					
                    $(".toggle").bootstrapSwitch({
							'size': 'mini',
							'onSwitchChange': function(event, state) {
								var code = $(this).attr('id');
								var isServiceable = $(this).data('seq');
								if(isServiceable==1){
								var action = $(this).bootstrapSwitch('state');
								if (action) {
									var flag = 1;
								} else {
									var flag = 0;
								}
								$.ajax({
									url: baseUrl + "/changeServiceable", 
									type: 'get',
									data: {
										'code': code,
										'flag': flag
									},
									success: function(data) {
										if (data) {
											toastr.success("Service Flag Updated", 'Restaurant', {
												"progressBar": true
											});
										} else {
											toastr.success("No Change!", 'Restaurant', {
												"progressBar": true
											});
										}
										getDataTable();
									}
								});
								}else{
									toastr.success("As Per Serviceable Restaurant is closed. You can not change status.", 'Restaurant', {
												"progressBar": true
											});	
                                     return false;											
								}
							},
							'AnotherName': 'AnotherValue'
						});
				},
            },
        });
    }
	
	function operations()
	{
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
						url: baseUrl + "/partner/delete",
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