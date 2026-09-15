function ProbarConexionServerBd() {
    var formData = $('#frm_conexiondb').serialize()
	$.ajax({
		type: 'POST',
		url: 'controlador/ConexionBDControlador.php',
            //LA PARTE DEL ACTION ES PARA PODER ENTRAR A LA FUNCION DENTRO DEL CONTROLADOR
		data:formData+'&accionajax=ProbarConexionServerBd',
            //SI TODO SE EJECUTA DE MANERA CORRECTA Y SE ENCONTRO UN USAURIO SE REGRESARA UN 1
            //CASO CONTRARIO SERA UN 0
		success: function(response) {
			if (response == 1) {
				Swal.fire({
					title: 'Conexión Exitosa',
					text: 'Todo correcto para su ejecucion',
					icon: 'success',
					showConfirmButton: false,
					confirmButtonText: 'Continuar',
					timer: 1500
				})
			}else{
				Swal.fire({
					title: 'Conexión Fallida',
					text: 'Intente nuevamente y Verifique los datos',
					icon: 'error',
					showConfirmButton: false,
					confirmButtonText: 'Continuar',
					timer: 1500
				})
			}
		}
	});
}