let map, infoWindow, marker, myLatlng, geocoder;
let lati, lngi;
let addressControl = document.querySelector("#address");
let latitudeControl = document.querySelector("#latitude");
let longitudeControl = document.querySelector("#longitude");

function handlePermission() {
    navigator.permissions.query({ name: 'geolocation' }).then(function (result) {
        if (result.state == 'granted') {
            report(result.state);
            // window.location.reload();
        } else if (result.state == 'prompt') {
            report(result.state);
            //navigator.geolocation.getCurrentPosition(revealPosition, positionDenied, geoSettings);
        } else if (result.state == 'denied') {
            report(result.state);
            toastr.error("Please allow location permission to show the map", "Location?");
        }
        result.onchange = function () {
            report(result.state);
        }
    });
}

function report(state) {
    console.log('Permission ' + state);
}

let geolocationOptions = {
    enableHighAccuracy: true,
    maximumAge: 10000,
    timeout: 5000,
};

const successCallback = (geolocation) => {   
    if (latitudeControl.value != "" && longitudeControl.value != "") {
        myLatlng = {
            lat: parseFloat(latitudeControl.value),
            lng: parseFloat(longitudeControl.value)
        };
    } else {
        myLatlng = {
            lat: parseFloat(geolocation.coords.latitude),
            lng: parseFloat(geolocation.coords.longitude)
        };
    } 
    console.log("My Location Is ", myLatlng);
    initMap();
};

const errorCallback = (error) => {
    console.log(error);
};

function initMap() {
    map = new google.maps.Map(document.getElementById("myMap"), {
        center: myLatlng,
        zoom: 16,
    });

    infoWindow = new google.maps.InfoWindow();

    geocoder = new google.maps.Geocoder();

    marker = new google.maps.Marker({
        map: map,
        position: myLatlng,
        draggable: true
    });

    geocoder.geocode({
        'latLng': myLatlng
    }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[0]) {
                addressControl.value = results[0].formatted_address;
                latitudeControl.value = marker.getPosition().lat();
                longitudeControl.value = marker.getPosition().lng();
                infoWindow.setContent(results[0].formatted_address);
                infoWindow.open(map, marker);
            }
        }
    });

    google.maps.event.addListener(marker, 'dragend', function () {
        geocoder.geocode({
            'latLng': marker.getPosition()
        }, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                if (results[0]) {
                    addressControl.value = results[0].formatted_address;
                    latitudeControl.value = marker.getPosition().lat();
                    longitudeControl.value = marker.getPosition().lng();
                    infoWindow.setContent(results[0].formatted_address);
                    infoWindow.open(map, marker);
                }
            }
        });
    });

}

latitudeControl.addEventListener("change", function (e) {
    let currentLatitude = Number(e.target.value);
    let currentLongitude = longitudeControl.value;
    var lat = parseFloat(currentLatitude);
    var lng = parseFloat(currentLongitude);
    myLatlng = {
        lat: lat,
        lng: lng
    };
    marker.setPosition(myLatlng);;
    map.setCenter(myLatlng);
    geocoder.geocode({
        'latLng': myLatlng
    }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[0]) {
                addressControl.value = results[0].formatted_address;
                infoWindow.setContent(results[0].formatted_address);
                infoWindow.open(map, marker);
            }
        }
    });
});

longitudeControl.addEventListener("change", function (e) {
    let currentLongitude = Number(e.target.value);
    let currentLatitude = latitudeControl.value;
    var lat = parseFloat(currentLatitude);
    var lng = parseFloat(currentLongitude);
    myLatlng = {
        lat: lat,
        lng: lng
    };
    marker.setPosition(myLatlng);
    map.setCenter(myLatlng);
    geocoder.geocode({
        'latLng': myLatlng
    }, function (results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if (results[0]) {
                addressControl.value = results[0].formatted_address;
                infoWindow.setContent(results[0].formatted_address);
                infoWindow.open(map, marker);
            }
        }
    });
});

function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(
        browserHasGeolocation
            ? "Error: The Geolocation service failed."
            : "Error: Your browser doesn't support geolocation."
    );
    infoWindow.open(map);
}

$(function () {
    setTimeout(() => {
        navigator.geolocation.getCurrentPosition(
            successCallback,
            errorCallback,
            geolocationOptions
        );
    }, 1000);
});
