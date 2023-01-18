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