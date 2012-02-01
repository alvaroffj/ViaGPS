function addUserGrupo() {
    var grupoID = $("#grupo option:selected").val();
    var userID = $("#userID").val();
    if(grupoID>0) {
        $.ajax({
            url: "?sec=configuracion&ssec=usuario&do=add_grupo",
            type: 'post',
            dataType: 'json',
            data: {
                id_grupo: grupoID
                , id_usuario: userID
            },
            beforeSend: function() {

            },
            complete: function(data) {
                var res = $.parseJSON(data.responseText);
                if(res.error == 0) {
                    var fila = "<li id=\""+res.idUsGr+"\" style=\"position:relative; padding: 5px; border-left: 2px solid #ccc; margin-bottom: 4px;\"><a href=\"?sec=vehiculo&op=mod_grupo&id="+res.groupID+"\">"+res.displayName+"</a><a onClick=\"delUserGrupo("+res.idUsGr+"); return false;\" style=\"cursor:pointer; position: absolute; right: 0;\"><img src=\"img/delete.png\" border=\"0\" title=\"Quitar grupo\" alt=\"Quitar grupo\"/></a></li>";
                    $(fila).hide().appendTo("#grupos").fadeIn();
                    $(".alert-message").html("<p>El grupo fue agregado correctamente</p>")
                        .attr("class", "alert-message success");
                } else {
                    $(".alert-message").html("<p>El grupo NO pudo ser agregado, intentelo de nuevo</p>")
                        .attr("class", "alert-message error");
                }
            }
        });
    }
}

function delUserGrupo(id) {
    $.ajax({
            url: "?sec=configuracion&ssec=usuario&do=del_grupo",
            type: 'post',
            dataType: 'json',
            data: {
                idUsGr: id
            },
            beforeSend: function() {

            },
            complete: function(data) {
                var res = $.parseJSON(data.responseText);
                if(res.error == 0) {
                    $("#"+id, "#grupos").fadeOut();
                } else {
                    $(".alert-message").html("<p>El grupo NO pudo ser quitado, intentelo de nuevo</p>")
                        .attr("class", "alert-message error");
                }
            }
        });
}

$(document).ready(function(){
    var validator = $("#formu").bind("invalid-form.validate",
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