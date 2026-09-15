<?php 

//***************************
//      INICIO DE CLASE
//***************************
class UsuariosYPermisosControlador{

	static public function AgregarTblUsuariosYPermisosControlador(){
	    $data = UsuariosYPermisosModelo::AgregarTblUsuariosYPermisosModelo();
	    $num = 0;
	    foreach ($data as $row) {
	        $num += 1;
	        echo '
	        	<tr>
	            <td class="py-1">'.$row['CIDUSUARIOTABLERO'].'</td>
	            <td class="py-1">'.$row['CTIPOUSUARIO'].'</td>
	            <td class="py-1">'.$row['CIDCODIGOUSUARIOTABLERO'].'</td>
	            <td class="py-1">'.$row['CNOMBREUSUARIOTABLERO'].'</td>
	            <td class="py-1"><button data-toggle="modal" data-target="#mdl_updateuser" class="w-100 showdatauserboard btn btn-dark border-0 "><i class="fas fa-solid fa-wrench"></i></button></td>
	            </tr>
	        ';
	    }
    }

    static public function AgregarCbxCompanyUsuariosYPermisosControlador(){
	    $data = UsuariosYPermisosModelo::BuscarCbxCompanyUsuariosYPermisosModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDEMPRESA'].'">'.$row['CNOMBREEMPRESA'].'</option>';
	    }
	} 

	static public function AgregarCbxReportesUsuariosYPermisosControlador(){
	    $data = UsuariosYPermisosModelo::BuscarCbxReportesUsuariosYPermisosModelo();
	    $currentTipoReporte = null; // Variable para realizar un seguimiento del tipo de reporte actual
	    
	    foreach ($data as $row) {
	        if ($row['CTIPOREPORTE'] != $currentTipoReporte) {
	            // Si el tipo de reporte ha cambiado, crea un nuevo grupo optgroup
	            if ($currentTipoReporte !== null) {
	                echo '</optgroup>';
	            }
	            
	            echo '<optgroup label="' . $row['CTIPOREPORTE'] . '">';
	            $currentTipoReporte = $row['CTIPOREPORTE'];
	        }
	        
	        echo '<option value="' . $row['CIDREPORTE'] . '">' . $row['CNOMBRE'] . '</option>';
	    }
	    
	    // Cerrar el último grupo optgroup si es necesario
	    if ($currentTipoReporte !== null) {
	        echo '</optgroup>';
	    }
	}


	static public function AgregarNuevosUsuariosTableroControlador(){

	    $UsersOld= UsuariosYPermisosModelo::BuscarUsuariosDelTableroRepositorioModelo();
	    $UsersNew = UsuariosYPermisosModelo::BuscarTablerosDelTableroNuevoModelo();

	    $notFoundUsers = [];
	    foreach ($UsersOld as $userOld) {
	        $found = true;
	        foreach ($UsersNew as $userNew) {
	            if ($userOld['CODIGO'] === $userNew['CODIGO']) {
	                $found = false;
	                break;
	            }
	        }
	        if ($found) {
	            $notFoundUsers[] = $userOld;
	        }
	    }
	    //identifica los usuarios que fueron agregados
	    $num=0;
	    // Recorrer el nuevo array 
	    foreach ($notFoundUsers as $user) {
	        // Llamar a una función y pasar los datos del usuario para poder agregar los nuevos
	        UsuariosYPermisosModelo::AgregarNuevosUsuariosTableroModelo($user);
	        $num++;
	    }
	    echo $num;
	}

	static public function ActualizarUnicoUsuarioTableroControlador(){
	    $CODIGO = isset($_POST['CODIGO']) ? $_POST['CODIGO'] : 'null';
	    $USUARIO = isset($_POST['USUARIO']) ? $_POST['USUARIO'] : 'null';
	    $lvlsSelected = isset($_POST['LVLS']) ? explode(',', $_POST['LVLS']) : array(); // Convertir la cadena en un array
	    $ddbsSelected = isset($_POST['DDBS']) ? explode(',', $_POST['DDBS']) : array(); // Convertir la cadena en un array
	    $CONTRASENA = isset($_POST['CONTRASENA']) ? ($_POST['CONTRASENA'] == '' ? 'null' : sha1($_POST['CONTRASENA'])) : 'null';
	    $datacontroller = array(
	        "0" => $CODIGO,
	        "1" => $USUARIO,
	        "2" => $CONTRASENA,
	        "3" => $ddbsSelected,
	        "4" => $lvlsSelected
	    );
	    $CANTIDAD = UsuariosYPermisosModelo::VerificarExistenciaDeUsuarioIgualModelo($datacontroller);
	    if ($CANTIDAD == 1) {
	        
	        $answer = UsuariosYPermisosModelo::ActualizarUnicoUsuarioTableroModelo($datacontroller);
	        if ($answer == 1) {
	            echo '1';
	        }else{
	            echo '0';
	        }
	    }else{
	        echo '0';
	    }
	} 

	//************************************************************
	// BUSCA LOS REPORTES ACTIVOS DEL USUARIO SELECICONADO
	//************************************************************
	static public function MostrarDataSelectReportesControlador(){
	    $CODIGO = isset($_POST['CODIGO']) ? $_POST['CODIGO'] : '';
	    $data = UsuariosYPermisosModelo::BuscarLvlsReportesUsuarioModelo($CODIGO);

	    echo json_encode($data);
	}  

	//************************************************************
	// BUSCA LAS EMPRESAS ACTIVAS DEL USUARIO SELECCIONADO
	//************************************************************
	static public function MostrarDataSelectCompanysBdControlador(){
	    $CODIGO = isset($_POST['CODIGO']) ? $_POST['CODIGO'] : '';

	    $data = UsuariosYPermisosModelo::BuscarLvlsCompanysUsuarioModelo($CODIGO);

	    echo json_encode($data);
	} 







//***************************
//        FIN DE CLASE
//***************************
}




//***************************************************************
//				EJECUTA LA FUNCION AJAX QUE SE MANDE
//***************************************************************
$accionajax = (isset($_REQUEST['accionajax'])) ? $_REQUEST['accionajax'] : '';

switch($accionajax){

	
	case 'AgregarNuevosUsuariosTablero':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/UsuariosYPermisosModelo.php';
	UsuariosYPermisosControlador::AgregarNuevosUsuariosTableroControlador();
	break;

	case 'NuevoConceptoPorTienda':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/UsuariosYPermisosModelo.php';
	UsuariosYPermisosControlador::NuevoConceptoPorTiendaControlador();
	break;

	case 'AgregarTblUsuariosYPermisos':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/UsuariosYPermisosModelo.php';
	UsuariosYPermisosControlador::AgregarTblUsuariosYPermisosControlador();
	break;

	case 'ActualizarUnicoUsuarioTablero':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/UsuariosYPermisosModelo.php';
	UsuariosYPermisosControlador::ActualizarUnicoUsuarioTableroControlador();
	break;

	case 'MostrarDataSelectReportes':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/UsuariosYPermisosModelo.php';
	UsuariosYPermisosControlador::MostrarDataSelectReportesControlador();
	break;
	
	case 'MostrarDataSelectCompanysBd':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/UsuariosYPermisosModelo.php';
	UsuariosYPermisosControlador::MostrarDataSelectCompanysBdControlador();
	break;


}
