
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
	  myLatlng =new google.maps.LatLng(lat,lng);
    }
	var map;
	var marker;
	var startMarker;
	var endMarker;
	var geocoder = new google.maps.Geocoder();
	var directionsDisplay;
    var directionsService = new google.maps.DirectionsService();
	var ResLatitude=$('#ResLatitude').val();
	var ResLongitude=$('#ResLongitude').val();
	var clLatitude=$('#clLatitude').val();
	var clLongitude=$('#clLongitude').val();
	var latitude=$('#latitude').val();
	var longitude=$('#longitude').val();
	var resLabel=$('#resLabel').val();
	var clLabel=$('#clLabel').val();
	var dlbLabel=$('#dlbLabel').val();
	var deliverypng=$('#deliverypng').val();
	var customerpng=$('#customerpng').val();
	var dlbImage = "<img src='"+$('#dlbProfilePic').val()+"' width='40px;' height='40px;'>";
	function initialize() {
		directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true});
		var mapOptions = {
			zoom: 18,
			center: myLatlng,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("myMap"), mapOptions);
		marker = new google.maps.Marker({
			map: map,
			position: myLatlng,
			draggable: false
		});
		var waypts = [];
		var startPoint = new google.maps.LatLng(ResLatitude,ResLongitude);
        var endPoint = new google.maps.LatLng(clLatitude,clLongitude);
        var waypoint = new google.maps.LatLng(latitude,longitude);
		var icons = {
		  end: new google.maps.MarkerImage(
			customerpng,
		  ),
		  start: new google.maps.MarkerImage(
		  
		  )
		 };
		/*waypts.push({
			location: waypoint,
			stopover: true
		});*/
        var bounds = new google.maps.LatLngBounds();
        bounds.extend(startPoint);
        bounds.extend(endPoint);
        map.fitBounds(bounds);
        var request = {
            origin: startPoint,
            destination: endPoint,
			//waypoints: waypts,
			//optimizeWaypoints: true,
            travelMode: google.maps.TravelMode.DRIVING
        };
        directionsService.route(request, function (response, status) {
			//debugger;
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(response);
				/*var icon = {
					url:  "<?= base_url() ?>"+"assets/order_tracking/delivery.png",
					scaledSize: new google.maps.Size(30,30), // scaled size
					origin: new google.maps.Point(0,0), // origin
					anchor: new google.maps.Point(0, 0) // anchor
				};*/
				waymarker = new google.maps.Marker({
					map: map,
					position: waypoint,
					icon : deliverypng,
					title:"Delivery Boy"
				});
				 var infowindow = new google.maps.InfoWindow({
					pixelOffset: new google.maps.Size(0, -40)
				});
				  infowindow.setContent(resLabel)
				  infowindow.setPosition(startPoint);
				  infowindow.open(map);
				  var infowindow = new google.maps.InfoWindow({
					pixelOffset: new google.maps.Size(0, -42)
				});
				  infowindow.setContent(clLabel)
				  infowindow.setPosition(endPoint);
				  infowindow.open(map);
				   var infowindow = new google.maps.InfoWindow({
					pixelOffset: new google.maps.Size(0, -42)
				});
				  infowindow.setContent('<div class="float-left">'+dlbImage+'</div><div class="float-right ml-2">'+dlbLabel+'</div>')
				  infowindow.setPosition(waypoint);
				  infowindow.open(map);
				  var leg = response.routes[ 0 ].legs[ 0 ];
				 makeMarker( leg.start_location, icons.start, "Restaurant" );
				 makeMarker( leg.end_location, icons.end, "Customer" );
                directionsDisplay.setMap(map);
            } else {
                alert("Directions Request from " + startPoint.toUrlValue(6) + " to " + endPoint.toUrlValue(6) + " failed: " + status);
            }
        });
	}
	google.maps.event.addDomListener(window, 'load', initialize);
	function makeMarker( position, icon, title ) {
	 new google.maps.Marker({
	  position: position,
	  map: map,
	  icon: icon,
	  title: title
	 });
	}