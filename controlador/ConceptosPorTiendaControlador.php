<?php 

//***************************
//      INICIO DE CLASE
//***************************
class ConceptosPorTiendaControlador{


	static public function AgregarTblConceptosPorTiendaControlador(){
		$data = ConceptosPorTiendaModelo::AgregarTblConceptosPorTiendaModelo();

		$num = 0;
		foreach ($data as $row) {
			$num += 1;
			echo '
			<tr>
			<td class="py-1">'.$num.'</td>
			<td class="py-1">'.$row['CODIGO'].'</td>
			<td class="py-1">'.$row['NOMBRE'].'</td>
			<td class="py-1 text-center"><button type="button" class="btn btn-primary w-100 showconceptsperStore" id="mdl_concepts" data-toggle="modal" data-target="#ModalConceptos "  ><i class="fas fa-fw fa-eye"></i></button></td>

			<td class="py-1"><button class="w-100 btn btn-danger fw-bold border-0 deleteconceptperstore"><i class="fas fa-fw fa-ban"></i></button></td>
			</tr>
			';
		}
	} 

	static public function NuevoConceptoPorTiendaControlador(){
		$CODIGO = isset($_POST['CODIGO']) ? $_POST['CODIGO'] : 'null';
		$NOMBRE = isset($_POST['NOMBRE']) ? $_POST['NOMBRE'] : 'null';
		$datacontroller = array(
			"0" => $CODIGO,
			"1" => $NOMBRE,
		);
		$CANTIDAD = ConceptosPorTiendaModelo::VerificarConceptosPorTiendaNoRepetidoModelo($datacontroller);
		if ($CANTIDAD == 0) {
			$answer = ConceptosPorTiendaModelo::NuevoConceptoPorTiendaModelo($datacontroller);
			if ($answer == 1) {
				echo '1';
			}else{
				echo '0';
			}
		}
	} 

	static public function EliminarConceptoPorTiendaControlador(){
	    $CODIGO = isset($_POST['CODIGO']) ? $_POST['CODIGO'] : 'null';
	    $NOMBRE = isset($_POST['NOMBRE']) ? $_POST['NOMBRE'] : 'null';
	    $datacontroller = array(
	        "0" => $CODIGO,
	        "1" => $NOMBRE,
	    );
	        $answer = ConceptosPorTiendaModelo::EliminarConceptoPorTiendaModelo($datacontroller);
	        if ($answer != 'error') {
	            echo 'okey';
	        }else{
	            echo 'error';
	        }
	} 

	static public function MostrarChxConceptosPorTiendaControlador(){
	    $CODIGO = isset($_POST['CODIGO']) ? $_POST['CODIGO'] : 'null';
	    $NOMBRE = isset($_POST['NOMBRE']) ? $_POST['NOMBRE'] : 'null';
	    $datacontroller = array(
	        "0" => $CODIGO,
	        "1" => $NOMBRE,
	    );
	        $answer = ConceptosPorTiendaModelo::MostrarChxConceptosPorTiendaModelo($datacontroller);
	        $answer = json_encode($answer);
	        echo $answer;
	} 

	static public function GuardarCambiosConceptosPorTiendaControlador(){
	    // SACAMOS LOS DATOS DEL MÉTODO POST
	    $CODIGO = isset($_POST['CODIGO']) ? $_POST['CODIGO'] : 'null';
	    $NOMBRE = isset($_POST['NOMBRE']) ? $_POST['NOMBRE'] : 'null';
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];

	    $datacontroller = array(
	        "0" => $CODIGO,
	        "1" => $NOMBRE,
	    );
	    $id = ConceptosPorTiendaModelo::BuscarIdConceptosPorTiendaModelo($datacontroller);
	    $answer = ConceptosPorTiendaModelo::EliminarCambiosConceptosPorTiendaModelo($id);

	    echo $answer;
	    $sum1 = 0;
	    $sum = 1;
	    if ($answer != 'error') {
	        $sum1 = 0;
	        $sum = 0;
	    // Recorremos el array checkboxValues y ejecutamos la función para cada elemento
	        foreach ($checkboxValues as $value) {
	            $sum ++;

	            // SE GUARDAN EN UN ARRAY
	            $datacontroller = array(
	                "0" => $id,
	                "1" => $value
	            );

	            // Y MANDAMOS A LLAMAR LA FUNCION PARA QUE EJECUTE EL NUEVO AGREGADO DE CHECKBOXS
	            $answer = ConceptosPorTiendaModelo::GuardarCambiosConceptosportienda($datacontroller);
	            if ($answer == 'ok') {
	                $sum1 ++;
	            }
	        }
	    }
	    if ($sum1 == $sum) {
	        echo $sum;
	    }else{
	        echo 'error';
	    }       

	    
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

	case 'NuevoConceptoPorTienda':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosPorTiendaModelo.php';
	ConceptosPorTiendaControlador::NuevoConceptoPorTiendaControlador();
	break;

	case 'AgregarTblConceptosPorTienda':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosPorTiendaModelo.php';
	ConceptosPorTiendaControlador::AgregarTblConceptosPorTiendaControlador();
	break;

	case 'EliminarConceptoPorTienda':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosPorTiendaModelo.php';
	ConceptosPorTiendaControlador::EliminarConceptoPorTiendaControlador();
	break;

	case 'MostrarChxConceptosPorTienda':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosPorTiendaModelo.php';
	ConceptosPorTiendaControlador::MostrarChxConceptosPorTiendaControlador();
	break;

	case 'GuardarCambiosConceptosPorTienda':
	session_start();
	include_once '../configuracion/configuracion.php';
	include_once '../configuracion/conexion.php';
	include_once '../modelo/ConceptosPorTiendaModelo.php';
	ConceptosPorTiendaControlador::GuardarCambiosConceptosPorTiendaControlador();
	break;

	


}
