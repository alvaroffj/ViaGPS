$(document).ready(function(){
    var $btn = $("#btn"),
        $form = $("#formu"),
        $msg = $("#msg"),
        validator = $form.bind("invalid-form.validate",
        function() {
            $msg.html("Debe ingresar su usuario y contrase&ntilde;a");
        }).validate({
        errorPlacement: function(error, element) {
        },
        submitHandler: function(form) {
            if(!$btn.hasClass("disabled")) {
                $btn.addClass("disabled");
                $.ajax({
                    url: form.action,
                    type: "post",
                    dataType: "json",
                    data: $(form).serializeArray(),
                    success: function(data) {
//                        console.log(data);
                        $msg.html(data.MENSAJE);
                        if(data.ERROR == 0) { //OK
                            window.location.reload(true);
                        } else {
                            $btn.removeClass("disabled");
                        }
                    }
                });
            }
            return false;
        },
        success: function(label) {
        }
    });
});