var map;
var poligono;
var tPol;
var info;

var dev_run = new google.maps.MarkerImage('img/car_run.png',
    new google.maps.Size(32, 32),
    new google.maps.Point(0,0),
    new google.maps.Point(16,16)
);

var dev_stop = new google.maps.MarkerImage('img/car_stop.png',
    new google.maps.Size(32, 32),
    new google.maps.Point(0,0),
    new google.maps.Point(16,16)
);

function initialize() {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(-33.42482011764062,-70.56612968444824);
    var myOptions = {
        zoom: 16,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
}

function cargaPInteres(pint) {
    latlon = new google.maps.LatLng(pint.latitude,pint.longitude);
    map.setZoom(15);
    punto = new google.maps.Circle({
        center: latlon,
        radius: pint.radio*1,
        strokeColor: '#ff0000',
        strokeOpacity: 0.7,
        strokeWeight: 2,
        fillColor: '#ff0000',
        fillOpacity: 0.3
    });
    punto.setMap(map);
}

function showDevice(run) {
    dev = new google.maps.LatLng(info.lat,info.lon);
    map.setCenter(dev);
    var marker = new google.maps.Marker({
        position: dev,
        map: map,
        icon: (run)?dev_run:dev_stop,
        zIndex: 1
    });
}

function cargaPol(pol, tPol) {
    ptos = Array();
    if(pol.length>0) {
        for(var i=0; i<pol.length; i++) {
            ptos[i] = new google.maps.LatLng(pol[i].LAT_PUNTO, pol[i].LON_PUNTO);
        }
        switch(tPol) {
            case '1':
                poligono = new google.maps.Polyline({
                    map: map,
                    strokeColor : '#ff0000',
                    strokeOpacity : 0.7,
                    strokeWeight : 4,
                    path : ptos
                });
                break;
            case '2':
                ptos[pol.length] = new google.maps.LatLng(pol[0].LAT_PUNTO, pol[0].LON_PUNTO);
                poligono = new google.maps.Polygon({
                    map: map,
                    path: ptos,
                    strokeColor: '#ff0000',
                    strokeWeight: 2,
                    strokeOpacity: 0.7,
                    fillColor: '#ff0000',
                    fillOpacity: 0.2
                });
                break;
        }
        var bounds = new google.maps.LatLngBounds();
        for (i=0;i<ptos.length;i++) {
            bounds.extend(ptos[i]);
        }
        map.fitBounds(bounds);
    }
}

function iniMapa() {
    initialize();
    info = $.parseJSON($("#info").html());
    switch(info.par*1) {
        case 1: //vel
            showDevice(true);
            break;
        case 2: //det
            showDevice(false);
            break;
        case 3: //geoz
            var pol = $.parseJSON($("#pol").html());
            cargaPol(pol, '2');
            showDevice(true);
            break;
        case 4: //geof
            var pol = $.parseJSON($("#pol").html());
            cargaPol(pol, '1');
            showDevice(true);
            break;
        case 5: //pint
            var pint = $.parseJSON($("#pint").html());
            cargaPInteres(pint);
            showDevice(true);
            break;
        default:
            showDevice(true);
            break;
    }
}