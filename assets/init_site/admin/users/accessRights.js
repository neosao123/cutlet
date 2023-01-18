const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function () {
	$.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
	var ModulesData=[];
	var ModulesData1=[];
	var ModulesDataRes={};
	var subModules=[];
	var subModulesRes={};
	var obj;
	var moduleCode,subModuleCode='';
	var status=true;
	var id,code,moduleName,moduleIcon,displayUrl,routeUrl,sequence,type,subStatus='';
	var subModuleName,subModuleIcon='';
	var userCode=$('#userCode').val();
	var container = $("#load-container");
	container.addClass("loading");
	$.ajax({
		url:baseUrl+"/users/getUserAccessList",
		type:'GET',
		success:function(data){
			$('#userAccessTable').html(data);
			container.removeClass("loading");
			checkAll();
		},
		complete:function(){
		   $.ajax({
				url:baseUrl+"/users/getUserAccessEditList",
				type:'GET',
				data:{'userCode':userCode},
				success:function(json){
                    if(json!=false){
						var obj=JSON.parse(json);
						for(var i=0;i<obj['ModulesData'].length;i++){
							moduleCode=obj['ModulesData'][i]['code'];
							$('#chkModule'+moduleCode).prop('checked','checked');
							if(obj['ModulesData'][i]['subStatus']==true){
								for(var j=0;j<obj['ModulesData'][i]['subModules'].length;j++){
									subModuleCode=obj['ModulesData'][i]['subModules'][j]['code'];
									$('#chkSubModule'+subModuleCode).prop('checked','checked'); 
								}
							}
						} // End for loop
					}
				}, 
				complete:function(){
					makeStructure();
				} 
			}); 
		}
	});
	function makeStructure(){
		$.ajax({
			url:baseUrl+"/users/getAllPrivileges",
			type:'POST',
			success:function(json){ 
			},
			complete:function(data){
				$('#btnSubmit').click(function(){
					container.addClass("loading");
					var privelegeData=JSON.parse(data.responseText);
					var ModulesData=privelegeData.ModulesData;
					var finalResult={};
					var resultArray=[];
					finalResult['status']=true;
					for(var i=0;i<ModulesData.length;i++){
						var modulearr={};
						if($('#chkModule'+ModulesData[i].code).is(":checked")){
							modulearr['id']=ModulesData[i].id;
							modulearr['code']=ModulesData[i].code;
							modulearr['moduleName']=ModulesData[i].moduleName;
							modulearr['moduleIcon']=ModulesData[i].moduleIcon;
							modulearr['displayUrl']=ModulesData[i].displayUrl;
							modulearr['routeUrl']=ModulesData[i].routeUrl;
							modulearr['sequence']=ModulesData[i].sequence;
							modulearr['type']=ModulesData[i].type;
							modulearr['subStatus']=ModulesData[i].subStatus;
							
							if(ModulesData[i].subStatus){
								var res2=[];
								var subModulesData=ModulesData[i].subModules;
								for(var j=0;j<subModulesData.length;j++){
									var submodulearr={};
									if($('#chkSubModule'+subModulesData[j].code).is(":checked")){
										submodulearr['id']=subModulesData[j].id;
										submodulearr['code']=subModulesData[j].code;
										submodulearr['subModuleName']=subModulesData[j].subModuleName;
										submodulearr['subModuleIcon']=subModulesData[j].subModuleIcon;
										submodulearr['displayUrl']=subModulesData[j].displayUrl;
										submodulearr['routeUrl']=subModulesData[j].routeUrl;
										submodulearr['sequence']=subModulesData[j].sequence;
										res2.push(submodulearr);
										
									}
								}
								modulearr['subModules']=res2;
							}
							resultArray.push(modulearr);	
						}
					}
					finalResult['ModulesData']=resultArray;
					storeRights(JSON.stringify(finalResult));
				});
			}
		});
	}

	function storeRights(userRights,userAction){
		var userCode=$("#userCode").val();
		$.ajax({
			url:baseUrl+"/users/saveRights",
			type:'POST',
			data:{
				'userCode':userCode,
			    'userrights':userRights
			},
			success:function(json){
				var obj=JSON.parse(json);
				container.removeClass("loading");
				if(obj.status){
						Swal.fire({
                            icon: "success",
                            text: obj.message,
                            showConfirmButton: true,
                        }).then(function() {
							location.reload();
						});
				}else{
					Swal.fire({
                            icon: "warning",
                            title: "Oops...",
                            text: obj.message,
                        });
				}
			}
		});
	}
	
	function checkAll(){
		$('#checkAll_module').change(function() {
			if(this.checked) {
				$('.mainmodules').prop('checked',true);
			}else{
				$('.mainmodules').prop('checked',false);
			}
		});
		$('#checkAll_submodule').change(function() {
			if(this.checked) {
				$('.submodules').prop('checked',true);
				$('.mainmodules').prop('checked',true);
			}else{
				$('.submodules').prop('checked',false);
				$('.mainmodules').prop('checked',false);
			}
		});
		$('#checkAll_submoduleaction').change(function() {
			if(this.checked) {
				$('.submodules').prop('checked',true);
				$('.mainmodules').prop('checked',true);
				$('.submodulesaction').prop('checked',true);
			}else{
				$('.submodules').prop('checked',false);
				$('.mainmodules').prop('checked',false);
				$('.submodulesaction').prop('checked',false);
			}
		});
	}
});
