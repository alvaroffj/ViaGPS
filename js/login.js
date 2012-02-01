$(document).ready(function(){
    var validator = $("#formu").bind("invalid-form.validate",
        function() {
            $(".notification").html("<div>Debe completar todos lo campos requeridos</div>");
            $(".notification").attr("class", "notification error png_bg");
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