<?php
require_once __DIR__.'/UtilidadRespuesta.php';

//***************************
//      INICIO DE CLASE
//***************************
class UtilidadPorDocumentosControlador{


	static public function InsertarTblUtilidadPorDocumentosControlador(){
	     // Obtener los valores del formulario
	    $client = isset($_POST['client']) ? $_POST['client'] : '';
	    $startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    //$cbx_document = isset($_POST['cbx_document']) ? $_POST['cbx_document'] : '';
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];

	    //SE ACOMODAN LAS FECHAS PARA QUE SE ADAPTEN AL FORMATO QUE VIENE EN LA CONSULTA
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));

	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorDocumentosModelo::InsertarTblUtilidadPorDocumentosModelo($client, $startdate, $endate, $checkboxValues);
        ob_start();
		
	    $num = 0;
	    foreach ($answer as $row) {
	        $num += 1;
	        echo "
	        <tr class='btnreportutilitydocumentonly' data-toggle='modal' data-target='#mdl_reportutilitydocumentOnly' data-serie='".$row['Serie']."' data-folio='".round($row['Folio'])."'>
	        <td class='py-0 '>".$num."</td>
	        <td class='py-0 '>".$row['Serie']."</td>
	        <td class='py-0 '>".round($row['Folio'])."</td>
	        <td class='py-0 '>".$row['Fecha']."</td>
	        <td class='py-0 '>".$row['ID_Cliente']."</td>
	        <td class='py-0 w-25'>".$row['Razón Social']."</td>
	        <td class='py-0 '>".$row['ID_Agente']."</td>
	        <td class='py-0 w-25'>".$row['Agente'] ."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Importe Ventas'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt '>".number_format(round($row['Descuento'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Importe de Costo'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['utilidad'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".round($row['porcentajeutilidad'],2)."</td>
	        </tr>
	        ";
	    }
        UtilidadRespuesta::EnviarTabla($answer, 'Importe Ventas', 'Importe de Costo');
    }

static public function InsertarTblSubConsultaUtilidadPorDocumentosControlador(){
	     // Obtener los valores del formulario
	    $folio = isset($_POST['folio']) ? $_POST['folio'] : '';
	    $serie = isset($_POST['serie']) ? $_POST['serie'] : '';

	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorDocumentosModelo::InsertarTblSubConsultaUtilidadPorDocumentosModelo($folio, $serie);
        ob_start();
	    $num = 0;
	    $porcentaje = 0;	
	    foreach ($answer as $row) {
	        $num += 1;
	        $porcentaje = $row['Importe Ventas'] == 0 ? 0 : ($row['UTILIDAD']*100)/$row['Importe Ventas'];
	        echo "
	        <tr>
	        <td class='py-0 '>".$num."</td>
	        <td class='py-0 '>".$row['Codigo']."</td>
	        <td class='py-0 w-25'>".$row['Producto']."</td>
	        <td class='py-0 '>".round($row['Unidades'],2)."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Importe Ventas'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt '>".number_format(round($row['Descuento'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Importe Coste'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['UTILIDAD'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".round($porcentaje,2)."%</td>
	        </tr>
	        ";


	    }
        UtilidadRespuesta::EnviarTabla($answer, 'Importe Ventas', 'Importe Coste');
    }

static public function GenerarPdfUtilidadPorDocumentosControlador(){
	 // Obtener los valores del formulario
	    $client = isset($_POST['client']) ? $_POST['client'] : '';
	    $startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    //$cbx_document = isset($_POST['cbx_document']) ? $_POST['cbx_document'] : '';
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];

	    //SE ACOMODAN LAS FECHAS PARA QUE SE ADAPTEN AL FORMATO QUE VIENE EN LA CONSULTA
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));

	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorDocumentosModelo::InsertarTblUtilidadPorDocumentosModelo($client, $startdate, $endate, $checkboxValues);
	    $num = 0;

	    $endate = date('d/m/Y', strtotime($endate));
	    $startdate = date('d/m/Y', strtotime($startdate));
	    $pdf = new PDF();
	    // Definir los campos y sus características para el encabezado de la tabla
	    $headerFields = array();

	    $pdf->setHeaderTitle('Documentos del '.$startdate .' hasta ' . $endate);
	    $pdf->setHeaderFields($headerFields);
	    $pdf->SetFont('Arial', '', 8);
	    $pdf->AliasNbPages();
	    $pdf->AddPage();
	    foreach ($answer as $row) {
	        if ($pdf->GetY() + 50 > $pdf->GetPageHeight()) {
	            $pdf->AddPage(); // Agregar una nueva página
	         }
	        if ($row['Importe Ventas'] != '0') {
	            $margen = (($row['utilidad']*100)/($row['Importe Ventas']));
	        }else{
	            $margen = '0';
	        }
	        $num += 1;
	        $pdf->Cell(20, 5, '#'.$num, 0, 0, 'L');
	        $pdf->Cell(20, 5, 'Serie: '.$row['Serie'], 0, 0, 'L');
	        $pdf->Cell(20, 5, 'Folio: '.round($row['Folio'],0), 0, 0, 'L');
	        $pdf->Cell(20, 5, utf8_decode('Fecha: '.$row['Fecha']), 0, 0, 'L');
	        $pdf->Ln();
	        $pdf->Cell(100, 5, utf8_decode('Cliente : '.$row['Razón Social']), 0, 0, 'L');
	        $pdf->Ln();
	        $pdf->Cell(100, 5, utf8_decode('Agente: '.$row['Agente']), 0, 0, 'L');
	        $pdf->Ln();

	        // Contenido de la fila de celdas
	        $pdf->Cell(10, 5, '');
	        $pdf->Cell(35, 5, 'Neto', 0, 0, 'R');
	        $pdf->Cell(35, 5, 'Descuento', 0, 0, 'R');
	        $pdf->Cell(35, 5, 'Costo', 0, 0, 'R');
	        $pdf->Cell(35, 5, 'Utilidad', 0, 0, 'R');
	        $pdf->Cell(35, 5, 'Margen', 0, 0, 'R');
	        $pdf->Ln();

	        // Dibujar línea debajo de la fila de celdas
	        $pdf->SetLineWidth(0.2);
	        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
	        $pdf->SetLineWidth(0.4);

	        $pdf->Cell(10, 5, 'Total');
	        $pdf->Cell(35, 5, '$'.number_format(round($row['Importe Ventas'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(35, 5, '$'.number_format(round($row['Descuento'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(35, 5, '$'.number_format(round($row['Importe de Costo'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(35, 5, '$'.number_format(round($row['utilidad'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(35, 5, '%'.number_format(round($margen,2),2,'.',','), 0, 0, 'R');
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

	case 'InsertarTblUtilidadPorDocumentos':
	session_start();
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorDocumentosModelo.php';
	UtilidadPorDocumentosControlador::InsertarTblUtilidadPorDocumentosControlador();
	break;

	case 'InsertarTblSubConsultaUtilidadPorDocumentos':
	session_start();
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorDocumentosModelo.php';
	UtilidadPorDocumentosControlador::InsertarTblSubConsultaUtilidadPorDocumentosControlador();
	break;

	case 'GenerarPdfUtilidadPorDocumentos':
	session_start();
	include_once '../fpdf.php';
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorDocumentosModelo.php';
	UtilidadPorDocumentosControlador::GenerarPdfUtilidadPorDocumentosControlador();
	break;



	


}
