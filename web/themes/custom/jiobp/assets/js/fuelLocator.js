$.getJSON("fuel-data-json/PriceAPIOutput.txt", function (res, status) {
    fuelPrice = res.RODetail
    var fuelPriceLoc = [];
    for (i = 0; i < fuelPrice.length; i++) {
        var str = {
            "rocode": fuelPrice[i].ROCode, "roid": fuelPrice[i].ROID, "Product": fuelPrice[i].Product, "Price": fuelPrice[i].Price, "date": fuelPrice[i].DTStartDate
        }
        fuelPriceLoc.push(str);
    }
    fuelData = fuelPriceLoc
});
$.getJSON("fuel-data-json/LocationAPIOutput.txt", function (res, status) {
    fuelLocation = res.RO_Details
    var newLocations = [];
    for (i = 0; i < fuelLocation.length; i++) {
        if (fuelLocation[i].latitude != 0 && fuelLocation[i].longitude != 0) {
            var str = {
                "name": fuelLocation[i].Roname,
                "state": fuelLocation[i].State,
                "city": fuelLocation[i].City,
                "address": fuelLocation[i].Roaddress1,
                "lattitude": fuelLocation[i].latitude,
                "longitude": fuelLocation[i].longitude,
                "type": 1,
                "pincode": fuelLocation[i].Ropincode,
                "rocode": fuelLocation[i].Rocode,
                "roid": fuelLocation[i].Roid,
                // "Dealername": fuelLocation[i].Dealername,
                // "Dealerphone": fuelLocation[i].Dealerphone,
                "Territory": fuelLocation[i].Territory
            }
            newLocations.push(str);
        }
    }
    data3 = newLocations
});
data3 = JSON.parse(data3);
fuelData = JSON.parse(fuelData);
var geocoder;
var state1;
var city2
var map;
var locations = [];

function initMap() {
    map = new google.maps.Map(document.getElementById('map'));
    locations = [];

    geocoder = new google.maps.Geocoder();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            map.setCenter(initialLocation);

            var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {

                        var exists = false;
                        $('#slct2').each(function () {
                            if (this.value == city2.long_name) {
                                exists = true;
                                return false;
                            }
                        });
                        if (exists) {
                            setTimeout(function () { $('#slct2').val(city2.long_name).trigger('change'); }, 500);
                        }



                    } else {
                    }
                } else {
                }
            });
        });
    }
}
var marker;
var markersArray = [];

function addMarker(locations, contentString) {
    if (marker && marker.setMap) {
        marker.setMap(null);
    }

    var infowindow = new google.maps.InfoWindow();

    var icons = {
        1: {
            icon: './assets/images/icons/map-locator-pin.svg'
        }

    };

    var i;


    for (i = 0; i < locations.length; i++) {

        marker = new google.maps.Marker({
            position: new google.maps.LatLng(locations[i][0], locations[i][1]),
            animation: google.maps.Animation.DROP,
            map: map,
            icon: icons[locations[i][2]].icon,
        });
        markersArray.push(marker);
        map.setCenter(new google.maps.LatLng(locations[i][0], locations[i][1]));
        map.setZoom(6);

        google.maps.event.addListener(marker, 'click', (function (marker, i) {
            return function () {
                infowindow.setContent('<div class="addrssmap"><h4>' + locations[i][4] + '<p></h4>' + locations[i][3] + '</p></div>');
                infowindow.open(map, marker);
            }
        })(marker, i));
    }


}

