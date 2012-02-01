var marker = null;
var latlon = null;
var punto;
function initialize() {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(-33.449204,-70.642776);
    var myOptions = {
        zoom: 11,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
    var menu = new contextMenu({map:map});
    menu.addItem('Crear punto de inter&eacute;s aqu&iacute;', function(map, latLng){
        getAddress(latLng.lat(), latLng.lng(), false);
    });
    menu.addItem('Acercar', function(map, latLng){
            map.setZoom( map.getZoom() + 1);
            map.panTo( latLng );
    });
    menu.addItem('Alejar', function(map, latLng){
            map.setZoom( map.getZoom() - 1 );
            map.panTo(latLng);
    });
    menu.addItem('Centrar aqui', function(map, latLng){
            map.panTo(latLng);
    });
}

function codeAddress(dir, pos) {
    var address = dir;
    geocoder.geocode( {'address': address}, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            if(pos) {
                $("input[name='lat']").val(results[0].geometry.location.lat());
                $("input[name='lon']").val(results[0].geometry.location.lng());
                dibujaPunto(results[0].geometry.location);
            }
        }
    });
}

function getAddress(lat, lon, flag) {
    if(flag) {
        lat = lat.replace(",", ".");
        lon = lon.replace(",", ".");
    }
    var latlng = new google.maps.LatLng(lat, lon);
    $("input[name='lat']").val(lat);
    $("input[name='lon']").val(lon);
    if (geocoder) {
        geocoder.geocode({
            'latLng': latlng
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                lala = results;
                if(results[0]) {
                    $("input[name='dir']").val(results[0].formatted_address);
                    dibujaPunto(latlng);
                }
            } else {
                alert("Hubo un error al codificar la direcci&oacute;n");
            }
        });
    }
}

function dibujaPunto(pto) {
    map.panTo(pto);
    if(map.getZoom()<15) {
        map.setZoom(15);
    }
    if(marker == null) {
        marker = new google.maps.Marker({
            map: map,
            position: pto,
            title: "Puedes arrastrame",
            draggable: true
        });
        punto = new google.maps.Circle({
            center: pto,
            radius: $("input[name='rad']").val()*1,
            strokeColor: '#ff0000',
            strokeOpacity: 0.7,
            strokeWeight: 2,
            fillColor: '#ff0000',
            fillOpacity: 0.3,
            map: map
        });
        google.maps.event.addListener(marker, 'dragend', function(e) {
            $("input[name='lat']").val(e.latLng.lat());
            $("input[name='lon']").val(e.latLng.lng());
            punto.setCenter(e.latLng);
            punto.setMap(map);
            punto.setRadius($("input[name='rad']").val()*1);
        });
        google.maps.event.addListener(marker, 'dragstart', function(e) {
            punto.setMap(null);
        })
    } else {
        marker.setPosition(pto);
        punto.setCenter(pto);
        punto.setMap(map);
        punto.setRadius($("input[name='rad']").val()*1);
    }
}

function geoLocaliza() {
    var dir = $("input[name='dir']").val();
    var lat = $("input[name='lat']").val();
    var lon = $("input[name='lon']").val();
    if(lat != "" && lon!="") {
        getAddress(lat, lon, true);
    } else {
        codeAddress(dir, true);
    }
}

function cargaPosIni() {
    if(latlon==null) {
        lat = $("input[name='lat']").val();
        lon = $("input[name='lon']").val();
        if(lat != "" && lon != "") {
            latlon = new google.maps.LatLng(lat,lon);
            if(marker == null) {
                map.setCenter(latlon);
                map.setZoom(15);
                marker = new google.maps.Marker({
                    map: map,
                    position: latlon,
                    title: "Puedes arrastrame",
                    draggable: true
                });
                punto = new google.maps.Circle({
                    center: latlon,
                    radius: $("input[name='rad']").val()*1,
                    strokeColor: '#ff0000',
                    strokeOpacity: 0.7,
                    strokeWeight: 2,
                    fillColor: '#ff0000',
                    fillOpacity: 0.3
                });
                punto.setMap(map);
                google.maps.event.addListener(marker, 'dragend', function(e) {
                    $("input[name='lat']").val(e.latLng.lat());
                    $("input[name='lon']").val(e.latLng.lng());
                    punto.setCenter(e.latLng);
                    punto.setMap(map);
                    punto.setRadius($("input[name='rad']").val()*1);
                });
                google.maps.event.addListener(marker, 'dragstart', function(e) {
                    punto.setMap(null);
                })
            } else {
                marker.setPosition(latlon);
                punto.setCenter(results[0].geometry.location);
                punto.setRadius($("input[name='rad']").val()*1);
            }
        }
    }
}

$(document).ready(function() {
    var validator = $("#formu").bind("invalid-form.validate",
        function() {
            $(".notification").html("<div>Debe completar todos lo campos requeridos</div>");
            $(".notification").attr("class", "notification error png_bg");
        }).validate({
        errorPlacement: function(error, element) {
        },
        submitHandler: function(form) {
            if(punto!=null) {
                form.submit();
            } else {
                $(".notification").html("<div>Debe crear un punto</div>");
                $(".notification").attr("class", "notification error png_bg");
            }
        },
        success: function(label) {
        }
    });
    initialize();
    cargaPosIni();
    $("#rad").change(function() {
        if(punto!=null)
            punto.setRadius($(this).val()*1);
    });
});
