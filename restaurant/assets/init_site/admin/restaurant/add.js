const baseUrl = document.getElementsByTagName("meta").baseurl.content;
function isNumberKey(evt) {
    var charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) return false;
    return true;
}

function ValidateAlpha(evt) {
    var keyCode = evt.which ? evt.which : evt.keyCode;
    if (keyCode > 47 && keyCode < 58) return false;
    return true;
}

function IsEmail(email) {
    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (!regex.test(email)) {
        return false;
    } else {
        return true;
    }
}
function IsContact(contact) {
    if (contact.length != 10) {
        return false;
    } else {
        return true;
    }
}

function deleteButton(type, code, value) {
		$.ajax({
			url: baseUrl + '/restaurant/deleteImage',
			method: "get",
			data: {
				'value': value,
				'code': code,
				'type': type,
			},
			datatype: "text",
			success: function(data) {
				// console.log(data);
				// return false;
				if (data = "true") {
					location.reload();
				} else {
					alert('not deleted');
				}
			}

		});

}

getLocation();
function getLocation() {
if (navigator.geolocation) {
navigator.geolocation.getCurrentPosition(showPosition);
} else {
console.log("Geolocation is not supported by this browser.");
}
}
var myLatlng;
function showPosition(position) {
var lat = position.coords.latitude;
var lng = position.coords.longitude;
myLatlng =new google.maps.LatLng(lat, lng);
}
	var map;
	var marker;
	//var myLatlng = new google.maps.LatLng(16.691307, 74.244865);
	var geocoder = new google.maps.Geocoder();
	var infowindow = new google.maps.InfoWindow();
	function initialize() {
		var mapOptions = {
			zoom: 18,
			center: myLatlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};

		map = new google.maps.Map(document.getElementById("myMap"), mapOptions);

		marker = new google.maps.Marker({
			map: map,
			position: myLatlng,
			draggable: true
		});

		geocoder.geocode({
			'latLng': myLatlng
		}, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				if (results[0]) {
					$('#latitude,#longitude').show();
					$('#address').val(results[0].formatted_address);
					$('#latitude').val(marker.getPosition().lat());
					$('#longitude').val(marker.getPosition().lng());
					infowindow.setContent(results[0].formatted_address);
					infowindow.open(map, marker);
				}
			}
		});

		google.maps.event.addListener(marker, 'dragend', function() {
			geocoder.geocode({
				'latLng': marker.getPosition()
			}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[0]) {
						$('#address').val(results[0].formatted_address);
						$('#latitude').val(marker.getPosition().lat());
						$('#longitude').val(marker.getPosition().lng());
						infowindow.setContent(results[0].formatted_address);
						infowindow.open(map, marker);
					}
				}
			});
		});
	}
	google.maps.event.addDomListener(window, 'load', initialize);
	
