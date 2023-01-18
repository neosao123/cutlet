 //const baseUrl = document.getElementsByTagName("meta").baseurl.content;
 function clearCategory(){
	$("#customizedCategoryCode").val("");
    $("#categoryTitle").val("")
	$("#categoryType").val("");
	$("#isCateEnabled").prop('checked',false);
}

function clearSubCategory(){
    $(".subAddonTitle").val("");
    $(".subAddonPrice").val(""); 
    $(".subAddonEnabled").prop('checked',false);
}
	
$("body").on('click',".deleteCustomizedCategory",function(e){
	var code = $(this).attr('id');
	code = code.substring(7);
	$.ajax({
		url: baseUrl+'/restaurantItems/deleteAddonCategory',
		data:{
			'code':code
		},
		type:'get',
		success:function(response){   
			if(response==true){
				toastr.success("Category deleted successfully", 'Delete Category', { "progressBar": true });  
				var prevAnchor = $('#'+code+'-tab').prev('a').attr('id');
				$("#"+prevAnchor).addClass('active');
				$("#"+prevAnchor).addClass('show');
				$("#"+code+"-tab").remove();
				$("#"+code).remove();
				$("#deltab_"+code).remove();
			}else{
				toastr.error("Failed to delete Category", 'Delete Category', { "progressBar": true });  
			}
		}
	});
});

	
	$("body").on('click',".lineDelete",function(e){
		var code = $(this).data('id');    
		$.ajax({
			url:baseUrl+'/restaurantItems/deleteAddonCategory',
			data:{'code':code},
			type:'get',
			success:function(response){   
				if(response==true){
					toastr.success("Add-on deleted successfully", 'Delete Add-on', { "progressBar": true });  
					$("#row_"+code).remove();
				}else{
					toastr.error("Failed to delete Add-on", 'Delete Add-on', { "progressBar": true });  
				}
			}
		});
	});
	
	$("body").on('click',".addSubCategory",function(e){
		var cateCode = $(this).data('id');                
		var subTitle = $("#subTitle"+cateCode).val().trim();
		var price = $("#price"+cateCode).val().trim(); 
		var isCateEnabled  = 0;
		if($("#isEnabled"+cateCode).is(':checked')) isCateEnabled = 1;
		if(subTitle !=''){
			if(price !=''){
				if(subTitle.length>2){
					$.ajax({
						url:baseUrl+'/restaurantItems/addAddonLine',
						data:{'cateCode':cateCode,'subTitle':subTitle,'price':price,'isCateEnabled':isCateEnabled},
						type:'get',
						success:function(response){  
							var res = JSON.parse(response);
							if(res.status=='true'){
								clearSubCategory();
								toastr.success(res.message, 'Add On', { "progressBar": true });  
								var enabled = '<span class="badge badge-danger">No</span>';
								if(isCateEnabled==1) enabled = '<span class="badge badge-success">Yes</span>'; 
								var option = '<a class="btn btn-sm text-white btn-danger lineDelete" data-id="'+res.code+'"><i class="fa fa-trash"></i></a>';
								var row = '<tr id="row_'+res.code+'"><td>'+subTitle+'</td><td>'+price+'</td><td>'+enabled+'</td><td>'+option+'</td></tr>';
								$("#tbd"+cateCode).append(row);  
							}else if(res.status=='false'){
								toastr.error(res.message, 'Vendor Item', { "progressBar": true });
							}
							else
							{
								toastr.info(res.message, 'Restaurant Item', { "progressBar": true });
							}
						} 
					});
				}else{ 
					toastr.error('Valid Subtitle is required.', 'Restaurant Item', { "progressBar": true });	
					$("#subTitle"+cateCode).val('');
					$("#subTitle"+cateCode).focus();
				}
			}else{
				toastr.error('Price is required', 'Restaurant Item', { "progressBar": true });
				$("#price"+cateCode).focus();
			}
		}else {
		    toastr.error('Subtitle is required.', 'Restaurant Item', { "progressBar": true });	
			$("#subTitle"+cateCode).focus();
		}
	});
	
	$("body").on('click',".editCustomizedCategory",function(e){
		var code = $(this).attr('id');
		code = code.substring(7);
		$.ajax({
			url:baseUrl+'/restaurantItems/getAddonCategoryData',
			data:{'code':code},
			type:'get',
			success:function(response){  
				if(response != ""){
					var res = JSON.parse(response);
					$("#customizedCategoryCode").val(res.code);
					$("#categoryTitle").val(res.categoryTitle);
					$("#categoryType").val(res.categoryType); 
					if(res.isEnabled == 1){
						$("#isCateEnabled").prop('checked',true);
					}else{
						$("#isCateEnabled").prop('checked',false);
					}
				}
				else
				{
					toastr.error("Something went Wrong.", 'Edit Category', { "progressBar": true });  
				}
			}
		});
	});
	
	$("body").on('click',"#addCustomizedCategory",function(e){ 
		var customizedCategoryCode = $("#customizedCategoryCode").val().trim();                
		var categoryTitle = $("#categoryTitle").val().trim();
		var categoryType = $("#categoryType").val().trim();
		var itemCode = $("#itemCode").val().trim();
		var url = baseUrl+'/restaurantItems/addAddonCategory';
		if(customizedCategoryCode!="" && customizedCategoryCode!=undefined) url = baseUrl+'/restaurantItems/updateAddonCategory';
		var isCateEnabled  = 0;
		if($("#isCateEnabled").is(':checked')) isCateEnabled = 1;
		if(categoryType=='choice'){
			var categoryTypeTitle = 'Choice';
		}else{
			var categoryTypeTitle = 'Add On';
		}
		if(categoryTitle.length>3)
		{
			if(categoryType!="" && categoryType!=undefined)
			{
			$.ajax({
				url:url,
				data:{'customizedCategoryCode':customizedCategoryCode,'vendorItemCode':itemCode,'categoryTitle':categoryTitle,'categoryType':categoryType,'isCateEnabled':isCateEnabled},
				type:'get',
				success:function(response) {
					var res = JSON.parse(response);
					if(res.status=='true'){
						if(customizedCategoryCode!="" && customizedCategoryCode!=undefined){
							clearCategory();
							var updatedtitle = res.updatedtitle;
							$('#'+customizedCategoryCode+'-tab').html(updatedtitle+' - '+categoryTypeTitle+' <span class="mr-1 btn btn-warning btn-xs deleteCustomizedCategory" id="deltab_'+customizedCategoryCode+'"><i class="fa fa-trash"></i></span><span class="mr-1 btn btn-danger btn-xs editCustomizedCategory" id="edttab_'+customizedCategoryCode+'"><i class="ti-pencil-alt"></i></span>');
							toastr.success(res.message, 'Category', { "progressBar": true });
						}else{
							clearCategory();
							$(".nav-link").removeClass('active');
							$(".tab-pane").removeClass('active');
							var isStatus = 'Disabled';
                            if (isCateEnabled == 1) isStatus = 'Enabled';
							
							toastr.success(res.message, 'Addon Category', { "progressBar": true });  
								var html = '<a class="nav-link active" id="'+res.code+'-tab" data-toggle="pill" href="#'+res.code+'" role="tab" aria-controls="'+res.code+'" aria-selected="true"><div class="row"><div class="col-md-7">'+categoryTitle+' - '+categoryTypeTitle +' -  '+ isStatus + '</div><div class="col-md-5"><span class="mr-1 ml-2 btn btn-warning btn-xs deleteCustomizedCategory" id="deltab_'+res.code+'"><i class="fa fa-trash"></i></span><span class="mr-1 btn btn-danger btn-xs editCustomizedCategory" id="edttab_'+res.code+'"><i class="ti-pencil-alt"></i></span></div></div></a>';
							var inputSubHtml =  '<div class="row">';
							inputSubHtml += '<div class="col-sm-5 mb-3"><label for="subTitle'+res.code+'">Addon Title:</label><input id="subTitle'+res.code+'" name="subTitle" class="form-control subAddonTitle"></div>';
							inputSubHtml += '<div class="col-sm-5 mb-3"><label for="price'+res.code+'">Price:</label> <input type="number" id="price'+res.code+'" name="price" class="form-control subAddonPrice"></div>';
							inputSubHtml += '<div class="col-sm-2 mb-3"><label for="categoryType">Enabled:</label><div class="custom-control custom-checkbox"><input type="checkbox" value="1" class="custom-control-input subAddonEnabled" id="isEnabled'+res.code+'" name="isEnabled"><label class="custom-control-label" for="isEnabled'+res.code+'">Enabled</label></div></div>';
							inputSubHtml += '<div class="col-sm-4 mb-3"><button type="button" class="btn btn-info btn-sm addSubCategory" data-id="'+res.code+'"><I class="fa fa-plus"></i> Sub Category</button></div>';
							inputSubHtml += '</div>';
							var table='<table style="width:100%" class="table table-bordered" id="tbl'+res.code+'"><thead><tr><th>Subtitle</th><th>Price</th><th>Enabled</th><th>#</th></tr></thead><tbody id="tbd'+res.code+'">';
							var html2 = '<div class="tab-pane fade show active" id="'+res.code+'" role="tabpanel" aria-labelledby="'+res.code+'-tab">'+inputSubHtml+table+'</div>';
							$(".nav-pills").append(html);  
							$("#v-pills-tabContent").append(html2);  
						}
					}
					else if(res.status=='false')
					{
						clearCategory();
						toastr.error(res.message, 'Restaurant Item', { "progressBar": true });
					}
					else
					{
						clearCategory();
						toastr.error(res.message, 'Restaurant Item', { "progressBar": true });
					}
				} 
			});
		  }else{
			toastr.error('Type is required.', 'Restaurant Item', { "progressBar": true });  
		  }
		}
		else{ 
			toastr.error('Valid Category is required.', 'Restaurant Item', { "progressBar": true });	
			$("#categoryTitle").val('');
			$("#categoryTitle").focus(); 
		}
	}); 