var COLORS = [["red", "#ff0000"], ["orange", "#ff8800"], ["green","#008000"], ["blue", "#000080"], ["purple", "#800080"]];
var options = {};
var lineCounter_ = 0;
var shapeCounter_ = 0;
var markerCounter_ = 0;
var colorIndex_ = 0;
var featureTable_;
var map;
var btnPre;
var poligono;
var tPol;

function select(buttonId) {
    $("#"+btnPre).attr("src", "http://google.com/mapfiles/ms/t/"+btnPre+"u.png");
    $("#"+buttonId).attr("src", "http://google.com/mapfiles/ms/t/"+buttonId+"d.png");
    btnPre = buttonId;
    if(buttonId!="Bs") {
        if(poligono) {
            poligono.stopEdit();
            poligono.setMap(null);
        }
        poligono = null;
        if(buttonId == "Bl") tPol = 1;
        else if(buttonId == "Bp") tPol = 2;
    }
}

function stopEditing() {
    select("Bs");
    google.maps.event.clearListeners(map, 'click');
//    google.maps.event.removeListener(map, 'click', addPunto);
}

function getColor(named) {
    return COLORS[(colorIndex_++) % COLORS.length][named ? 0 : 1];
}

function startShape() {
    select("Bp");
    color = getColor(false);
    poligono = new google.maps.Polygon({
        strokeColor: color,
        strokeWeight: 2,
        strokeOpacity: 0.7,
        fillColor: color,
        fillOpacity: 0.2
    });
    
    google.maps.event.addListener(map, 'click', addPunto);
}



function startLine() {
    select("Bl");
    color = getColor(false);
    poligono = new google.maps.Polyline({
        strokeColor   : color,
        strokeOpacity : 0.7,
        strokeWeight  : 4
    });
    google.maps.event.addListener(map, 'click', addPunto);
}

function addPunto(event) {
    var path = poligono.getPath();
    path.push(event.latLng);
    startDrawing(poligono, " ", function() {}, color);
}

function startDrawing(poly, name, onUpdate, color) {
    poly.setMap(map);
    poly.stopEdit();
    poly.runEdit(true);
}

function initialize() {
    geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(-33.42482011764062,-70.56612968444824);
    var myOptions = {
        zoom: 16,
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map"), myOptions);
    select("Bs");
    btnPre = "Bs";
}

function addPolForm(p, f) {
    puntos = p.getPath().b;
    var n = puntos.length;
    var form = $("#"+f);
    if(tPol==2) n = n-1;
    form.append("<input type='hidden' name='tipo' value='"+tPol+"'>");
    form.append("<input type='hidden' name='nPuntos' value='"+n+"'>");
    for(var i=0; i<n; i++) {
        form.append("<input type='hidden' name='p_"+i+"' value='"+puntos[i].lat()+","+puntos[i].lng()+"'>");
    }
}

function cargaPol(f) {
    var form = $("#"+f);
    var puntos = form.find("input[name^='p_']");
    tPol = form.find("input[name='tipo']")[0].value;
    ptos = Array();
    var coord;
    if(puntos.length>0) {
        for(var i=0; i<puntos.length; i++) {
            coord = puntos[i].value.split(",");
            ptos[i] = new google.maps.LatLng(coord[0], coord[1]);
        }
        var color = getColor(false);
        switch(tPol) {
            case '1':
                poligono = new google.maps.Polyline({
                    strokeColor : color,
                    strokeOpacity : 0.7,
                    strokeWeight : 4,
                    path : ptos
                });
                break;
            case '2':
                coord = puntos[0].value.split(",");
                ptos[puntos.length] = new google.maps.LatLng(coord[0], coord[1]);
                poligono = new google.maps.Polygon({
                    path: ptos,
                    strokeColor: color,
                    strokeWeight: 2,
                    strokeOpacity: 0.7,
                    fillColor: color,
                    fillOpacity: 0.2
                });
                break;
        }
        var bounds = new google.maps.LatLngBounds();
        for (i=0;i<ptos.length;i++) {
            bounds.extend(ptos[i]);
        }
        map.fitBounds(bounds);
        startDrawing(poligono, " ", function() {}, color);
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
            if(poligono!=null) {
                addPolForm(poligono, "formu");
                form.submit();
            } else {
                $(".notification").html("<div>Debe dibujar una Geozona o Geofrontera</div>");
                $(".notification").attr("class", "notification error png_bg");
            }
        },
        success: function(label) {
        }
    });
    initialize();
    cargaPol("formu");
});