const baseUrl = document.getElementsByTagName("meta").baseurl.content;

	var map;
	var marker;
	// var myLatlng = new google.maps.LatLng(20.268455824834792,85.84099235520011);
	var latVal = $('#latitude').val();
	var lngVal = $('#longitude').val();
	var myLatlng = new google.maps.LatLng(latVal, lngVal);
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
	
	function IsContact(contact) {
    if (contact.length != 10) {
        return false;
    } else {
        return true;
    }
   }
	
	$("body").on("change","input[name=packagingType]",function(e){ 
		if($(this).is(":checked")){
			var thisVal = $(this).val();
			if(thisVal=="CART"){
				$("#cartPack").show();
				$("#cartPackagingPrice").attr("required",true);
			} else {
				$("#cartPack").hide();
				$("#cartPackagingPrice").removeAttr("required");
				$("#cartPackagingPrice").val(0);
			}
		} 
	});
	
	$(document).ready(function () {
		$.ajaxSetup({
			headers: {
				"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
			},
		});
		
		 $("#password_confirmation").on("change", function () {
			var cpassword = $(this).val();
			var password = $('#password').val();
			if (cpassword != password) {
				$("#updatePassword").prop("disabled", true);
				$("#password_error").text("Password does not match");
				setTimeout(() => {
					$("#password_error").empty();
				}, 5000);
				return false;
			}
			else{
				$("#updatePassword").prop("disabled", false);
			}

		});
		
		 $(document).on("change", "#ownerContact", function () {
			 debugger
			var mobile = $(this).val();
			var code =$("#code").val();
			if (IsContact(mobile) == true) {
				$.ajax({
					url: baseUrl + "/checkDuplicatemobileOnUpdate",
					method: "get",
					data: {mobile: mobile,code:code},
					success: function (data) {
						debugger;
						if (data.status == "true") {
							$("#mobile_error").text(data.success);
							$("#updatePassword").prop("disabled", true);
						} else {
							$("#mobile_error").empty();
							$("#updatePassword").prop("disabled", false);
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
		    
		 
	});