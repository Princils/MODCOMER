function DesabilitarSubmit(cbx,btn){
    var id = document.getElementById(cbx).value;
    if (id != 0) {
        $("#"+btn).removeAttr("disabled");
    }else{
        $("#"+btn).attr('disabled', 'disabled');
    }
    $('#'+cbx).on('change', function() {
        var id = $(this).val();
        if (id != 0) {
            $("#"+btn).removeAttr("disabled");
        }else{
            $("#"+btn).attr('disabled', 'disabled');
        }
    })
}