function addMarker1(lat, lon, type, address, name, contentString) {
    locations.push({
        "0": lat,
        "1": lon,
        "2": type,
        "3": address,
        "4": name
    });

    if (marker && marker.setMap) {
        marker.setMap(null);
    }

    var infowindow = new google.maps.InfoWindow();

    var icons = {
        1: {
            icon: './assets/images/icons/map-locator-pin.svg'
        }

    };

    var i;


    for (i = 0; i < locations.length; i++) {
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(locations[i][0], locations[i][1]),
            map: map,
            icon: icons[locations[i][2]].icon,
        });
        markersArray.push(marker);
        map.setCenter(new google.maps.LatLng(locations[i][0], locations[i][1]));
        map.setZoom(15);

        google.maps.event.addListener(marker, 'click', (function (marker, i) {
            var lName = locations[i][4]
            var lAdd = locations[i][3]
            var lLat = locations[i][0]
            var lLon = locations[i][1]
            return function () {
                infowindow.setContent('<div class="addrssmap"><h4>' + lName + '<p></h4><p>' + lAdd + '</p></div>');
                infowindow.open(map, marker);
            }
        })(marker, i));
    }
    locations = [];
}

function getlatlong(lat, lon) {
    window.open(
        'https://maps.google.com/?q=' + lat + ',' + lon,
        '_blank'
    );

}

function clearOverlays() {
    for (var i = 0; i < markersArray.length; i++) {
        markersArray[i].setMap(null);
    }
    markersArray.length = 0;
}

