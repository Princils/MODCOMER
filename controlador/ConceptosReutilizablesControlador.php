<?php 	

//***************************
//      INICIO DE CLASE
//***************************
class ConceptosReutilizablesControlador{

	static public function AgregarCbxSucursalControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxSucursalModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDDIRSUCU'].'">'.$row['CSUCURSAL'].'</option>';
	    }
	}

	static public function AgregarTblConceptosTodosControlador(){
	    $id = $_POST['id'];
	    $data = ConceptosReutilizablesModelo::ConsultarConceptosTodos($id);
	    foreach ($data as $row) {
	        $dataonly = ConceptosReutilizablesModelo::ContultarConceptosPorTiendaModelo($row['CIDCONCEPTODOCUMENTO']);
	        $store = '';
	        foreach ($dataonly as $key) {
	            $store .= ' store_'.$key['CIDCONCEPTOPORTIENDA'].' ';
	        }
	        echo '<tr>
	        <td style="width: 95%; word-wrap: break-word; padding: 5px 10px;" class"py-0">'.$row['CNOMBRECONCEPTO'].'</td>
	        <td style="width: 5%; padding: 5px 10px;" class"py-0"><input type="checkbox" class=" '.$store.' sucu_'.$row['CIDDIRSUCU'].' mycheck2"  id="mycheck" name="'.$row['CIDCONCEPTODOCUMENTO'].'" checked></td>
	        </tr>';
	    }
	}  

	
	static public function AgregarCbxDocumentosControlador(){
		$tipos = array('1' => 3,	'2' => 4,	'3' => 5,	'4' =>6);
	    $data = ConceptosReutilizablesModelo::BuscarTiposDocumentosModelo($tipos);
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDDOCUMENTODE'].'">'.$row['CDESCRIPCION'].'</option>';
	    }
	}

	static public function CambiarCbxConceptosControlador(){
	    $id = $_POST['id'];
	    $data = ConceptosReutilizablesModelo::BuscarConceptosSegunIdDocuemntosModelo($id);
	    foreach ($data as $row) {
	        $dataonly = ConceptosReutilizablesModelo::BuscarFiltroConceptosPorTiendaSegunIdConceptoModelo($row['CIDCONCEPTODOCUMENTO']);
	        $store = '';
	        foreach ($dataonly as $key) {
	            $store .= ' store_'.$key['CIDCONCEPTOPORTIENDA'].' ';
	        }
	        echo '<tr>
	        <td style="width: 95%; word-wrap: break-word; padding: 5px 10px;" class"py-0">'.$row['CNOMBRECONCEPTO'].'</td>
	        <td style="width: 5%; padding: 5px 10px;" class"py-0"><input type="checkbox" class=" '.$store.' mycheck2  sucu_'.$row['CIDDIRSUCU'].'" name="'.$row['CIDCONCEPTODOCUMENTO'].'" checked></td>
	        </tr>';
	    }
	}  
	
	static public function AgrgearCbxConceptosPorTiendaControlador(){
	    $data = ConceptosReutilizablesModelo::AgrgearCbxConceptosPorTiendaModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDCONCEPTOPORTIENDA'].'">'.$row['CNOMBRECONCEPTOPORTIENDA'].'</option>';
	    }
	}



	static public function BuscarFiltroAutocompletadoProductoInputControlador(){
	   $data = ConceptosReutilizablesModelo::BuscarFiltroAutocompletadoProductoInputModelo();
	   echo $data; 
	}

	static public function BuscarFiltroAutocompletadoClienteInputControlador(){
	   $data = ConceptosReutilizablesModelo::BuscarFiltroAutocompletadoClienteInputModelo();
	   echo $data; 
	}

	//*******************************************************************************************

	static public function AgregarCbxClasificacion1ClienteControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxClasificacion1ClienteModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxTypoClienteControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxTypoClienteModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxZonaControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxZonaModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxAgenteClienteControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxAgenteClienteModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxClasificacion5ClienteControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxClasificacion5ClienteModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxClasificacion6ClienteControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxClasificacion6ClienteModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	//*******************************************************************************************

	static public function AddCbxAgente1Controlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxAgente1Modelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxAgenteClasificacion2Controlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxAgenteClasificacion2Modelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxAgenteClasificacion3Controlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxAgenteClasificacion3Modelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxAgenteClasificacion4Controlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxAgenteClasificacion4Modelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxAgenteClasificacion5Controlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxAgenteClasificacion5Modelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AddCbxAgenteClasificacion6Controlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxAgenteClasificacion6Modelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	//*******************************************************************************************

	static public function AgregarCbxLineaGeneralControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxLineaGeneralModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AgregarCbxLineaDetalladaControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxLineaDetalladaModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AgregarCbxIndicadorComisionControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxIndicadorComisionModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AgregarCbxTipoClasificacionControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxTipoClasificacionModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AgregarCbxRotacionControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxRotacionModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}

	static public function AgregarCbxRevisionDiariaControlador(){
	    $data = ConceptosReutilizablesModelo::AgregarCbxRevisionDiariaModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CIDVALORCLASIFICACION'].'">'.$row['CVALORCLASIFICACION'].'</option>';
	    }
	}


	static public function BuscarFiltroAutocompletadoAgenteInputControlador(){
	   $data = ConceptosReutilizablesModelo::BuscarFiltroAutocompletadoAgenteInputModelo();
	   echo $data; 
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
	case 'CambiarCbxConceptos':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosReutilizablesModelo.php';
	ConceptosReutilizablesControlador::CambiarCbxConceptosControlador();
	break;

	case 'AgregarTblConceptosTodos':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosReutilizablesModelo.php';
	ConceptosReutilizablesControlador::AgregarTblConceptosTodosControlador();
	break;

	case 'BuscarFiltroAutocompletadoClienteInput':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosReutilizablesModelo.php';
	ConceptosReutilizablesControlador::BuscarFiltroAutocompletadoClienteInputControlador();
	break;

	case 'BuscarFiltroAutocompletadoProductoInput':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosReutilizablesModelo.php';
	ConceptosReutilizablesControlador::BuscarFiltroAutocompletadoProductoInputControlador();
	break;

	case 'BuscarFiltroAutocompletadoAgenteInput':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosReutilizablesModelo.php';
	ConceptosReutilizablesControlador::BuscarFiltroAutocompletadoAgenteInputControlador();
	break;


	



}
