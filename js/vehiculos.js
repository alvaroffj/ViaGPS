function getDireccion(latlon, number) {
    var ll = latlon.split(",");

    $.ajax({
        url: "?sec=monitoreo&get=direccion&lat="+ll[0]+"&lon="+ll[1],
        type: 'get',
//        dataType: json,
        beforeSend: function() {

        },
        complete: function(data) {
            r = $.parseJSON(data.responseText);
            $("#pos_"+number).html(r.DIRECCION);
        }
    });
}
var $posicion;
var geocoder;
$(document).ready(function(){
    $("table#grupos").tablesorter({ sortList: [[1,0]] });
    $("table#vehiculos").tablesorter({ sortList: [[1,0]] });
    $posicion = $(".posicion");
    geocoder = new google.maps.Geocoder();
    $posicion.each(function(){
        dire = $(this).html();
        id = this.id.split("_")[1];
        $(this).html("Cargando...");
        getDireccion(dire, id);
    });

    $('.edit').editable('?&sec=configuracion&ssec=vehiculo&do=mod_dev', {
        indicator : 'Guardando...',
        tooltip : 'Click para editar',
        id : $(this).id,
        name : "nomDev",
//        submit : 'Guardar',
//        width: 100,
//        height: 15,
//        cssclass: "inline-form",
//        onblur: "ignore"
    });
});