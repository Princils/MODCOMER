<?php

//***************************
//		INICIO DE CLASE
//***************************
class ConexionBDControlador {

	static public function ProbarConexionServerBd(){
	    //extraemos los datos del formulario
		$datacontrolador = array("0" => $_POST['servidor'],"1" => $_POST['basededatos'],"2"=>$_POST['usuario'],"3"=>$_POST['password']);
		$respuesta = Conexion::ConnectarDinamico($datacontrolador);
		if ($respuesta) {
			echo '1';        
		}else{
			echo '0';
		}           
	} 


	static public function GuardarConexionServerBd(){
		if (isset($_POST['btn_conectarservirdorbd'])) {
	        //extraemos los datos del formulario
			$datacontrolador = array("0" => $_POST['servidor'],"1" => $_POST['basededatos'],"2"=>$_POST['usuario'],"3"=>$_POST['password']);
			$respuesta = Conexion::ConnectarDinamico($datacontrolador);
			if ($respuesta) {
				$_SESSION['servidor'] = $_POST['servidor'];       
				$_SESSION['basededatos'] = $_POST['basededatos'];       
				$_SESSION['usuario'] = $_POST['usuario'];       
				$_SESSION['password'] = $_POST['password'];
				$servidor =$_POST['servidor'];
				$basededatos =$_POST['basededatos'];
				$usuario =$_POST['usuario'];
				$password =$_POST['password'];
	             // Ruta y nombre del archivo de texto
				$archivo = 'configuracion/data_conection.txt';

	            // Abrir el archivo en modo escritura
				$file = fopen($archivo, 'w');
	            // Escribir los datos actualizados en el archivo
				fwrite($file, "Servidor: $servidor" . PHP_EOL);
				fwrite($file, "Base de Datos: $basededatos" . PHP_EOL);
				fwrite($file, "Usuario: $usuario" . PHP_EOL);
				fwrite($file, "Contraseña: $password" . PHP_EOL);
				fwrite($file, "---------------------" . PHP_EOL);
	            // Cerrar el archivo
				fclose($file);    

				if (isset($_SESSION['codigo_usuario'])) {
					?>
					<script type="text/javascript">
						Swal.fire({
							title: 'Conexión Realizada',
							text: 'La Conexión se guardo correctamente',
							icon: 'success',
							showConfirmButton: false,
							confirmButtonText: 'Continuar',
							timer: 1500
						})
					</script>
					<?php  
				}else{
					?>
					<script type="text/javascript">
						Swal.fire({
							title: 'Conexión Realizada',
							text: 'La Conexión se guardo correctamente',
							icon: 'success',
							showConfirmButton: true,
							confirmButtonText: 'Continuar',
						}).then((result) => {
							if (result.isConfirmed) {      
								window.location.href = "index.php?vista=login";   
							}
						})
					</script>
					<?php  
				}

			}else{
				?>
				<script type="text/javascript">
					Swal.fire({
						title: 'Conexión Fallida',
						text: 'Intente nuevamente y Verifique los datos',
						icon: 'error',
						showConfirmButton: false,
						confirmButtonText: 'Continuar',
						timer: 1500
					})
				</script>
				<?php
			}
		}
	}

//***************************
//		  FIN DE CLASE
//***************************
}








//***************************************************************
//				EJECUTA LA FUNCION AJAX QUE SE MANDE
//***************************************************************
$accionajax = (isset($_REQUEST['accionajax'])) ? $_REQUEST['accionajax'] : '';

switch($accionajax){

	case 'ProbarConexionServerBd':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConexionBDModelo.php';
	ConexionBDControlador::ProbarConexionServerBd();
	break;


}
