<?php 

//***************************
//      INICIO DE CLASE
//***************************
class UtilidadPorAgenteControlador{


	static public function InsertarTblUtilidadPorAgentesControlador(){
	     //************************INP DE AGENTES + LA CONVERSION EN CASO DE SER 0**********************************************
	    $startagentval = isset($_POST['startagentval']) ? $_POST['startagentval'] : '';
	    $endagentval = isset($_POST['endagentval']) ? $_POST['endagentval'] : '';

	    if ($startagentval == '0') {
	        $startagentval = ConceptosReutilizablesModelo::BuscarMinimoAgenteAlphanumericoModelo();
	    }
	    if ($endagentval == '0') {
	        $endagentval = ConceptosReutilizablesModelo::BuscarMaximoAgenteAlphanumericoModelo();
	    }

	    /*************************CBX PARA CONCEPTOS***********************************************************/
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];

	    /*************************CBX PARA PRODUCTOS***********************************************************/
		$cbx_LineGeneral = isset($_POST['cbx_LineGeneral']) ? json_decode($_POST['cbx_LineGeneral']) : [];
	    $cbx_LineDetailed = isset($_POST['cbx_LineDetailed']) ? json_decode($_POST['cbx_LineDetailed']) : [];
	    $cbx_CommissionIndicator = isset($_POST['cbx_CommissionIndicator']) ? json_decode($_POST['cbx_CommissionIndicator']) : [];
	    $cbx_TypeClassification = isset($_POST['cbx_TypeClassification']) ? json_decode($_POST['cbx_TypeClassification']) : [];
	    $cbx_Rotation = isset($_POST['cbx_Rotation']) ? json_decode($_POST['cbx_Rotation']) : [];
	    $cbx_DailyReview = isset($_POST['cbx_DailyReview']) ? json_decode($_POST['cbx_DailyReview']) : [];

	    /*************************CBX PARA AGENTES***********************************************************/
		$cbxAgent1 = isset($_POST['cbxAgent1']) ? json_decode($_POST['cbxAgent1']) : [];
	    $agentClasification2 = isset($_POST['agentClasification2']) ? json_decode($_POST['agentClasification2']) : [];
	    $agentClasification3 = isset($_POST['agentClasification3']) ? json_decode($_POST['agentClasification3']) : [];
	    $agentClasification4 = isset($_POST['agentClasification4']) ? json_decode($_POST['agentClasification4']) : [];
	    $agentClasification5 = isset($_POST['agentClasification5']) ? json_decode($_POST['agentClasification5']) : [];
	    $agentClasification6 = isset($_POST['agentClasification6']) ? json_decode($_POST['agentClasification6']) : [];

	    /*************************CBX PARA CLIENTES***********************************************************/
		$cbx_clas1client = isset($_POST['cbx_clas1client']) ? json_decode($_POST['cbx_clas1client']) : [];
	    $cbx_typeclient = isset($_POST['cbx_typeclient']) ? json_decode($_POST['cbx_typeclient']) : [];
	    $cbx_agent = isset($_POST['cbx_agent']) ? json_decode($_POST['cbx_agent']) : [];
	    $cbx_zone = isset($_POST['cbx_zone']) ? json_decode($_POST['cbx_zone']) : [];
	    $cbx_clas5client = isset($_POST['cbx_clas5client']) ? json_decode($_POST['cbx_clas5client']) : [];
	    $cbx_clas6client = isset($_POST['cbx_clas6client']) ? json_decode($_POST['cbx_clas6client']) : [];

