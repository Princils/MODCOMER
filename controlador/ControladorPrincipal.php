<?php 
require_once 'configuracion/Licencia.php';

class ControladorPrincipal{

	static public function PaginaControlador(){
		include 'vista/plantilla.php';
	}

	static public function LinkPaginaControlador(){
        if (isset($_GET['vista'])) {
          $link = $_GET['vista'];
        }else{
          $link = "login";
        }
        $respuesta = Paginas::RutaPagina($link);

        include ("$respuesta");
  	}

    static public function ValidarReporteUsuarioControlador($reporte,$tipo){
        $idreporte = ModeloPrincipal::BusacrReporteModelo($reporte,$tipo);
        $idreporte = 'lvl'.$idreporte;
        return (isset($_SESSION[$idreporte]) ? ($_SESSION[$idreporte] == 1 ? true : false) : false);
    }

    static public function VerificarConexion(){
        $Validacion = ModeloPrincipal::ValidarConexionTxtModelo();
        return $Validacion;
    }

    static public function VerificarPaginasControlador(){
        // 1. VALIDACIÓN Y REDIRECCIÓN SI LA LICENCIA ESTÁ VENCIDA / ALTERADA
        $checkLicencia = Licencia::ValidarEstadoLicencia();
        
        if (!$checkLicencia['valido']) {
            if (!isset($_GET['vista']) || $_GET['vista'] != "asignarnuevovencimiento") {
                header('Location: index.php?vista=asignarnuevovencimiento');
                exit();
            }
            return; 
        }

        // 2. VALIDACIÓN DE CONEXIÓN A BASE DE DATOS Y SESIÓN
        $Validacion = ControladorPrincipal::VerificarConexion();
        if ($Validacion == false) {
            session_unset();
        }

        if ($Validacion) {
            if (isset($_GET['vista'])) {
                // Permitir el acceso a asignarnuevovencimiento sin cerrar/alterar la sesión activa
                if ($_GET['vista'] == "asignarnuevovencimiento") {
                    return;
                }

                if ($_GET['vista'] != "login") {
                    if (!isset($_SESSION['codigo_usuario'])) {
                        header('Location: index.php?vista=login');
                        exit();
                    }
                } else {
                    unset($_SESSION['nivel_usuario']);
                    unset($_SESSION['codigo_usuario']);
                    unset($_SESSION['nombre_usuario']);
                }
            } else {
                header('Location: index.php?vista=login');
                exit();
            }
        } else {
            if (isset($_GET['vista'])) {
                if ($_GET['vista'] != "conexion_bd") {
                    header('Location: index.php?vista=conexion_bd');
                    exit();
                }
            } else {
                header('Location: index.php?vista=conexion_bd');
                exit();
            }
        }
    }
}