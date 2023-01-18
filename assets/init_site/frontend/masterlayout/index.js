var change_theme = $("#change-theme");
var theme_tag = $("#theme-tag");
var theme_view = $("#theme-view");
$(function () {
    "use strict";

    localStorage.executesAfter = "true";
    var theme = localStorage.getItem("my_theme");
    if (theme == undefined && theme == null) {
        theme = "light";
        window.localStorage.setItem("my_theme", "light");
    }

    $("body").attr("data-theme", theme);
    if (theme === "light" || theme === "" || theme === undefined) {
        $("#main-wrapper").attr("data-navbarbg", "skin6");
        $("nav").removeClass("navbar-dark").addClass("navbar-light");
        $("#navbarSupportedContent").attr("data-navbarbg", "skin6");
    } else {
        $("#main-wrapper").attr("data-navbarbg", "skin5");
        $("nav").removeClass("navbar-light").addClass("navbar-dark");
        $("#navbarSupportedContent").attr("data-navbarbg", "skin5");
    }

    if (theme == "dark") {
        theme_view.attr("checked", false);
        theme_tag.removeClass("fas").removeClass("fa-moon");
        theme_tag.addClass("far").addClass("fa-sun");
    } else {
        theme_view.attr("checked", true);
        theme_tag.removeClass("far").removeClass("fa-sun");
        theme_tag.addClass("fas").addClass("fa-moon");
    }

    if (change_theme.length > 0) {
        change_theme.on("click", function (event) {
            let theme1 = "";
            if (theme_tag.hasClass("fa-moon")) {
                theme1 = "dark";
                theme_view.attr("checked", true);
                theme_tag.removeClass("fas").removeClass("fa-moon");
                theme_tag.addClass("far").addClass("fa-sun");
            } else {
                theme1 = "light";
                theme_view.attr("checked", false);
                theme_tag.removeClass("far").removeClass("fa-sun");
                theme_tag.addClass("fas").addClass("fa-moon");
            }
            setTimeout(() => {
                theme_view.trigger("change");
                window.localStorage.setItem("my_theme", theme1);
                if (theme1 === "light" || theme1 === "" || theme1 === undefined) {
                    $("#main-wrapper").attr("data-navbarbg", "skin6");
                    $("nav").removeClass("navbar-dark").addClass("navbar-light");
                    $("#navbarSupportedContent").attr("data-navbarbg", "skin6");
                } else {
                    $("#main-wrapper").attr("data-navbarbg", "skin5");
                    $("nav").removeClass("navbar-light").addClass("navbar-dark");
                    $("#navbarSupportedContent").attr("data-navbarbg", "skin5");
                }
            }, 250);
        });
    }
});

$( document ).ready(function() {
	 $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf_token"]').attr("content"),
        },
    });
	var baseUrl = document.getElementsByTagName("meta").baseurl.content;
	$.ajax({
		url:baseUrl+"/setting/getMaintenanceMode",
		method:"GET",
		data:{},
		datatype:"text",
		success: function(data){
			var da = JSON.parse(data);
			if(da['settingValue']==1){
				$("#maintenanceMode").prop("checked",true);
			} else {
				$("#maintenanceMode").prop("checked",false);
			}
			$("#messageTitle").val(da['messageTitle']);
			$("#messageDescription").val(da['messageDescription']);
		}
	}); 
	$("#maintenanceMode").change(function(){
		var baseUrl = document.getElementsByTagName("meta").baseurl.content;
		if($(this).is(":checked")){
			$("#maintenance-modal").modal('show');	
			$("#close").click(function(){
			$.ajax({
				url:baseUrl+"/setting/getMaintenanceMode",
				method:"GET",
				data:{},
				datatype:"text",
				success: function(data){
					var da = JSON.parse(data);
					if(da['settingValue']==1){
							$("#maintenanceMode").prop("checked",true);
						} else {
							$("#maintenanceMode").prop("checked",false);
						}
						$("#messageTitle").val(da['messageTitle']);
						$("#messageDescription").val(da['messageDescription']);
					},
				}); 
			});
		} else {
			var settingValue= 0;
			$.ajax({					
				url: baseUrl+"/setting/updateMaintenanceOff",
				method:"get",
				data:{"settingValue":settingValue},
				datatype:"text",
				success: function(data){
					if(data){
						toastr.success('Maintenance mode is `OFF` now!', 'Maintenance Mode', { "progressBar": true });
					} else {
						toastr.error('Failed to update maintenance mode!', 'Maintenance Mode', { "progressBar": true });
					} 
				}
			});
		}
	});
	$("#updateMaintenance").click(function(){
		var baseUrl = document.getElementsByTagName("meta").baseurl.content;
		var settingValue = 1;
		var messageTitle = $("#messageTitle").val().trim();
		var messageDescription = $("#messageDescription").val().trim();
		if(messageTitle.length>=5 && messageDescription.length>=5){
			$.ajax({
				url: baseUrl+"/setting/updateMaintenanceOn",
				method:"post",
				data:{"settingValue":settingValue,'messageTitle':messageTitle,'messageDescription':messageDescription},
				success: function(data){
					if(data){
						toastr.success('Maintenance mode is `OFF` now!', 'Maintenance Mode', { "progressBar": true });
						$("#maintenanceMode").prop('checked',true);
					} else {
						toastr.error('Failed to update maintenance mode!', 'Maintenance Mode', { "progressBar": true });
						$("#maintenanceMode").prop('checked',false);
					}
					$("#maintenance-modal").modal('hide');
				}
			});
		}
	});
});
