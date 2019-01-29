/**
 * Copyright (c) 1995-2019 Cybage Software Pvt. Ltd., India 
 * http://www.cybage.com/pages/centers-of-excellence/ecommerce/ecommerce.aspx
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'mage/url',
    'Magento_Customer/js/customer-data',
    'https://maps.googleapis.com/maps/api/js?key=' + window.checkoutConfig.storepickup.google.apiUrl + '&v=3.exp&libraries=places'
], function ($, ko, Component, quote, url, customerData) {
    'use strict';
    var infoWindow;
    var markers = [];
    var map;
    var locationSelect;
    var isLoggedIn = window.isCustomerLoggedIn;
    var countryData = customerData.get('directory-data');
    var street = '';
    var street0 = '';
    var street1 = '';
    var street2 = '';
    return Component.extend({
        defaults: {
            template: 'Cybage_Storepickup/storepickupmap',
            error_message: window.checkoutConfig.storepickup.addressError
        },
        initMap: function () {            
            //Get address parameters 
            var searchText = '';
            var city = '';
            var state = '';
            var postcode = '';
            var country = '';

            if (isLoggedIn == true) {
                
                var countryId = quote.shippingAddress().countryId;
                country = countryData()[countryId].name;
                city = quote.shippingAddress().city;
                state = quote.shippingAddress().region;
                postcode = quote.shippingAddress().postcode;
                street = quote.shippingAddress().street;
                if (street.length > 0) {
                    $.each(street, function (index, value) {
                        searchText += value + ', ';
                    });
                }
                if (city != '') {
                    searchText += city + ', ';
                }
                if (state != "") {
                    searchText += state + ' ';
                }
                if (postcode != '') {
                    searchText += postcode + ', ';
                }

                if (country != '') {
                    searchText += country;
                }
            } else {
                city = $('[name="city"]').val();
                postcode = $('[name="postcode"]').val();
                street0 = $('[name="street[0]"]').val();
                street1 = $('[name="street[1]"]').val();
                street2 = $('[name="street[2]"]').val();
                if (street0 != "" && street0 != "undefined") {
                    street = street0;
                }
                if (street1 != "" && street1 != "undefined") {
                    street += street1 + ', ';
                }
                if (street2 != "" && street2 != "undefined") {
                    street += street2 + ', ';
                }
                if ($.trim(street) != "") {
                    searchText += street + ', ';
                }
                if (city != '') {
                    searchText += city + ', ';
                }

                if (postcode != '') {
                    searchText += postcode + ', ';
                }
                if ($('select[name="region_id"] option:selected').val() != '' && $('select[name="region_id"] option:selected').val() != "undefined") {
                    state = $('select[name="region_id"] option:selected').text();
                    searchText += state + ' ';
                }

                if ($('[name="country_id"] option:selected').val() != '' && $('[name="country_id"] option:selected').val() != "undefined") {
                    country = $('[name="country_id"] option:selected').text();
                    searchText += country;
                }
            }

            $("#autocomplete").val("");
            $("#autocomplete").val(searchText);
            var sydney = {lat: parseFloat(window.checkoutConfig.storepickup.center.lat), lng: parseFloat(window.checkoutConfig.storepickup.center.lng)};
            var map = new google.maps.Map(document.getElementById('map'), {
                center: sydney,
                zoom: parseInt(window.checkoutConfig.storepickup.zoom),
                mapTypeId: 'roadmap',
                mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
            });
            infoWindow = new google.maps.InfoWindow();
            var locationSelect = document.getElementById("pickupstore_id");
            locationSelect.onchange = function () {
                var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
                if (markerNum != "none") {
                    google.maps.event.trigger(markers[markerNum], 'click');
                }
            };
            $("#searchButton").click();
        },
        initObservable: function () {

            this.selectedMethod = ko.computed(function () {
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;

                if (selectedMethod == 'storepickup_storepickup') {
                    $("#map").show();
                } else {

                    $("#map").hide();
                }
                return selectedMethod;
            }, this);

            return this;
        },
        initAutocomplete: function () {
            var method = quote.shippingMethod();
            var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
            if (selectedMethod == window.checkoutConfig.storepickup.selected_shipping_method) {
                var autocomplete = new google.maps.places.Autocomplete(
                        /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
                        {types: ['geocode']});

                this.initMap();
            }
        },
        searchNearLoc: function (center) {
            var self = this;
            this.clearLocations();

            var radius = window.checkoutConfig.storepickup.radius;

            var searchUrl = url.build('storepickup/map/displaymap?lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius);
            this.downloadUrl(searchUrl, function (data) {
                var markerNodes = jQuery.parseJSON(data);
                if (markerNodes.length == 0) {
                    self.hideMethodElement();
                } else {
                    $(".message").hide();
                    self.showMethodElement();
                    var bounds = new google.maps.LatLngBounds();
                    var sydney = {lat: parseFloat(center.lat()), lng: parseFloat(center.lng())};
                    var map = new google.maps.Map(document.getElementById('map'), {
                        zoom: parseInt(window.checkoutConfig.storepickup.zoom),
                        center: sydney,
                        mapTypeId: 'roadmap',
                        mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
                    });
                    var infowindow = new google.maps.InfoWindow();
                    var marker, i, html;
                    for (var i = 0; i < markerNodes.length; i++) {

                        var id = markerNodes[i].store_id;
                        var name = markerNodes[i].store_name;
                        var address = markerNodes[i].street_address;
                        var distance = parseFloat(markerNodes[i].distance);
                        var latlng = new google.maps.LatLng(
                                parseFloat(markerNodes[i].latitude),
                                parseFloat(markerNodes[i].longitude));
                        var lat = parseFloat(markerNodes[i].latitude);
                        var lng = parseFloat(markerNodes[i].longitude);
                        self.createOption(name, distance, id);
                        var html = "<div><b>" + markerNodes[i].name + "</b> <br/>" + markerNodes[i].address + "</div>";
                        infowindow = new google.maps.InfoWindow({
                            content: html
                        });
                        marker = new google.maps.Marker({
                            position: new google.maps.LatLng(lat, lng),
                            map: map,
                            title: name,
                        });
                        google.maps.event.addListener(marker, 'click', (function (marker, i) {
                            return function () {
                                var name = markerNodes[i].store_name;
                                var address = markerNodes[i].street_address;
                                var html = "<b>" + name + "</b> <br/>" + address;
                                infowindow.setContent(html);
                                infowindow.open(map, marker, html);
                                $("#pickupstore_id option").filter(function () {
                                    return this.text == name;
                                }).attr('selected', true);
                            }
                        })(marker, i));
                        markers.push(marker);
                        bounds.extend(latlng);
                    }

                    map.fitBounds(bounds);
                    $("#pickupstore_id").show();
                    $("#pickupstore_id").onchange = function () {
                        var markerNum = $("#pickupstore_id option:selected").val();
                        ;
                        google.maps.event.trigger(markers[markerNum], 'click');
                    };
                }
            });
        },
        showMap: function () {
            $("#map").show();
        },
        hideMethodElement: function () {
            $("#my-carrier-custom-block-wrapper").hide();
            $(".message").show();
        },
        showMethodElement: function () {
            $("#my-carrier-custom-block-wrapper").show();   
            $(".message").hide();
        },

        getShippingMethod: function () {
            return window.checkoutConfig.storepickup.selected_shipping_method;
        },
        searchLocations: function () {
            var postcode = '';
            var city = '';
            var postcode = '';
            var searchText = '';
            var state = '';
            var regionId = '';
            var country = '';
            var countryId = '';
            var address = '';
            if (isLoggedIn == true) {
                var countryId = quote.shippingAddress().countryId;
                country = countryData()[countryId].name;
                city = quote.shippingAddress().city;
                state = quote.shippingAddress().region;
                postcode = quote.shippingAddress().postcode;
                street = quote.shippingAddress().street;
                if (street.length > 0) {
                    $.each(street, function (index, value) {
                        searchText += value + ', ';
                    });
                }
                if (city != '') {
                    searchText += city + ', ';
                }
                if (state != "") {
                    searchText += state + ' ';
                }
                if (postcode != '') {
                    searchText += postcode + ', ';
                }

                if (country != '') {
                    searchText += country;
                }
            } else {
                city = $('[name="city"]').val();
                postcode = $('[name="postcode"]').val();
                street0 = $('[name="street[0]"]').val();
                street1 = $('[name="street[1]"]').val();
                street2 = $('[name="street[2]"]').val();
                if (street0 != "" && street0 != "undefined") {
                    street = street0;
                }
                if (street1 != "" && street1 != "undefined") {
                    street += street1 + ', ';
                }
                if (street2 != "" && street2 != "undefined") {
                    street += street2 + ', ';
                }
                if ($.trim(street) != "") {
                    searchText += street + ', ';
                }
                if (city != '') {
                    searchText += city + ', ';
                }
                if ($('select[name="region_id"] option:selected').val() != '' && typeof ($('select[name="region_id"] option:selected').val()) != "undefined") {
                    regionId = $('select[name="region_id"] option:selected').val();
                }
                if (regionId != '') {
                    state = $('select[name="region_id"] option:selected').attr('data-title');
                    searchText += state + ' ';
                }
                if (postcode != '') {
                    searchText += postcode + ', ';
                }

                if ($('[name="country_id"] option:selected').val() != '' && $('[name="country_id"] option:selected').val() != "undefined") {
                    countryId = $('[name="country_id"] option:selected').val();
                }
                if (countryId != '') {
                    country = $('[name="country_id"] option:selected').attr('data-title');
                    searchText += country;
                }                
            }
         
            var self = this;
            if($("#autocomplete").val() == ""){
                $("#autocomplete").val(searchText);
            }
            if ((address == 'undefined' || address == "") && searchText != '' || searchText != 'undefined') {
                address = $("#autocomplete").val();
            }

            if (address != "") {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({address: address}, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        self.searchNearLoc(results[0].geometry.location);
                    } else {
                        alert(address + ' not found');
                    }
                });
            } else {
                $("#map").css('display', 'none');
            }

        },

        clearLocations: function () {
            infoWindow.close();
            var locationSelect = document.getElementById("pickupstore_id");
            for (var i = 0; i < markers.length; i++) {
                markers[i].setMap(null);
            }
            markers.length = 0;
            var option = '';
            option = document.createElement("option");
            option.value = "none";
            option.innerHTML = "See all results:";
            $("#pickupstore_id").html('');
            locationSelect.appendChild(option);
        },

        createMarker: function (center, lat, lng, name, address) {

            var markLatLng = {lat: parseFloat(lat), lng: parseFloat(lng)};

            var map = new google.maps.Map(document.getElementById('map'), {
                center: markLatLng,
                zoom: parseInt(window.checkoutConfig.storepickup.markerzoom),
                mapTypeId: 'roadmap',
                mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
            });
            var html = "<b>" + name + "</b> <br/>" + address;
            var marker = new google.maps.Marker({
                position: markLatLng,
                map: map

            });
            google.maps.event.addListener(marker, 'click', function () {
                infoWindow.setContent(html);
                infoWindow.open(map, marker);
            });
            markers.push(marker);
            marker.setMap(map);
        },

        createOption: function (name, distance, num) {
            var locationSelect = document.getElementById("pickupstore_id");
            var option = document.createElement("option");
            option.value = num;
            option.innerHTML = name;
            if(!($("#pickupstore_id option[value='"+num+"']").length > 0)){
                locationSelect.appendChild(option);
            }
        },

        downloadUrl: function (url, callback) {
            var self = this;
            var request = window.ActiveXObject ?
                    new ActiveXObject('Microsoft.XMLHTTP') :
                    new XMLHttpRequest;

            request.onreadystatechange = function () {
                if (request.readyState == 4) {
                    request.onreadystatechange = self.doNothing();
                    callback(request.responseText, request.status);
                }
            };
            request.open('GET', url, true);
            request.send(null);
        },
        doNothing: function () {

        }
    });
});