/**
  * Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India 
  * http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
  */ 

define([
    'jquery',
    'mage/mage',
    'prototype',
    'googleMapPlaceLibrary'
],
function ($, config) {
    'use strict';
    return function (config) {
        $(document).ready(function () {
            var autocomplete, map, markers = [], infoWindow, locationSelect;
            initAutocomplete();
            initMap();

            var street = $("#order-shipping_address_street0").val();
            var city = $("#order-shipping_address_city").val();
            var country_id = $("#order-shipping_address_country_id").val();
            var region = $("#order-shipping_address_region_id option:selected").text();
            if ($("#order-shipping_address_region_id").val() == "") {
                region = $("#order-shipping_address_region").val();
            }
            var postcode = $("#order-shipping_address_postcode").val();
            var formattedAddress = street + ', ' + city + ', ' + region + '  ' + postcode + ', ' + country_id;
            $("#autocomplete").val(formattedAddress);
            var address = $("#autocomplete").val();
            var geocoder = new google.maps.Geocoder();

            geocoder.geocode({address: address}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $(".storepickup-results").empty();
                    searchLocationsNear(results[0].geometry.location);
                } else if (address == '') {
                    if (!$("#autocomplete-error").hasClass("autocomplete-store")) {
                        $(".storepickup-results").append("<label for='autocomplete' generated='true' class='mage-error autocomplete-store' id='autocomplete-error'>This is a required field.</label>");
                    }
                } else {
                    alert(address + ' not Found');
                }
            });
            function geolocate() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        var geolocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };
                        var circle = new google.maps.Circle({
                            center: geolocation,
                            radius: position.coords.accuracy
                        });
                        autocomplete.setBounds(circle.getBounds());
                    });
                }
            }

            function initAutocomplete() {
                autocomplete = new google.maps.places.Autocomplete(
                        /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
                        {types: ['geocode']});
            }

            function initMap() {
                var mapOptions = {
                    zoom: config.zoom,
                    scrollwheel: false,
                    center: {lat: config.latitude, lng: config.longitude},
                    styles: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                };
                map = new google.maps.Map(document.getElementById('map'), mapOptions);
                infoWindow = new google.maps.InfoWindow();

                document.getElementById("searchButton").onclick = searchLocations;
                locationSelect = document.getElementById("locationSelect");
                locationSelect.onchange = function () {
                    var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
                    if (markerNum != "none") {
                        google.maps.event.trigger(markers[markerNum], 'click');
                    }
                };
            }

            function searchLocations() {
                var address = document.getElementById("autocomplete").value;
                var geocoder = new google.maps.Geocoder();

                geocoder.geocode({address: address}, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        $(".storepickup-results").empty();
                        searchLocationsNear(results[0].geometry.location);
                    } else if (address == '') {
                        if (!$("#autocomplete-error").hasClass("autocomplete-store")) {
                            $(".storepickup-results").append("<label for='autocomplete' generated='true' class='mage-error autocomplete-store' id='autocomplete-error'>This is a required field.</label>");
                        }
                    } else {
                        alert(address + ' not Found');
                    }
                });
            }

            function clearLocations() {
                infoWindow.close();
                for (var i = 0; i < markers.length; i++) {
                    markers[i].setMap(null);
                }
                markers.length = 0;

                locationSelect.innerHTML = "";
                var option = document.createElement("option");
                option.value = "none";
                option.innerHTML = "Please Select";
                locationSelect.appendChild(option);
            }

            function searchLocationsNear(center) {
                clearLocations();
                var searchUrl = config.ajaxUrl + '?lat=' + center.lat() + '&lng=' + center.lng();
                $.ajax({
                    dataType: 'json',
                    url: searchUrl,
                    showLoader: true //use for display loader 
                }).done(function (response) {
                    var length = response.length;
                    var address = document.getElementById("autocomplete").value;
                    if (length == 0) {
                        $(".storepickup-results").append("<span class='results-word'>Sorry no Pickup Stores are available for <span class='italic'>" + address + "</span></span><br />");
                        document.getElementById("locationLabel").style.visibility = "hidden";
                        locationSelect.style.visibility = "hidden";
                    }
                    var bounds = new google.maps.LatLngBounds();
                    for (var i = 0; i < length; i++) {
                        var data = response[i];
                        var id = data.store_id;
                        var name = data.store_name;
                        var address = data.street_address;
                        var distance = data.distance;
                        var latlng = new google.maps.LatLng(
                                parseFloat(data.lat),
                                parseFloat(data.lng));

                        createOption(name, address, id);
                        createMarker(latlng, name, address);
                        bounds.extend(latlng);
                    }
                    if (length > 0) {
                        map.fitBounds(bounds);
                        document.getElementById("locationLabel").style.visibility = "visible";
                        locationSelect.style.visibility = "visible";
                        locationSelect.onchange = function () {
                            var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
                            google.maps.event.trigger(markers[markerNum], 'click');
                        };
                    }
                });
            }

            function createMarker(latlng, name, address) {
                var html = "<b>" + name + "</b> <br/>" + address;
                var marker = new google.maps.Marker({
                    map: map,
                    position: latlng
                });
                google.maps.event.addListener(marker, 'click', function () {
                    infoWindow.setContent(html);
                    infoWindow.open(map, marker);
                });
                markers.push(marker);
            }

            function createOption(name, address, num) {
                var option = document.createElement("option");
                option.value = num;
                option.innerHTML = name + ", " + address;
                locationSelect.appendChild(option);
            }

        });
    };
});