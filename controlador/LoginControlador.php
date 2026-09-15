<?php 

//***************************
//      INICIO DE CLASE
//***************************
class LoginControlador extends Conexion{

    function __construct(){
        session_unset();
    }


    static public function IniciarSesionControlador(){
        if (isset($_POST['verrificarsession'])) {
            
            $datacontroller = array("0" => txtserver,"1" => txtdatabase,"2"=>txtuser,"3"=>txtpassword);




            InicializadorTablasModelo::VerificarTablasPrincipalesModelo($datacontroller);
            //extraemos los datos del formulario
            $datacontroller = array("0" => $_POST['usuario'],"1"=>sha1($_POST['password']));
            //verifamos el ID que tienen el usuario y la contraseña por separado
            $respuesta_usuario = LoginModelo::VerificarUsuarioModelo($datacontroller);
            //Agregamos el id de usuario obtenido en la funcion anterior para verificar
            $datacontroller = array("0" => $_POST['usuario'],"1"=>sha1($_POST['password']),"2" => $respuesta_usuario,);
            //Verificamos la contraseña con el id y la password guardada en datos controller si es correcto nos debera arrojar 1, caso contrario nos arrojara un 0
            $respuesta_password = LoginModelo::VerificarPasswordModelo($datacontroller);
            if ($respuesta_password == 1 && $respuesta_usuario > 0) {
                //aqui asignamos las credenciales y el nivel que tendran los usuarios al entrar a la aplicacion 

                $datacontroller = array("0" => $_POST['usuario'],"1"=>sha1($_POST['password']),"2" => $respuesta_usuario,);

                //CREDENCIALES BASICAS DEL USUARIO 
                $data = LoginModelo::CredencialesDeUsuarioModelo($datacontroller);

                //asignamos las credenciales en la session para identificar los tipos de usuarios
                $_SESSION['id_usuario'] = $data["CIDUSUARIOTABLERO"];
                $_SESSION['nivel_usuario'] = $data["CTIPOUSUARIO"];
                $_SESSION['codigo_usuario'] = $data["CIDCODIGOUSUARIOTABLERO"];
                $_SESSION['nombre_usuario'] = $data["CNOMBREUSUARIOTABLERO"];

                //sacmos todos los reportes que existen
                $respuesta_reportes = LoginModelo::ConultarReportesModelo();
                //sacmos todos los reportes que el usuario puede ver
                $data_reportes = LoginModelo::ConsultarReportesDeUsuarioModelo($datacontroller);

                // Extraer los valores de 'CIDREPORTE' de $data_reportes
                $data_reportes_ids = array_column($data_reportes, 'CIDREPORTE');

                foreach ($respuesta_reportes as $reporte) {
                    //id del reporte
                    $idreporte = $reporte['CIDREPORTE'];
                    //verifica si el $idreporte existe en $datareportes
                    $isset = in_array($idreporte, $data_reportes_ids) ? 1 : 0;
                    $_SESSION['lvl'.$idreporte] = $isset;
                }

                header('Location: index.php?vista=SeleccionarEmpresa');
                exit;
            } else {
                header('Location: index.php?vista=login&error=1');
                exit;
            }
            
        } 
    }

//***************************
//        FIN DE CLASE
//***************************
}