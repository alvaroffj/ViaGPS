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
    $('div.btn-group[data-toggle="buttons-radio"]').each(function(){
        var group   = $(this);
        var form    = group.parents('form').eq(0);
        var name    = group.attr('name');
        var hidden  = $('input[name="' + name + '"]', form);
        $('button', group).each(function(){
        var button = $(this);
        button.live('click', function(e){
            hidden.val($(this).val());
            e.preventDefault();
        });
        if(button.val() == hidden.val()) {
            button.addClass('active');
        }
        });
    });
    
    $('div.btn-group[data-toggle="buttons-checkbox"]').each(function(){
        var group   = $(this);
        var form    = group.parents('form').eq(0);
        var name    = group.attr('name');
        $('button', group).each(function(){
        var button = $(this);
        button.live('click', function(e){
            var btn = $(this);
            var hidden  = $('input[name="' + btn.attr("name") + '"]', form);
            if(btn.hasClass("active")) {
                hidden.val(0);
            } else {
                hidden.val(btn.val());
            }
            e.preventDefault();
        });
        });
    });
});