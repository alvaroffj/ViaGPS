//var reportes = [];
//var repJson = [];
//repJson[0] = 1;
//repJson[1] = 1;
//repJson[2] = 0;
//repJson[3] = 1;
//repJson[4] = 0;
var reporte = function(c, json, mapa) {
    this.controlador = c;
    this.isJSON = json;
    this.showMapa = mapa;
    this.getControlador = function() {return this.controlador}
}

var reportes = [];
reportes[0] = new reporte("auditoria", 1, 1);
reportes[1] = new reporte("alarma", 1, 1);
reportes[2] = new reporte("recorrido", 0, 0);
reportes[3] = new reporte("velocidad", 1, 1);
reportes[4] = new reporte("consumo", 0, 0);

var reporteSel = -1;
var $resize;
var $mainNav = [];

function setReporte(n) {
    reporteSel = n;
    $.ajax({
        url: "?sec=reporte&ssec="+reportes[n].controlador+"&ajax",
        type: 'get',
        beforeSend: function() {
        },
        complete: function(data) {
            if(reportes[reporteSel].showMapa) {
                $map_canvas.show();
            } else {
                $map_canvas.hide();
            }
            $("#bar", $lateralLeft).html(data.responseText);
            clearInterval(monitoreo);
            hideAllDevice();
            desactivaTodos();
        }
    });
}

function setSec() {
    $url = $.url();
    $sec = $url.fsegment(1);
    switch($sec) {
        case "reporte":
            $ssec = $url.fsegment(2);
            for(var i=0; i<reportes.length; i++) {
                if(reportes[i].controlador==$ssec) {
                    break;
                }
            }
            creaPinVehiculos(false);
            setReporte(i);
            $mainNav[1].addClass("active");
            $mainNav[0].removeClass("active");
            $mainNav[2].removeClass("active");
            break;
        default:
            creaPinVehiculos(true);
            $mainNav[0].addClass("active");
            $mainNav[1].removeClass("active");
            $mainNav[2].removeClass("active");
            break;
        case "monitoreo":
            creaPinVehiculos(true);
            $mainNav[0].addClass("active");
            $mainNav[1].removeClass("active");
            $mainNav[2].removeClass("active");
            break;
    }
}

$(document).ready(function() {
    $resize = $("#resize");
    $mainNav[0] = $($("#main-nav").children()[0]);
    $mainNav[1] = $($("#main-nav").children()[1]);
    $mainNav[2] = $($("#main-nav").children()[2]);
    $resize.draggable({
        axis: 'x',
        stop: function(event, ui) {
            $lateralLeft.width(event.pageX-12);
            if($reporte && reporteSel>=0 && !reportes[reporteSel].showMapa) {
                $reporte.css({
                    "margin-left":($lateralLeft.width()+20+$lateralLeft.position().left)+"px",
                    "margin-right":(5-($lateralRight.position().left-$(window).width()))+"px"
                });
            }
        }
    });
});