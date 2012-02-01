$(document).ready(function(){
    $("table#inGrupo").tablesorter({ sortList: [[1,0]] });
    $("table#outGrupo").tablesorter({ sortList: [[1,0]] });
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