	    //**************************************************INP PARA LAS FECHAS CON SU CONVERSION*********************************************
	   	$startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));


	    $datacontroller = array(
	    	 "0"=> $checkboxValues
	        ,"1"=> $startagentval
	        ,"2"=> $endagentval
	        ,"3"=> $cbx_LineGeneral
	        ,"4"=> $cbx_LineDetailed
	        ,"5"=> $cbx_CommissionIndicator
	        ,"6"=> $cbx_TypeClassification
	        ,"7"=> $cbx_Rotation
	        ,"8"=> $cbx_DailyReview
	        ,"9"=> $cbxAgent1
	        ,"10"=> $agentClasification2
	        ,"11"=> $agentClasification3
	        ,"12"=> $agentClasification4
	        ,"13"=> $agentClasification5
	        ,"14"=> $agentClasification6
	        ,"15"=> $cbx_clas1client
	        ,"16"=> $cbx_typeclient
	        ,"17"=> $cbx_agent
	        ,"18"=> $cbx_zone
	        ,"19"=> $cbx_clas5client
	        ,"20"=> $cbx_clas6client
	        ,"21"=> $startdate
	        ,"22"=> $endate
	    );


	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorAgenteModelo::InsertarTblUtilidadPorAgenteModelo($datacontroller);

	    $num = 0;

	    foreach ($answer as $row) {
	        $num += 1;
			if ($row['Neto'] != '0') {
			    // Primero se realiza la operación de división y luego se redondea el resultado si es necesario.
			    $margen = round(($row['Utilidad'] / $row['Neto']) * 100, 2);
			} else {
			    $margen = '0';
			}


	        echo "
	        <tr class='asigdatabtn' data-toggle='modal' data-target='#mdl_select' data-id='".$row['IDAGENTE']."' data-codigo='".$row['CODIGOAGENTE']."'>
	        <td class='py-0 '>".$num."</td>
	        <td class='py-0 '>".$row['CODIGOAGENTE']."</td>
	        <td class='py-0 '>".$row['NOMBREAGENTE']."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Neto'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt '>".number_format(round($row['Descuento'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Costo'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Utilidad'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".round($margen,2)."</td>
	        </tr>
	        ";
	    }
	} 

	
	static public function InsertarTblUtilidadPorAgenteSoloProductosControlador(){
	     //************************INP DE AGENTES + LA CONVERSION EN CASO DE SER 0**********************************************
	    $startagentval = isset($_POST['startagentval']) ? $_POST['startagentval'] : '0';
	    $endagentval = isset($_POST['endagentval']) ? $_POST['endagentval'] : '0';
	    $Codigo = isset($_POST['Codigo']) ? $_POST['Codigo'] : '';


	    /*************************CBX PARA CONCEPTOS***********************************************************/
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];

	    /*************************CBX PARA PRODUCTOS***********************************************************/
		$cbx_LineGeneral = isset($_POST['cbx_LineGeneral']) ? json_decode($_POST['cbx_LineGeneral']) : [];
	    $cbx_LineDetailed = isset($_POST['cbx_LineDetailed']) ? json_decode($_POST['cbx_LineDetailed']) : [];
	    $cbx_CommissionIndicator = isset($_POST['cbx_CommissionIndicator']) ? json_decode($_POST['cbx_CommissionIndicator']) : [];
	    $cbx_TypeClassification = isset($_POST['cbx_TypeClassification']) ? json_decode($_POST['cbx_TypeClassification']) : [];
	    $cbx_Rotation = isset($_POST['cbx_Rotation']) ? json_decode($_POST['cbx_Rotation']) : [];
	    $cbx_DailyReview = isset($_POST['cbx_DailyReview']) ? json_decode($_POST['cbx_DailyReview']) : [];

	    /*************************CBX PARA AGENTES***********************************************************/
		$cbxAgent1 = isset($_POST['cbxAgent1']) ? json_decode($_POST['cbxAgent1']) : [];
	    $agentClasification2 = isset($_POST['agentClasification2']) ? json_decode($_POST['agentClasification2']) : [];
	    $agentClasification3 = isset($_POST['agentClasification3']) ? json_decode($_POST['agentClasification3']) : [];
	    $agentClasification4 = isset($_POST['agentClasification4']) ? json_decode($_POST['agentClasification4']) : [];
	    $agentClasification5 = isset($_POST['agentClasification5']) ? json_decode($_POST['agentClasification5']) : [];
	    $agentClasification6 = isset($_POST['agentClasification6']) ? json_decode($_POST['agentClasification6']) : [];

	    /*************************CBX PARA CLIENTES***********************************************************/
		$cbx_clas1client = isset($_POST['cbx_clas1client']) ? json_decode($_POST['cbx_clas1client']) : [];
	    $cbx_typeclient = isset($_POST['cbx_typeclient']) ? json_decode($_POST['cbx_typeclient']) : [];
	    $cbx_agent = isset($_POST['cbx_agent']) ? json_decode($_POST['cbx_agent']) : [];
	    $cbx_zone = isset($_POST['cbx_zone']) ? json_decode($_POST['cbx_zone']) : [];
	    $cbx_clas5client = isset($_POST['cbx_clas5client']) ? json_decode($_POST['cbx_clas5client']) : [];
	    $cbx_clas6client = isset($_POST['cbx_clas6client']) ? json_decode($_POST['cbx_clas6client']) : [];

	    //**************************************************INP PARA LAS FECHAS CON SU CONVERSION*********************************************
	   	$startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));


	    $datacontroller = array(
	    	 "0"=> $checkboxValues
	        ,"1"=> $startagentval
	        ,"2"=> $endagentval
	        ,"3"=> $cbx_LineGeneral
	        ,"4"=> $cbx_LineDetailed
	        ,"5"=> $cbx_CommissionIndicator
	        ,"6"=> $cbx_TypeClassification
	        ,"7"=> $cbx_Rotation
	        ,"8"=> $cbx_DailyReview
	        ,"9"=> $cbxAgent1
	        ,"10"=> $agentClasification2
	        ,"11"=> $agentClasification3
	        ,"12"=> $agentClasification4
	        ,"13"=> $agentClasification5
	        ,"14"=> $agentClasification6
	        ,"15"=> $cbx_clas1client
	        ,"16"=> $cbx_typeclient
	        ,"17"=> $cbx_agent
	        ,"18"=> $cbx_zone
	        ,"19"=> $cbx_clas5client
	        ,"20"=> $cbx_clas6client
	        ,"21"=> $startdate
	        ,"22"=> $endate
	        ,"23"=> $Codigo
	    );

	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorAgenteModelo::InsertarTblUtilidadPorAgenteSoloProductosModelo($datacontroller);

	    $num = 0;
	    $porcentaje;	
	    foreach ($answer as $row) {
	        $num += 1;
	        $utilidad = ($row['Neto']-$row['Costo']);
	        if ($row['Neto'] != '0') {
	            $margen = (($utilidad*100)/($row['Neto']));
	        }else{
	            $margen = '0';
	        }
	        echo "
	        <tr>
	        <td class='py-0 '>".$num."</td>
	        <td class='py-0 '>".$row['Código']."</td>
	        <td class='py-0 w-25'>".$row['Producto']."</td>
	        <td class='py-0 numbertxt'>".round($row['Unidades'],0)."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Neto'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Descuento'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($row['Costo'],2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($utilidad,2),2,'.',',')."</td>
	        <td class='py-0 numbertxt'>".number_format(round($margen,4),2,'.',',')."</td>
	        </tr>

	        ";
	    }
	} 


	static public function InsertarTblUtilidadPorAgentesSoloDocumentosControlador(){
	     //************************INP DE AGENTES + LA CONVERSION EN CASO DE SER 0**********************************************
	    $startagentval = isset($_POST['startagentval']) ? $_POST['startagentval'] : '0';
	    $endagentval = isset($_POST['endagentval']) ? $_POST['endagentval'] : '0';
	    $Codigo = isset($_POST['Codigo']) ? $_POST['Codigo'] : '';


	    /*************************CBX PARA CONCEPTOS***********************************************************/
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];

	    /*************************CBX PARA PRODUCTOS***********************************************************/
		$cbx_LineGeneral = isset($_POST['cbx_LineGeneral']) ? json_decode($_POST['cbx_LineGeneral']) : [];
	    $cbx_LineDetailed = isset($_POST['cbx_LineDetailed']) ? json_decode($_POST['cbx_LineDetailed']) : [];
	    $cbx_CommissionIndicator = isset($_POST['cbx_CommissionIndicator']) ? json_decode($_POST['cbx_CommissionIndicator']) : [];
	    $cbx_TypeClassification = isset($_POST['cbx_TypeClassification']) ? json_decode($_POST['cbx_TypeClassification']) : [];
	    $cbx_Rotation = isset($_POST['cbx_Rotation']) ? json_decode($_POST['cbx_Rotation']) : [];
	    $cbx_DailyReview = isset($_POST['cbx_DailyReview']) ? json_decode($_POST['cbx_DailyReview']) : [];

	    /*************************CBX PARA AGENTES***********************************************************/
		$cbxAgent1 = isset($_POST['cbxAgent1']) ? json_decode($_POST['cbxAgent1']) : [];
	    $agentClasification2 = isset($_POST['agentClasification2']) ? json_decode($_POST['agentClasification2']) : [];
	    $agentClasification3 = isset($_POST['agentClasification3']) ? json_decode($_POST['agentClasification3']) : [];
	    $agentClasification4 = isset($_POST['agentClasification4']) ? json_decode($_POST['agentClasification4']) : [];
	    $agentClasification5 = isset($_POST['agentClasification5']) ? json_decode($_POST['agentClasification5']) : [];
	    $agentClasification6 = isset($_POST['agentClasification6']) ? json_decode($_POST['agentClasification6']) : [];

	    /*************************CBX PARA CLIENTES***********************************************************/
		$cbx_clas1client = isset($_POST['cbx_clas1client']) ? json_decode($_POST['cbx_clas1client']) : [];
	    $cbx_typeclient = isset($_POST['cbx_typeclient']) ? json_decode($_POST['cbx_typeclient']) : [];
	    $cbx_agent = isset($_POST['cbx_agent']) ? json_decode($_POST['cbx_agent']) : [];
	    $cbx_zone = isset($_POST['cbx_zone']) ? json_decode($_POST['cbx_zone']) : [];
	    $cbx_clas5client = isset($_POST['cbx_clas5client']) ? json_decode($_POST['cbx_clas5client']) : [];
	    $cbx_clas6client = isset($_POST['cbx_clas6client']) ? json_decode($_POST['cbx_clas6client']) : [];

	    //**************************************************INP PARA LAS FECHAS CON SU CONVERSION*********************************************
	   	$startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));


	    $datacontroller = array(
	    	 "0"=> $checkboxValues
	        ,"1"=> $startagentval
	        ,"2"=> $endagentval
	        ,"3"=> $cbx_LineGeneral
	        ,"4"=> $cbx_LineDetailed
	        ,"5"=> $cbx_CommissionIndicator
	        ,"6"=> $cbx_TypeClassification
	        ,"7"=> $cbx_Rotation
	        ,"8"=> $cbx_DailyReview
	        ,"9"=> $cbxAgent1
	        ,"10"=> $agentClasification2
	        ,"11"=> $agentClasification3
	        ,"12"=> $agentClasification4
	        ,"13"=> $agentClasification5
	        ,"14"=> $agentClasification6
	        ,"15"=> $cbx_clas1client
	        ,"16"=> $cbx_typeclient
	        ,"17"=> $cbx_agent
	        ,"18"=> $cbx_zone
	        ,"19"=> $cbx_clas5client
	        ,"20"=> $cbx_clas6client
	        ,"21"=> $startdate
	        ,"22"=> $endate
	        ,"23"=> $Codigo
	    );

	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorAgenteModelo::InsertarTblUtilidadPorAgenteSoloDocumentosModelo($datacontroller);

	    $num = 0;
	    $porcentaje;	
	    foreach ($answer as $row) {
	        $num += 1;
	        $utilidad = ($row['Neto']-$row['Costo']);
	        if ($row['Neto'] != '0') {
	            $margen = (($utilidad*100)/($row['Neto']));
	        }else{
	            $margen = '0';
	        }
	        echo "
		        <tr>
		        <td class='py-0 '>".$num."</td>
		        <td class='py-0 '>".$row['CSERIEDOCUMENTO']."</td>
		        <td class='py-0 '>".round($row['CFOLIO'],0)."</td>
		        <td class='py-0 w-25'>".$row['CNOMBREAGENTE']."</td>
		        <td class='py-0 w-25'>".$row['CRAZONSOCIAL']."</td>
		        <td class='py-0 numbertxt'>".number_format(round($row['Neto'],2),2,'.',',')."</td>
		        <td class='py-0 numbertxt'>".number_format(round($row['Descuento'],2),2,'.',',')."</td>
		        <td class='py-0 numbertxt'>".number_format(round($row['Costo'],2),2,'.',',')."</td>
		        <td class='py-0 numbertxt'>".number_format(round($utilidad,2),2,'.',',')."</td>
		        <td class='py-0 numbertxt'>".number_format(round($margen,4),2,'.',',')."</td>
		        </tr>
	        ";


	    }
	    if ($num == 0) {
	        echo
	        "<td class='py-0' colspan='11'> NO SE ENCONTRARON REGISTROS CON LOS FILTROS INGRESADOS</td>";   
	    }
	} 


	static public function GenerarPdfUtilidadPorAgentesControlador(){
	     //************************INP DE AGENTES + LA CONVERSION EN CASO DE SER 0**********************************************
	    $startagentval = isset($_POST['startagentval']) ? $_POST['startagentval'] : '';
	    $endagentval = isset($_POST['endagentval']) ? $_POST['endagentval'] : '';

	    if ($startagentval == '0') {
	        $startagentval = ConceptosReutilizablesModelo::BuscarMinimoAgenteAlphanumericoModelo();
	    }
	    if ($endagentval == '0') {
	        $endagentval = ConceptosReutilizablesModelo::BuscarMaximoAgenteAlphanumericoModelo();
	    }

	    /*************************CBX PARA CONCEPTOS***********************************************************/
	    $checkboxValues = isset($_POST['checkboxValues']) ? json_decode($_POST['checkboxValues']) : [];

	    /*************************CBX PARA PRODUCTOS***********************************************************/
		$cbx_LineGeneral = isset($_POST['cbx_LineGeneral']) ? json_decode($_POST['cbx_LineGeneral']) : [];
	    $cbx_LineDetailed = isset($_POST['cbx_LineDetailed']) ? json_decode($_POST['cbx_LineDetailed']) : [];
	    $cbx_CommissionIndicator = isset($_POST['cbx_CommissionIndicator']) ? json_decode($_POST['cbx_CommissionIndicator']) : [];
	    $cbx_TypeClassification = isset($_POST['cbx_TypeClassification']) ? json_decode($_POST['cbx_TypeClassification']) : [];
	    $cbx_Rotation = isset($_POST['cbx_Rotation']) ? json_decode($_POST['cbx_Rotation']) : [];
	    $cbx_DailyReview = isset($_POST['cbx_DailyReview']) ? json_decode($_POST['cbx_DailyReview']) : [];

	    /*************************CBX PARA AGENTES***********************************************************/
		$cbxAgent1 = isset($_POST['cbxAgent1']) ? json_decode($_POST['cbxAgent1']) : [];
	    $agentClasification2 = isset($_POST['agentClasification2']) ? json_decode($_POST['agentClasification2']) : [];
	    $agentClasification3 = isset($_POST['agentClasification3']) ? json_decode($_POST['agentClasification3']) : [];
	    $agentClasification4 = isset($_POST['agentClasification4']) ? json_decode($_POST['agentClasification4']) : [];
	    $agentClasification5 = isset($_POST['agentClasification5']) ? json_decode($_POST['agentClasification5']) : [];
	    $agentClasification6 = isset($_POST['agentClasification6']) ? json_decode($_POST['agentClasification6']) : [];

	    /*************************CBX PARA CLIENTES***********************************************************/
		$cbx_clas1client = isset($_POST['cbx_clas1client']) ? json_decode($_POST['cbx_clas1client']) : [];
	    $cbx_typeclient = isset($_POST['cbx_typeclient']) ? json_decode($_POST['cbx_typeclient']) : [];
	    $cbx_agent = isset($_POST['cbx_agent']) ? json_decode($_POST['cbx_agent']) : [];
	    $cbx_zone = isset($_POST['cbx_zone']) ? json_decode($_POST['cbx_zone']) : [];
	    $cbx_clas5client = isset($_POST['cbx_clas5client']) ? json_decode($_POST['cbx_clas5client']) : [];
	    $cbx_clas6client = isset($_POST['cbx_clas6client']) ? json_decode($_POST['cbx_clas6client']) : [];

	    //**************************************************INP PARA LAS FECHAS CON SU CONVERSION*********************************************
	   	$startdate = isset($_POST['startdate']) ? $_POST['startdate'] : '';
	    $endate = isset($_POST['endate']) ? $_POST['endate'] : '';
	    $endate = date('d-m-Y', strtotime($endate));
	    $startdate = date('d-m-Y', strtotime($startdate));


	    $datacontroller = array(
	    	 "0"=> $checkboxValues
	        ,"1"=> $startagentval
	        ,"2"=> $endagentval
	        ,"3"=> $cbx_LineGeneral
	        ,"4"=> $cbx_LineDetailed
	        ,"5"=> $cbx_CommissionIndicator
	        ,"6"=> $cbx_TypeClassification
	        ,"7"=> $cbx_Rotation
	        ,"8"=> $cbx_DailyReview
	        ,"9"=> $cbxAgent1
	        ,"10"=> $agentClasification2
	        ,"11"=> $agentClasification3
	        ,"12"=> $agentClasification4
	        ,"13"=> $agentClasification5
	        ,"14"=> $agentClasification6
	        ,"15"=> $cbx_clas1client
	        ,"16"=> $cbx_typeclient
	        ,"17"=> $cbx_agent
	        ,"18"=> $cbx_zone
	        ,"19"=> $cbx_clas5client
	        ,"20"=> $cbx_clas6client
	        ,"21"=> $startdate
	        ,"22"=> $endate
	    );


	    // LLAMA LA FUNCION DEL MODELO QUE DEVUELVE LA LOS VALORES DE LA CONSULTA QUE REALIZA EL REPORTE
	    $answer = UtilidadPorAgenteModelo::InsertarTblUtilidadPorAgenteModelo($datacontroller);

	    $num = 0;

	    $endate = date('d/m/Y', strtotime($endate));
	    $startdate = date('d/m/Y', strtotime($startdate));
	    $pdf = new PDF('L');
	    // Definir los campos y sus características para el encabezado de la tabla
	    $headerFields = array();

	    $pdf->setHeaderTitle('Ventas de agentes del '.$startdate .' hasta ' . $endate);
	    $pdf->setHeaderFields($headerFields);
	    $pdf->SetFont('Arial', '', 8);
	    $pdf->AliasNbPages();
	    $pdf->AddPage();
	    foreach ($answer as $row) {
	        $num += 1;

	        if ($pdf->GetY() + 50 > $pdf->GetPageHeight()) {
	            $pdf->AddPage(); // Agregar una nueva página
		        // Contenido de la fila de celdas
		        $pdf->Cell(10, 5, '#', 0, 0, 'L');
		        $pdf->Cell(50, 5, utf8_decode('Codigó'), 0, 0, 'L');
		        $pdf->Cell(60, 5, 'Agente', 0, 0, 'L');
		        $pdf->Cell(35, 5, 'Neto($)', 0, 0, 'R');
		        $pdf->Cell(35, 5, 'Descuento($)', 0, 0, 'R');
		        $pdf->Cell(35, 5, 'Costo($)', 0, 0, 'R');
		        $pdf->Cell(35, 5, 'Utilidad($)', 0, 0, 'R');
		        $pdf->Cell(20, 5, '%', 0, 0, 'R');
		        $pdf->Ln();

	         }
			
			if ($num == 1) {
		        $pdf->Cell(10, 5, '#', 0, 0, 'L');
		        $pdf->Cell(50, 5, utf8_decode('Codigó'), 0, 0, 'L');
		        $pdf->Cell(60, 5, 'Agente', 0, 0, 'L');
		        $pdf->Cell(35, 5, 'Neto($)', 0, 0, 'R');
		        $pdf->Cell(35, 5, 'Descuento($)', 0, 0, 'R');
		        $pdf->Cell(35, 5, 'Costo($)', 0, 0, 'R');
		        $pdf->Cell(35, 5, 'Utilidad($)', 0, 0, 'R');
		        $pdf->Cell(20, 5, '%', 0, 0, 'R');
		        $pdf->Ln();

			}


		        // Dibujar línea debajo de la fila de celdas
		        $pdf->SetLineWidth(0.2);
		        $pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 280, $pdf->GetY());
		        $pdf->SetLineWidth(0.4);

			if ($row['Neto'] != '0') {
			    // Primero se realiza la operación de división y luego se redondea el resultado si es necesario.
			    $margen = round(($row['Utilidad'] / $row['Neto']) * 100, 2);
			} else {
			    $margen = '0';
			}



	        $pdf->Cell(10, 5, $num, 0, 0, 'L');
	        $pdf->Cell(50, 5, utf8_decode(ConceptosReutilizablesModelo::CortarText($row['CODIGOAGENTE'], 35)), 0, 0, 'L');
	        $pdf->Cell(60, 5, utf8_decode(ConceptosReutilizablesModelo::CortarText($row['NOMBREAGENTE'], 40)), 0, 0, 'L');
	        $pdf->Cell(35, 5, number_format(round($row['Neto'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(35, 5, number_format(round($row['Descuento'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(35, 5, number_format(round($row['Costo'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(35, 5, number_format(round($row['Utilidad'],2),2,'.',','), 0, 0, 'R');
	        $pdf->Cell(20, 5, number_format(round($margen,2),2,'.',','), 0, 0, 'R');
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

	case 'InsertarTblUtilidadPorAgentes':
	session_start();
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorAgenteModelo.php';
	include_once '../../modelo/ConceptosReutilizablesModelo.php';
	UtilidadPorAgenteControlador::InsertarTblUtilidadPorAgentesControlador();
	break;

	case 'InsertarTblUtilidadPorAgentesSoloDocumentos':
	session_start();
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorAgenteModelo.php';
	include_once '../../modelo/ConceptosReutilizablesModelo.php';
	UtilidadPorAgenteControlador::InsertarTblUtilidadPorAgentesSoloDocumentosControlador();
	break;

	case 'InsertarTblUtilidadPorAgenteSoloProductos':
	session_start();
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorAgenteModelo.php';
	include_once '../../modelo/ConceptosReutilizablesModelo.php';
	UtilidadPorAgenteControlador::InsertarTblUtilidadPorAgenteSoloProductosControlador();
	break;

	case 'GenerarPdfUtilidadPorAgentes':
	session_start();
	include_once '../fpdf.php';
	include_once '../../configuracion/configuracion.php';
	include_once '../../configuracion/conexion.php';
	include_once '../../modelo/ventasconutilidad/UtilidadPorAgenteModelo.php';
	include_once '../../modelo/ConceptosReutilizablesModelo.php';
	UtilidadPorAgenteControlador::GenerarPdfUtilidadPorAgentesControlador();
	break;



	


}