$(document).ready(function () {
    $('#slct2').on('change', function (ev) {
        ev.preventDefault();
        var id = $(this).val().trim();
        var address = [];
        var result = ``;
        var j = 0;
        status1 = 0;
        clearOverlays();

        if (id != '') {
            for (var i = 0, len = data3.length; i < len; i++) {

                for (var j = 0; j < fuelData.length; j++) {
                    if (data3[i].roid == fuelData[j].roid) {
                        if (fuelData[j].Product == 'Petrol') {
                            data3[i]['Petrol'] = fuelData[j].Price
                        }
                        if (fuelData[j].Product == 'Diesel') {
                            data3[i]['Diesel'] = fuelData[j].Price
                        }
                        if (fuelData[j].Product == 'Auto LPG') {
                            data3[i]['Auto LPG'] = fuelData[j].Price
                        }
                        if (fuelData[j].date) {
                            data3[i]['date'] = fuelData[j].date
                        }
                    }
                }
                if (data3[i]['Petrol'] != undefined && data3[i]['Petrol'] != 0) {
                    var PetrolPrice = `<li>
                                <b>Petrol</b>
                                <a href="javacript:;" class="tooltipLink" data-toggle="tooltip"
                                    title="Last Updated : ${data3[i]['date']}"></a>
                                <small>${data3[i]['Petrol']} Rs./Ltr</small>
                            </li>`
                } else {
                    var PetrolPrice = ``
                }
                if (data3[i]['Diesel'] != undefined && data3[i]['Diesel'] != 0) {
                    var DieselPrice = `<li>
                                <b>Diesel</b>
                                <a href="javacript:;" class="tooltipLink" data-toggle="tooltip"
                                    title="Last Updated : ${data3[i]['date']}"></a>
                                <small>${data3[i]['Diesel']} Rs./Ltr</small>
                            </li>`
                } else {
                    var DieselPrice = ``
                }
                if (data3[i]['Auto LPG'] != undefined && data3[i]['Auto LPG'] != 0) {
                    var LpgPrice = `<li>
                                <b>Auto LPG</b>
                                <a href="javacript:;" class="tooltipLink" data-toggle="tooltip"
                                    title="Last Updated : ${data3[i]['date']}"></a>
                                <small>${data3[i]['Auto LPG']} Rs./Ltr</small>
                            </li>`
                } else {
                    var LpgPrice = ``
                }
                if (PetrolPrice == '' && DieselPrice == '' && LpgPrice == '') {
                    var priceList = ``
                } else {
                    var priceList = `
                    <li>                                            
                        <i><img src="./assets/images/icons/FuelPrice.svg" alt=""></i>
                        <ul class="fuelPriceList">
                            ${PetrolPrice}
                            ${DieselPrice}
                            ${LpgPrice}
                        </ul>
                    </li>`
                }


                if (data3[i]['address'].toUpperCase().includes(id.toUpperCase()) || data3[i]['state'].toUpperCase().includes(id.toUpperCase()) || data3[i]['city'].toUpperCase().includes(id.toUpperCase())) {
                    var address1 = data3[i]['address'];
                    var locName = data3[i]['name']

                    var cityName = data3[i]['city']
                    if (data3[i]['city'] == '') {
                        var cityName = data3[i]['Territory']
                    }
                    // if (data3[i]['Dealername'] != '' && data3[i]['Dealerphone'] != '') {
                    //     var contactInfo = `<i><img src="./assets/images/icons/FuelPrice.svg" alt=""></i>
                    //     ${data3[i]['Dealername']}: ${data3[i]['Dealerphone']}`

                    // }
                    // else {
                    //     var contactInfo = ''
                    // }


                    var address2 = "'" + data3[i]['lattitude'] + "','" + data3[i]['longitude'] + "','" + data3[i]['type'] + "'";
                    result = result + `
                                <li onclick="javascript:addMarker1(${address2},'${address1}','${locName}');">                                
                                    <h4>${locName} - ${cityName}</h4>
                                    <span><small>RO Code: ${data3[i]['rocode']}</small> <small>RO ID: ${data3[i]['roid']}</small></span>
                                    <ul class="contactDtls">
                                        <li>
                                            <i><img src="./assets/images/icons/location-pin.svg" alt=""></i>
                                            <a href="javascript:getlatlong('${data3[i]['lattitude']}','${data3[i]['longitude']}');">Get Directions</a>
                                        </li>
                                        ${priceList}
                                    </ul>
                                </li>
                            `;
                    locations.push({
                        "0": data3[i]['lattitude'],
                        "1": data3[i]['longitude'],
                        "2": data3[i]['type'],
                        "3": address1,
                        "4": data3[i]['name']
                    });
                    status1 = 1;
                    j++;
                }
            }
        }

        addMarker(locations);
        locations = [];
        if (result == '' || id == '') {
            $(".addressList").html("Please refine your search by entering the nearest Locality or Pincode and try again")
        } else {
            $(".addressList").html(result);

        }
        $(".address-scroll").show();
        $(".title2").show();
        $(".locatorMap").show();
    });
    $('#slct3').on('change', function (ev) {
        ev.preventDefault();
        $(".overlay .enquiryLoader").show();
        $(".title2").show();
        $(".locatorMap").show();
        var id = $(this).val();
        var address = [];
        var result = "";
        var j = 0;
        status1 = 0;
        clearOverlays();

        if (id != '') {
            for (var i = 0, len = data3.length; i < len; i++) {

                for (var j = 0; j < fuelData.length; j++) {
                    if (data3[i].roid == fuelData[j].roid) {
                        if (fuelData[j].Product == 'Petrol') {
                            data3[i]['Petrol'] = fuelData[j].Price
                        }
                        if (fuelData[j].Product == 'Diesel') {
                            data3[i]['Diesel'] = fuelData[j].Price
                        }
                        if (fuelData[j].Product == 'Auto LPG') {
                            data3[i]['Auto LPG'] = fuelData[j].Price
                        }
                        if (fuelData[j].date) {
                            data3[i]['date'] = fuelData[j].date
                        }
                    }
                }
                if (data3[i]['Petrol'] != undefined && data3[i]['Petrol'] != 0) {
                    var PetrolPrice = `<li>
                                <b>Petrol</b>
                                <a href="javacript:;" class="tooltipLink" data-toggle="tooltip"
                                    title="Last Updated : ${data3[i]['date']}"></a>
                                <small>${data3[i]['Petrol']} Rs./Ltr</small>
                            </li>`
                } else {
                    var PetrolPrice = ``
                }
                if (data3[i]['Diesel'] != undefined && data3[i]['Diesel'] != 0) {
                    var DieselPrice = `<li>
                                <b>Diesel</b>
                                <a href="javacript:;" class="tooltipLink" data-toggle="tooltip"
                                    title="Last Updated : ${data3[i]['date']}"></a>
                                <small>${data3[i]['Diesel']} Rs./Ltr</small>
                            </li>`
                } else {
                    var DieselPrice = ``
                }
                if (data3[i]['Auto LPG'] != undefined && data3[i]['Auto LPG'] != 0) {
                    var LpgPrice = `<li>
                                <b>Auto LPG</b>
                                <a href="javacript:;" class="tooltipLink" data-toggle="tooltip"
                                    title="Last Updated : ${data3[i]['date']}"></a>
                                <small>${data3[i]['Auto LPG']} Rs./Ltr</small>
                            </li>`
                } else {
                    var LpgPrice = ``
                }

                if (PetrolPrice == '' && DieselPrice == '' && LpgPrice == '') {
                    var priceList = ``
                } else {
                    var priceList = `
                    <li>                                            
                        <i><img src="./assets/images/icons/FuelPrice.svg" alt=""></i>
                        <ul class="fuelPriceList">
                            ${PetrolPrice}
                            ${DieselPrice}
                            ${LpgPrice}
                        </ul>
                    </li>`
                }


                if (data3[i]['pincode'] == id) {
                    var address1 = data3[i]['address'];
                    var locName = data3[i]['name']
                    var cityName = data3[i]['city']
                    if (data3[i]['city'] == '') {
                        var cityName = data3[i]['Territory']
                    }
                    if (data3[i]['city'] == '' && data3[i]['Territory'] == '') {
                        var cityName = data3[i]['State']
                    }
                    // if (data3[i]['Dealername'] != '' && data3[i]['Dealerphone'] != '') {
                    //     var contactInfo = `<i><img src="./assets/images/icons/call-icon.svg" alt=""></i>
                    //     ${data3[i]['Dealername']}: ${data3[i]['Dealerphone']}`

                    // }
                    // else {
                    //     var contactInfo = ''
                    // }
                    var contactInfo = '<i><img src="./assets/images/icons/FuelPrice.svg" alt=""></i>'
                    var address2 = "'" + data3[i]['lattitude'] + "','" + data3[i]['longitude'] + "','" + data3[i]['type'] + "'";
                    result = result + `
                                <li onclick="javascript:addMarker1(${address2},'${address1}','${locName}');">
                                                <h4>${locName} - ${cityName}</h4>
                                                <span><small>RO Code: ${data3[i]['rocode']}</small> <small>RO ID: ${data3[i]['roid']}</small></span>
                                                <ul class="contactDtls">
                                                    <li>
                                                        <i><img src="./assets/images/icons/location-pin.svg" alt=""></i>
                                                        <a href="javascript:getlatlong('${data3[i]['lattitude']}','${data3[i]['longitude']}');">Get Directions</a>
                                                    </li>
                                                    ${priceList}
                            `;
                    locations.push({
                        "0": data3[i]['lattitude'],
                        "1": data3[i]['longitude'],
                        "2": data3[i]['type'],
                        "3": address1,
                        "4": data3[i]['name']
                    });

                    status1 = 1;
                    j++;
                }
            }
        }

        addMarker(locations);
        locations = [];
        if (result == '' || id == '') {
            $(".addressList").html("Please refine your search by entering the nearest Locality or Pincode and try again")
        } else {
            $(".addressList").html(result);

        }
        $(".address-scroll").show();
        $(".title2").show();
        $(".locatorMap").show();
    });

    $('#searchBtn').on('click', function (e) {
        e.preventDefault();
        if ($('#slct2').val() == '' && $('#slct3').val() == '') {
            locations = [];
        }
        else if ($('#slct2').val() != '' || $('#slct3').val == '') {
            $('#slct2').change();
        } else if ($('#slct2').val() == '' || $('#slct3').val() != '') {
            $('#slct3').change();
        }
    })
});
$('body').on('click', '.addressList li', function () {
    $('.addressList li').removeClass('active')
    $(this).addClass('active');
});

$($('.fuelPriceList li').get().reverse()).each(function (index) {
    $(this).css('z-index', index + 10);
});