$(document).ready(function() {
	
  $("body").on("change", "#city", function(e) {
        var ID = $(this).val();
        if (ID != "") {
            $.ajax({
                type: "GET",
				data: {
					'cityCode': ID
				},
                url: baseUrl + "/getAreaDetails",
                success: function (res) {
                    if (res != undefined || res != "") {
						$("#addressCode").empty();
						$("#addressCode").append(res);
					} 
                },
            });
        }
    });
	
	$(document).on("change", "#entityImage", function () {
        var filePath = $(this).val();
        // Allowing file type
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
        if (!allowedExtensions.exec(filePath)) {
			 alert("Invalid file type");
            $(this).val(null);
            return false;
        } 
    });
	
	$(document).on("change", "#fssaiImage", function () {
        var filePath = $(this).val();
        // Allowing file type
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
        if (!allowedExtensions.exec(filePath)) {
			alert("Invalid file type");
            $(this).val(null);
            return false;
        } 
    });
	
	$(document).on("change", "#gstImage", function () {
        var filePath = $(this).val();
        // Allowing file type
        var allowedExtensions = /(\.jpeg|\.jpg|\.png)$/i;
        if (!allowedExtensions.exec(filePath)) {
			alert("Invalid file type");
            $(this).val(null);
            return false;
        } 
    });
	
	$("body").on("change", "input[name=packagingType]", function(e) {
		if ($(this).is(":checked")) {
			var thisVal = $(this).val();
			if (thisVal == "CART") {
				$("#cartPack").show();
				$("#cartPackagingPrice").attr("required", true);
				$("#cartPackagingPrice").attr("data-parsley-required-message", "Cart Packaging Price is required");
			} else {
				$("#cartPack").hide();
				$("#cartPackagingPrice").removeAttr("required");
				$("#cartPackagingPrice").removeAttr("data-parsley-required-message");
				$("#cartPackagingPrice").val(0);
			}
		}
	});
	$("body").on("change", "input[name=gstApplicable]", function(e) {
		if ($(this).is(":checked")) {
			var thisVal = $(this).val();
			if (thisVal == "YES") {
				$("#gstPercentDiv").show();
				$("#gstPercent").attr("required", true);
				$("#gstNumber").attr("required", true);  
			} else {
				$("#gstPercentDiv").hide();
				$("#gstPercent").removeAttr("required");
				$("#gstPercent").val(0);
				$("#gstNumber").removeAttr("required");
			}
		}
	});
	
     $(document).on("change", ".verify_email", function () {
        var email = $(this).val();
        if (IsEmail(email) == true) {
            $.ajax({
                url: baseUrl + "/checkDuplicateemail",
                method: "get",
                data: {email: email},
                success: function (data) {
                    if (data.status == "true") {
                        $("#shEmail").text(data.success);
                        $("#restaurant").prop("disabled", true);
                    } else {
                        $("#shEmail").empty();
                        $("#restaurant").prop("disabled", false);
                    }
                },
            });
        } else {
            $("#shEmail").text("Valid Email is required");
            setTimeout(() => {
                $("#shEmail").empty();
            }, 5000);
            return false;
        }
     });
	 
	 $(document).on("change", ".verify_mobile", function () {
		 debugger
        var mobile = $(this).val();
        if (IsContact(mobile) == true) {
            $.ajax({
                url: baseUrl + "/checkDuplicatemobile",
                method: "get",
                data: {mobile: mobile},
                success: function (data) {
                    if (data.status == "true") {
                        $("#shMobile").text(data.success);
                        $("#restaurant").prop("disabled", true);
                    } else {
                        $("#shMobile").empty();
                        $("#restaurant").prop("disabled", false);
                    }
                },
            });
        } else {
            $("#shMobile").text("Valid Mobile is required");
            setTimeout(() => {
                $("#shMobile").empty();
            }, 5000);
            return false;
        }
     });
	 
	 $(document).on("change", ".verify_owner_mobile", function () {
		 debugger
        var mobile = $(this).val();
		var code =$("#code").val();
        if (IsContact(mobile) == true) {
            $.ajax({
                url: baseUrl + "/checkDuplicatemobileOnUpdate",
                method: "get",
                data: {mobile: mobile,code:code},
                success: function (data) {
                    if (data.status == "true") {
                        $("#shUpdateMobile").text(data.success);
                        $("#restaurant").prop("disabled", true);
                    } else {
                        $("#shUpdateMobile").empty();
                        $("#restaurant").prop("disabled", false);
                    }
                },
            });
        } else {
            $("#shUpdateMobile").text("Valid Mobile is required");
            setTimeout(() => {
                $("#shUpdateMobile").empty();
            }, 5000);
            return false;
        }
     });
	 
	$(document).on("change", ".verify_owner_email", function () {
        var email = $(this).val();
		var code =$("#code").val();
        if (IsEmail(email) == true) {
            $.ajax({
                url: baseUrl + "/checkDuplicateemailOnUpdate",
                method: "get",
                data: {email: email,code:code},
                success: function (data) {
                    if (data.status == "true") {
                        $("#shUpdateEmail").text(data.success);
                        $("#restaurant").prop("disabled", true);
                    } else {
                        $("#shUpdateEmail").empty();
                        $("#restaurant").prop("disabled", false);
                    }
                },
            });
        } else {
            $("#shUpdateEmail").text("Valid Email is required");
            setTimeout(() => {
                $("#shUpdateEmail").empty();
            }, 5000);
            return false;
        }
     });
	
	
});
