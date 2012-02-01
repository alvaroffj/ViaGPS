var valAnt;

function desactivaMenor(sel) {
    var hora = sel[sel.selectedIndex].value;
    var name = sel.name.split("_");
    var dest = name[0]+"_fin_"+name[2];
    console.log(dest);
    var op = $("#"+dest).children();
    var n = op.length;
    var i;
    for(i=0; i<n; i++) {
        op[i].disabled = false;
    }
    for(i=0; i<hora; i++) {
        op[i].disabled = true;
    }
}

function setNext(sel) {
    var idSel = sel[sel.selectedIndex].value;
    var ope, par;
    switch(sel.id) {
        case 'Tipo':
            switch(idSel) {
                case "1":
                    par = Array(Array("2", "Tiempo"));
                    ope = Array(Array("1", "Mayor a"), Array("2", "Menor a"));
                    setSelect("Parametro", par, 1);
                    setSelect("Operador", ope, 2);
                    setValor($("#Parametro")[0]);
                    break;
                case "2":
                    par = Array(Array("1", "Velocidad"));
                    ope = Array(Array("1", "Mayor a"), Array("2", "Menor a"));
                    setSelect("Parametro", par, 2);
                    setSelect("Operador", ope, 1);
                    setValor($("#Parametro")[0]);
                    break;
                case "3":
                    par = Array(Array("3", "Geozona"), Array("4", "Geofrontera"), Array("5", "Punto de interes"));
                    ope = Array(Array("4", "Entra a"), Array("5", "Sale de"));
                    setSelect("Parametro", par, 3);
                    setSelect("Operador", ope, 3);
                    setValor($("#Parametro")[0]);
                    break;
                case "4":
                    $.ajax({
                        url: "?sec=configuracion&ssec=alarma&get=sensores",
                        type: "get",
                        complete: function(data) {
                            var res = $.parseJSON(data.responseText);
                            var n = res.length;
                            var par = [];
                            for(var i=0; i<n; i++) {
                                par[i] = Array(res[i].ID_SENSOR, res[i].NOM_SENSOR);
                            }
                            setSelect("Parametro", par, 4);
                            setValor($("#Parametro")[0]);
                        }
                    });
                    break;
            }
            break;
        case "Parametro":
            if($("#Tipo")[0].selectedIndex!=4) {
                switch(idSel) {
                    case "3":
                        ope = Array(Array("4", "Entra a"), Array("5", "Sale de"));
                        setSelect("Operador", ope, null);
                        break;
                    case "4":
                        ope = Array(Array("6", "Cruza"));
                        setSelect("Operador", ope, null);
                        break;
                    case "5":
                        ope = Array(Array("4", "Entra a"), Array("5", "Sale de"));
                        setSelect("Operador", ope, null);
                        break;
                }
            } else {
                console.log("sensor");
                $.ajax({
                    url: "?sec=configuracion&ssec=alarma&get=sensor&id="+idSel,
                    type: "get",
                    complete: function(data) {
                        var res = $.parseJSON(data.responseText);
                        switch(res.TIPO_PROCESO_SENSOR) {
                            case 1:
                                ope = Array(Array("7", "Esta"));
                                break;
                            case 2:
                                ope = Array(Array("1", "Mayor a"), Array("2", "Menor a"));
                                break;
                            case 3:
                                ope = Array(Array("1", "Mayor a"), Array("2", "Menor a"));
                                break;
                        }
                        setSelect("Operador", ope, null);
                    }
                });
            }
            break;
    }
}

function setSelect(idSel, par, padre) {
    var n = par.length;
    var op;
    for(var i=0; i<n; i++) {
        if(i==0)
            op += "<OPTION VALUE='"+par[i][0]+"' SELECTED>"+par[i][1]+"</OPTION>";
        else
            op += "<OPTION VALUE='"+par[i][0]+"'>"+par[i][1]+"</OPTION>";
    }
    var sel = $("#"+idSel);
    sel.html(op);
    sel.attr("padre", padre);
}

function getSelect(idSel, par) {
    var n = par.length;
    var op;
    for(var i=0; i<n; i++) {
        if(i==0)
            op += "<OPTION VALUE='"+par[i][0]+"' SELECTED>"+par[i][1]+"</OPTION>";
        else
            op += "<OPTION VALUE='"+par[i][0]+"'>"+par[i][1]+"</OPTION>";
    }
    op = "<SELECT id='"+idSel+"' name='"+idSel+"'>"+op+"</SELECT>";
    return op;
}

function setValor(sel) {
    var valAct = sel[sel.selectedIndex].value;
    console.log("valAct: "+valAct);
    var formAux;
    if($(sel).attr("padre")!=4) {
        formAux = $("#val_"+valAct).html();
        $("#formu #valor").html(formAux);
        setNext(sel);
    } else {
        $("#val_"+valAnt).attr("style", "display:none;");
        $.ajax({
            url: "?sec=configuracion&ssec=alarma&get=sensor&id="+valAct,
            type: "get",
            complete: function(data) {
                var res = $.parseJSON(data.responseText);
                console.log(res);
                if(res.TIPO_PROCESO_SENSOR == 1) {
                    ope = Array(Array("7", "Esta"));
                    $.ajax({
                        url: "?sec=configuracion&ssec=alarma&get=sensorOp&id="+valAct,
                        type: "get",
                        complete: function(data) {
                            var res = $.parseJSON(data.responseText);
                            console.log(res);
                            var n = res.length;
                            var par = [];
                            for(var i=0; i<n; i++) {
                                par[i] = Array(res[i].ID_SENSOR_OPCION, res[i].SENSOR_OPCION);
                            }
                            $("#formu #valor").html(getSelect("valor", par)).attr("style", "display:inline;");
                        }
                    });
                } else {
                    ope = Array(Array("1", "Mayor a"), Array("2", "Menor a"));
                    $("#formu #valor").html("<input type='text' name='valor' value='' class='text-input required'/><label>"+res.UNIDAD_SENSOR+"</label>");
                }
                setSelect("Operador", ope, null);
            }
        });
//        setNext(sel);
    }
    valAnt = valAct;
}

function getDevices(sel, idDest) {
    var idGr = sel[sel.selectedIndex].value;
    $.ajax({
        url: "?sec=configuracion&ssec=alarma&get=devByGrupo&id_grupo="+idGr,
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
    valAnt = 1;
    $("table#lista").tablesorter({ sortList: [[1,0]] });
    $("table#reglas").tablesorter({ sortList: [[1,0]] });
    $("table#vehiculos").tablesorter({ sortList: [[1,0]] });
    $("table#acciones").tablesorter({ sortList: [[1,0]] });
    var validator = $("#alerta").bind("invalid-form.validate",
        function() {
            $(".alert-message").html("<p>Debe completar todos lo campos requeridos</p>").attr("class", "alert-message error");
        }).validate({
        errorPlacement: function(error, element) {
        },
        submitHandler: function(form) {
            form.submit();
        },
        success: function(label) {
        }
    });
});