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
    myLatlng = {
        lat: parseFloat(latitudeControl.value),
        lng: parseFloat(longitudeControl.value)
    };
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

    infoWindow.setContent(addressControl.value);
    infoWindow.open(map, marker);
}

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
