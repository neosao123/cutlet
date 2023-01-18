const baseUrl = document.getElementsByTagName("meta").baseurl.content;
$(document).ready(function(){
    $("#client").select2({});
	$("#cityCode2").select2({});
	$("#cityCode2").change(function(){
		var cityCode2 = [];
		$.each($("#cityCode2 option:selected"), function(){            
			cityCode2.push($(this).val());
		});
   		var id = $(this).val();
   		$.ajax({
   			type:'get',
   			url: baseUrl+"/notification/getCustomersListByCity",
   			data: {'cityCode':id},
   			success: function(d){
   				$("#client").html(d);
   			}
   		});
   	});
});