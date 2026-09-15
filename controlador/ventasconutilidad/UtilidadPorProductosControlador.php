<?php 

//***************************
//      INICIO DE CLASE
//***************************
class UtilidadPorProductosControlador{


	static public function InsertarTblUtilidadPorProductosControlador(){
	     // Obtener los valores del formulario

	    $startproductval = isset($_POST['startproductval']) ? $_POST['startproductval'] : '';
	    $endproductval = isset($_POST['endproductval']) ? $_POST['endproductval'] : '';
	    $startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $agentval = isset($_POST['agentval']) ? $_POST['agentval'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    $chxserv = isset($_POST['chxserv']) ? '1' : '0';
	    $chxpaq = isset($_POST['chxpaq']) ? '1' : '0';
	    $chxno0 = isset($_POST['chxno0']) ? '1' : '0';
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];
	    $cbx_LineGeneral = isset($_POST['cbx_LineGeneral']) ? json_decode($_POST['cbx_LineGeneral']) : [];
	    $cbx_LineDetailed = isset($_POST['cbx_LineDetailed']) ? json_decode($_POST['cbx_LineDetailed']) : [];
	    $cbx_CommissionIndicator = isset($_POST['cbx_CommissionIndicator']) ? json_decode($_POST['cbx_CommissionIndicator']) : [];
	    $cbx_TypeClassification = isset($_POST['cbx_TypeClassification']) ? json_decode($_POST['cbx_TypeClassification']) : [];
	    $cbx_Rotation = isset($_POST['cbx_Rotation']) ? json_decode($_POST['cbx_Rotation']) : [];
	    $cbx_DailyReview = isset($_POST['cbx_DailyReview']) ? json_decode($_POST['cbx_DailyReview']) : [];

	    //SE ACOMODAN LAS FECHAS PARA QUE SE ADAPTEN AL FORMATO QUE VIENE EN LA CONSULTA
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));

	    if ($startproductval == '0') {
	        $startproductval = ConceptosReutilizablesModelo::BuscarMinimoProductoAlphanumericoModelo();
	    }
	    if ($endproductval == '0') {
	        $endproductval = ConceptosReutilizablesModelo::BuscarMaximoProductoAlphanumericoModelo();
	    }
	    $datacontroller = array("0" => $startproductval
	        ,"1"=> $endproductval
	        ,"2"=> $startdate
	        ,"3"=> $endate
	        ,"4"=> $chxserv
	        ,"5"=> $chxpaq
	        ,"6"=> $chxno0
	        ,"7"=> $checkboxValues
	        ,"8"=> $cbx_LineGeneral
	        ,"9"=> $cbx_LineDetailed
	        ,"10"=> $cbx_CommissionIndicator
	        ,"11"=> $cbx_TypeClassification
	        ,"12"=> $cbx_Rotation
	        ,"13"=> $cbx_DailyReview
	        ,"14"=> $agentval
	    );



	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorProductosModelo::InsertarTblUtilidadPorProductosModelo($datacontroller);
	    $num = 0;
	    foreach ($answer as $row) {
	        $num += 1;
	        
	        if ($row['tipo'] == '1') {
	            $utilidad = ($row['Importe Ventas']-$row['Importe Costo']);
	            if ($row['Importe Ventas'] != '0') {
	                $margen = (($utilidad*100)/($row['Importe Ventas']));
	            }else{
	                $margen = '0';
	            }
	            echo "
	            <tr class='btnreportutilityproductonly'  data-toggle='modal' data-target='#mdl_reportutilitydocumentOnly' data-codigo=".$row['Código']."'>
	            <td class='py-0 '>".$num."</td>
	            <td class='py-0 '>".$row['Código']."</td>
	            <td class='py-0 '>".$row['Producto']."</td>
	            <td class='py-0 numbertxt'>".round($row['Unidades'],0)."</td>
	            <td class='py-0 numbertxt'>".number_format(round($row['Importe Ventas'],2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".number_format(round($row['Descuento'],2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".number_format(round($row['Importe Costo'],2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".number_format(round($utilidad,2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".round($margen,2)."</td>
	            </tr>
	            "; 
	        }else{
	            $data=UtilidadPorProductosModelo::BuscarPaqueteUtilidadPorProductoModelo($row['Código'],$startdate,$endate);
	            $utilidad = ($data['Importe Ventas']-$data['Importe Costo']);
	            if ($data['Importe Ventas'] != '0') {
	                $margen = (($utilidad*100)/($data['Importe Ventas']));
	            }else{
	                $margen = '0';
	            }
	            echo "
	            <tr class='btnreportutilityproductOnly' data-toggle='modal' data-target='#mdl_reportutilitydocumentOnly' data-codigo=".$data['Código']."'>
	            <td class='py-0 '>".$num."</td>
	            <td class='py-0 '>".$data['Código']."</td>
	            <td class='py-0 '>".$data['Producto']."</td>
	            <td class='py-0 numbertxt'>".round($data['Unidades'],0)."</td>
	            <td class='py-0 numbertxt'>".number_format(round($data['Importe Ventas'],2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".number_format(round($data['Descuento'],2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".number_format(round($data['Importe Costo'],2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".number_format(round($utilidad,2),2,'.',',')."</td>
	            <td class='py-0 numbertxt'>".round($margen,2)."</td>
	            </tr>
	            ";
	        }  
	        
	        
	    }
	    if ($num == 0) {
	        echo
	        "<td class='py-0' colspan='11'> NO SE ENCONTRARON REGISTROS CON LOS FILTROS INGRESADOS</td>";   
	    }
	} 


	static public function EjecutarSubConsultaUtilidadPorProductosControlador(){
	     // Obtener los valores del formulario
	    $Codigo = isset($_POST['Codigo']) ? $_POST['Codigo'] : '';
	    $startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';

	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));

	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];
	    $datacontroller = array("0" => $Codigo, "1" => $startdate,"2" => $endate,"3" => $checkboxValues);

	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorProductosModelo::EjecutarSubConsultaUtilidadPorProductosModelo($datacontroller);
	    print_r($answer);
	    $num = 0;
	    $porcentaje;
	    foreach ($answer as $row) {
	        $num += 1;
	        $utilidad = ($row['Importe Ventas']-$row['Importe de Costo']);
	        if ($row['Importe Ventas'] != '0') {
	            $margen = (($utilidad*100)/($row['Importe Ventas']));
	        }else{
	            $margen = '0';
	        }
	        echo "
	        <tr>
	        <td class='py-0 '>".$num."</td>
	        <td class='py-0 '>".$row['Serie']."</td>
	        <td class='py-0 '>".round($row['Folio'],0)."</td>
	        <td class='py-0 '>".$row['Fecha']."</td>
	        <td class='py-0 '>".round($row['CCODIGOCLIENTE'],0)."</td>
	        <td class='py-0 '>".$row['Razón Social']."</td>
	        <td class='py-0 '>".$row['CCODIGOAGENTE']."</td>
	        <td class='py-0 '>".$row['Agente']."</td>
	        <td class='py-0 numbertxt'>".$row['Unidades']."</td>
	        <td class='py-0 '>".$row['UM']."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Importe Ventas'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Descuento'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Importe de Costo'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($utilidad,2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".round($margen,2)."</td>
	        </tr>

	        ";
	    }
	    if ($num == 0) {
	        echo
	        "<td class='py-0' colspan='16'> NO SE ENCONTRARON REGISTROS CON LOS FILTROS INGRESADOS</td>";   
	    }
	} 

	static public function GenerarPdfUtilidadPorProductosControlador(){
	     // Obtener los valores del formulario
	    $startproductval = isset($_POST['startproductval']) ? $_POST['startproductval'] : '';
	    $endproductval = isset($_POST['endproductval']) ? $_POST['endproductval'] : '';
	    $startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    $chxserv = isset($_POST['chxserv']) ? '1' : '0';
	    $chxpaq = isset($_POST['chxpaq']) ? '1' : '0';
	    $chxno0 = isset($_POST['chxno0']) ? '1' : '0';
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];
	    $cbx_LineGeneral = isset($_POST['cbx_LineGeneral']) ? json_decode($_POST['cbx_LineGeneral']) : [];
	    $cbx_LineDetailed = isset($_POST['cbx_LineDetailed']) ? json_decode($_POST['cbx_LineDetailed']) : [];
	    $cbx_CommissionIndicator = isset($_POST['cbx_CommissionIndicator']) ? json_decode($_POST['cbx_CommissionIndicator']) : [];
	    $cbx_TypeClassification = isset($_POST['cbx_TypeClassification']) ? json_decode($_POST['cbx_TypeClassification']) : [];
	    $cbx_Rotation = isset($_POST['cbx_Rotation']) ? json_decode($_POST['cbx_Rotation']) : [];
	    $cbx_DailyReview = isset($_POST['cbx_DailyReview']) ? json_decode($_POST['cbx_DailyReview']) : [];
	    $agentval = isset($_POST['agentval']) ? $_POST['agentval'] : '';

	    //SE ACOMODAN LAS FECHAS PARA QUE SE ADAPTEN AL FORMATO QUE VIENE EN LA CONSULTA
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));

	    if ($startproductval == '0') {
	        $startproductval = ConceptosReutilizablesModelo::BuscarMinimoProductoAlphanumericoModelo();
	    }
	    if ($endproductval == '0') {
	        $endproductval = ConceptosReutilizablesModelo::BuscarMaximoProductoAlphanumericoModelo();
	    }
	    $datacontroller = array("0" => $startproductval
	        ,"1"=> $endproductval
	        ,"2"=> $startdate
	        ,"3"=> $endate
	        ,"4"=> $chxserv
	        ,"5"=> $chxpaq
	        ,"6"=> $chxno0
	        ,"7"=> $checkboxValues
	        ,"8"=> $cbx_LineGeneral
	        ,"9"=> $cbx_LineDetailed
	        ,"10"=> $cbx_CommissionIndicator
	        ,"11"=> $cbx_TypeClassification
	        ,"12"=> $cbx_Rotation
	        ,"13"=> $cbx_DailyReview
	        ,"14"=> $agentval
	    );



	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorProductosModelo::InsertarTblUtilidadPorProductosModelo($datacontroller);

	    $num = 0;
	    $endate = date('d/m/Y', strtotime($endate));
	    $startdate = date('d/m/Y', strtotime($startdate));
	    $pdf = new PDF("P", 'mm', "A4");
	    // Definir los campos y sus características para el encabezado de la tabla
	    $headerFields = array();

	    $pdf->setHeaderTitle('Ventas Por Productos del '.$startdate .' hasta ' . $endate);
	    $pdf->setHeaderFields($headerFields);
	    $pdf->SetFont('Arial', '', 8);
	    $pdf->AliasNbPages();
	    $pdf->AddPage();

	    foreach ($answer as $row) {
	       if ($pdf->GetY() + 50 > $pdf->GetPageHeight()) {
	            $pdf->AddPage(); // Agregar una nueva página
	         }

	        $num += 1;
	        $pdf->Cell(20, 5, '#'.$num, 0, 0, 'L');
	        $pdf->Ln();
	        $pdf->Cell(100, 5, 'Codigo: '.utf8_decode($row['Código']), 0, 0, 'L');
	        $pdf->Ln();
	        $pdf->Cell(100, 5, 'Producto: '.utf8_decode($row['Producto']), 0, 0, 'L');
	        $pdf->Ln();

	        // Contenido de la fila de celdas
	        $pdf->Cell(6, 5, '');
	        $pdf->Cell(20, 5, 'Unidades', 0, 0, 'R');
	        $pdf->Cell(25, 5, 'Neto', 0, 0, 'R');
	        $pdf->Cell(25, 5, 'Descuento', 0, 0, 'R');
	        $pdf->Cell(25, 5, 'Costo', 0, 0, 'R');
	        $pdf->Cell(25, 5, 'Utilidad', 0, 0, 'R');
	        $pdf->Cell(20, 5, 'Margen', 0, 0, 'R');
	        $pdf->Ln();

	        // Dibujar línea debajo de la fila de celdas
	        $pdf->SetLineWidth(0.2);
	        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
	        $pdf->SetLineWidth(0.4);

	        if ($row['tipo'] == '1') {
	            $utilidad = ($row['Importe Ventas']-$row['Importe Costo']);
	            if ($row['Importe Ventas'] != '0') {
	                $margen = (($utilidad*100)/($row['Importe Ventas']));
	            }else{
	                $margen = '0';
	            }

	            $pdf->Cell(6, 5, 'Total');
	            $pdf->Cell(20, 5, number_format(round($row['Unidades'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($row['Importe Ventas'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($row['Descuento'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($row['Importe Costo'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($utilidad,2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(20, 5, '%'.number_format(round($margen,2),2,'.',','), 0, 0, 'R');

	        }else{
	            $data=UtilidadPorProductosModelo::BuscarPaqueteUtilidadPorProductoModelo($row['Código'],$startdate,$endate);
	            $utilidad = ($data['Importe Ventas']-$data['Importe Costo']);
	            if ($data['Importe Ventas'] != '0') {
	                $margen = (($utilidad*100)/($data['Importe Ventas']));
	            }else{
	                $margen = '0';
	            }
	            $pdf->Cell(6, 5, 'Total');
	            $pdf->Cell(20, 5, number_format(round($row['Unidades'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($row['Importe Ventas'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($row['Descuento'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($row['Importe Costo'],2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(25, 5, '$'.number_format(round($utilidad,2),2,'.',','), 0, 0, 'R');
	            $pdf->Cell(20, 5, '%'.number_format(round($margen,2),2,'.',','), 0, 0, 'R');
	        }    
	        $pdf->Ln();
	        $pdf->Ln();
	        $pdf->Ln();

	    }


	    // Guardar el PDF en un archivo temporal
	    $temp_file = 'Reporte.pdf'; // Directorio temporal para guardar los PDFs
	    $ruta = "controlador/Reporte.pdf"; // Directorio temporal para guardar los PDFs
	    $pdf->Output($temp_file, 'F');


	    // Enviar la ruta del archivo al cliente
	    echo ($ruta);
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

	case 'InsertarTblUtilidadPorProductos':
	session_start();
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorProductosModelo.php';
	include_once '../../modelo/ConceptosReutilizablesModelo.php';
	UtilidadPorProductosControlador::InsertarTblUtilidadPorProductosControlador();
	break;

	case 'EjecutarSubConsultaUtilidadPorProductos':
	session_start();
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorProductosModelo.php';
	include_once '../../modelo/ConceptosReutilizablesModelo.php';
	UtilidadPorProductosControlador::EjecutarSubConsultaUtilidadPorProductosControlador();
	break;

	case 'GenerarPdfUtilidadPorProductos':
	session_start();
	include_once '../fpdf.php';
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorProductosModelo.php';
	include_once '../../modelo/ConceptosReutilizablesModelo.php';
	UtilidadPorProductosControlador::GenerarPdfUtilidadPorProductosControlador();
	break;



	


}
