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
        map.clearOverlays();
        poligono = null;
        if(buttonId == "Bl") tPol = 1;
        else if(buttonId == "Bp") tPol = 2;
    }
}

function stopEditing() {
    select("Bs");
}

function getColor(named) {
    return COLORS[(colorIndex_++) % COLORS.length][named ? 0 : 1];
}

function getIcon(color) {
    var icon = new GIcon();
    icon.image = "http://google.com/mapfiles/ms/micons/" + color + ".png";
    icon.iconSize = new GSize(32, 32);
    icon.iconAnchor = new GPoint(15, 32);
    return icon;
}

function startShape() {
    select("Bp");
    var color = getColor(false);
    var polygon = new GPolygon([], color, 2, 0.7, color, 0.2);
    startDrawing(polygon, " ", function() {}, color);
}

function startLine() {
    select("Bl");
    var color = getColor(false);
    var line = new GPolyline([], color);
    startDrawing(line, "", function() {}, color);
}

function startDrawing(poly, name, onUpdate, color) {
    map.addOverlay(poly);
    poly.enableDrawing(options);
    poly.enableEditing({onEvent: "mouseover"}, function(){});
    poly.disableEditing({onEvent: "mouseout"}, function(){});

    GEvent.addListener(poly, "endline", function() {
//        addPolForm(poly, "formu");
        select("Bs");
    });

    GEvent.addListener(poly, "lineupdated", function(latlng, index) {
        poligono = poly;
//        if(poligono!=null)
//            addPolForm(poly, "formu");
    });

    GEvent.addListener(poly, "click", function(latlng, index) {
        if (typeof index == "number") {
            poly.deleteVertex(index);
        }
        poligono = poly;
    });
}

function updateMarker(marker, cells, opt_changeColor) {
    alert("updateMarker");
    if (opt_changeColor) {
        var color = getColor(true);
        marker.setImage(getIcon(color).image);
        cells.color.style.backgroundColor = color;
    }
    var latlng = marker.getPoint();
}

function initialize() {
//    if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
        map.setCenter(new GLatLng(-33.42482011764062,-70.56612968444824), 13);
        map.addControl(new GSmallMapControl());
        map.addControl(new GMapTypeControl());
        map.clearOverlays();
        select("Bs");
        btnPre = "Bs";
//    } else {
//        alert("fail");
//    }
}

function addPolForm(p, f) {
    alert(p.getVertexCount());
    var n = p.getVertexCount();
    var form = $("#"+f);
    if(tPol==2) n = n-1;
    form.append("<input type='hidden' name='tipo' value='"+tPol+"'>");
    form.append("<input type='hidden' name='nPuntos' value='"+n+"'>");
    for(var i=0; i<n; i++) {
        form.append("<input type='hidden' name='p_"+i+"' value='"+p.getVertex(i).y+","+p.getVertex(i).x+"'>");
    }
}

function cargaPol(f) {
    var form = $("#"+f);
    var puntos = form.find("input[name^='p_']");
    tPol = form.find("input[name='tipo']")[0].value;
    var ptos = Array();
    var coord;
    if(puntos.length>0) {
        for(var i=0; i<puntos.length; i++) {
            coord = puntos[i].value.split(",");
            ptos[i] = new GLatLng(coord[0], coord[1]);
        }
        var color = getColor(false);
        switch(tPol) {
            case '1':
                poligono = new GPolyline(ptos, color);
                break;
            case '2':
                coord = puntos[0].value.split(",");
                ptos[puntos.length] = new GLatLng(coord[0], coord[1]);
                poligono = new GPolygon(ptos, color, 2, 0.7, color, 0.2);
                break;
        }
        map.setCenter(ptos[ptos.length/2], 13);
        startDrawing(poligono, " ", function() {}, color);
//        poligono = new GPolygon(ptos);
//        map.addOverlay(poligono);
    }
}

$(document).ready(function() {
    initialize();
    cargaPol("formu");
    var validator = $("#formu").bind("invalid-form.validate",
    function() {
            $("#msg").html("Debe completar todos lo campos requeridos");
            $("#msg").attr("class", "msg fail");
        }).validate({
            errorPlacement: function(error, element) {
            },
            submitHandler: function(form) {
                if(poligono!=null) {
                    addPolForm(poligono, "formu");
                    form.submit();
                } else {
                    $("#msg").html("Debe dibujar un GeoArea o GeoLimite");
                    $("#msg").attr("class", "msg fail");
                }
            },
            success: function(label) {
            }
        });
});