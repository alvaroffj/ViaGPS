var map;
var poligono;
var tPol;
var info;
var animacion;
var title;
var $det;

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
//    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(-33.42482011764062,-70.56612968444824);
    var myOptions = {
        zoom: 16,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
}

function aniAuditoria() {
    title = $("#fancybox-title");
    if(title.length == 1) {
        title.show();
        title.attr("class", "fancybox-title-over");
        title.attr("style", "margin-left: 10px; width: 552px; bottom: 10px; display: block; ");
//        title = $("<div id='fancybox-title' class='fancybox-title-over' style='margin-left: 10px; width: 552px; bottom: 10px; display: block; '></div>");
//        title.appendTo($("#fancybox-outer"));
        $det = $("#fancybox-title-over");
        if($det.length == 0) {
            $det = $("<div id='fancybox-title-over'></div>");
            $det.appendTo(title);
        }
    }
    $det.html("<p align='center'>Preparando...</p>");
    initialize();
    $info = $(".info");
    var pos;
    var i=0;
    var n = $info.length;
    var marker = new google.maps.Marker({
//        icon: (info.velocidad>0)?dev_run:dev_stop
//        title: info.licensePlate,
//        zIndex: 1
    });
    marker.setMap(map);
    animacion = setInterval(function() {
        info = $.parseJSON($($info[i]).html());
//        console.log(info.fecha);
        $det.html("<b>Veh&iacute;culo</b>: "+info.displayName+"<br /><b>Patente</b>: "+info.licensePlate+"<br /><b>Fecha</b>: "+info.fecha+"<br /><b>Velocidad</b>: "+info.velocidad+" (Km/h)<br /><b>Distancia</b>: "+info.distancia+" (Km)");
        pos = new google.maps.LatLng(info.lat, info.lon);
        map.panTo(pos);
        marker.setPosition(pos);
        marker.setIcon((info.velocidad>0)?dev_run:dev_stop);
        i++;
        if(i>n-1) clearInterval(animacion);
    }, 1000);
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

function getDevices(sel, idDest){
    var idGr = sel[sel.selectedIndex].value;
    $.ajax({
        url: "?sec=reporte&get=devByGrupo&id_grupo="+idGr,
        type: 'get',
        dataType: 'json',
        beforeSend: function() {
        },
        complete: function(data) {
            var res = $.parseJSON(data.responseText);
            var nRes = res.length;
            var i;
            var op = "<OPTION VALUE='0'>Todos</OPTION>";
            for(i=0; i<nRes; i++) {
                op += "<OPTION VALUE='"+res[i].deviceID+"'>" + res[i].displayName + "</OPTION>";
            }
            $("#"+idDest).html(op);
        }
    });
}

$(document).ready(function() {
    fecha = new Date();
    $form = $("#formu");
    $("#fecha_ini").datepicker({
        maxDate: fecha,
        'dateFormat': 'yy-mm-dd'
    });
    $("#fecha_fin").datepicker({
        maxDate: fecha,
        'dateFormat': 'yy-mm-dd'
    });
    var validator = $form.bind("invalid-form.validate",
        function() {
//            $(".notification").html("<div>Debe completar todos lo campos requeridos</div>");
//            $(".notification").attr("class", "notification error png_bg");
        }).validate({
        errorPlacement: function(error, element) {
        },
        submitHandler: function(form) {
            if(!$("#submit").hasClass("working") && $("#Grupo option:selected", $form).val()!='0') {
                $.ajax({
                    url: form.action,
                    type: 'post',
                    context: form,
                    data: {
                        id_grupo: $("#Grupo option:selected", $form).val()
                        , id_device: $("#Vehiculo option:selected", $form).val()
                        , fecha_ini: $("#fecha_ini", $form).val()
                        , hrs_ini: $("#hrs_ini", $form).val()
                        , min_ini: $("#min_ini", $form).val()
                        , fecha_fin: $("#fecha_fin", $form).val()
                        , hrs_fin: $("#hrs_fin", $form).val()
                        , min_fin: $("#min_fin", $form).val()
                        , vel: $("#vel", $form).val()
                        , operador: $("#Operador", $form).val()
                    },
                    beforeSend: function() {
                        $("#submit").addClass("working");
                        $("#reporte").html("<p align='center'>Generando reporte...</p>");
                    },
                    complete: function(data) {
                        $("#submit").removeClass("working");
                        $("#reporte").html(data.responseText);
                        $("#filtros").slideUp();
//                        $("a.pop", "#reporte").fancybox({
//                            'titlePosition' : 'over',
//                            'type': 'ajax',
//                            'onComplete': function(){
//                                iniMapa();
//                            }
//                        });
//                        $("a#aniRep", "#reporte").fancybox({
////                            'titlePosition' : 'over',
//                            'type': 'ajax',
//                            'overlayOpacity': 0.8,
//                            'overlayColor': '#000',
//                            'onComplete': function(){
//                                aniAuditoria();
//                            },
//                            'onClosed': function() {
//                                clearInterval(animacion);
//                            }
//                        });
                    }
                });
            }
            return false;
        },
        success: function(label) {
        }
    });
});