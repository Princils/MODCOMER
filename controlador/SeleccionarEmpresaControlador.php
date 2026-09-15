<?php

//***************************
//		INICIO DE CLASE
//***************************
class SeleccionarEmpresaControlador extends Conexion{

	static public function AgregearCBXCompanyControlador(){
	    $data = SeleccionarEmpresaModelo::BusacarCompanysModelo();
	    foreach ($data as $row) {
	        echo '<option value="'.$row['CRUTADATOS'].'">'.$row['CNOMBREEMPRESA'].'</option>';
	    }
	} 


	static public function IniciarCompanyControlador(){
	    if (isset($_POST['btn_seleccionarempresa'])) {

			$ruta = $_POST['company'];

	        $_SESSION['database'] = basename($ruta);


            $datacontroller = array("0" => txtserver,"1" => $_SESSION['database'] ,"2"=>txtuser,"3"=>txtpassword);

	        $tablas =InicializadorTablasModelo::VerificarTablasBdDinamicaModelo($datacontroller);
			 	if ($tablas == true) {
				     ?>
				        <script type="text/javascript">
				         window.location.href = "index.php?vista=Dashboard";   
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




}
