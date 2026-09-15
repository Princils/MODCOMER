<?php

//***************************
//		INICIO DE CLASE
//***************************
class UtilidadPorAgenteModelo extends Conexion{

	static public function InsertarTblUtilidadPorAgenteModelo($data){
		//*******************CBX CONCEPTOS*****************************/
		$concepts = "'" . implode("','", $data['0']) . "'";

		//******************* FECHAS *****************************/
		$startdate = $data['21'];
		$endate = $data['22'];

		//******************* AGENTES *****************************/
		$startagentval = $data['1'];
		$endagentval = $data['2'];


		/********************CBX PRODUCTOS*****************************/
		$cbx_LineGeneral = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '3', 'CIDVALORCLASIFICACION1','P');
		$cbx_LineDetailed = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '4', 'CIDVALORCLASIFICACION2','P');
		$cbx_CommissionIndicator = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '5', 'CIDVALORCLASIFICACION4','P');
		$cbx_TypeClassification = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '6', 'CIDVALORCLASIFICACION3','P');
		$cbx_Rotation = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '7', 'CIDVALORCLASIFICACION5','P');
		$cbx_DailyReview = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '8', 'CIDVALORCLASIFICACION6','P');

		/********************CBX AGENTES*****************************/
		$cbxAgent1 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '9', 'CIDVALORCLASIFICACION1','c');
		$agentClasification2 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '10', 'CIDVALORCLASIFICACION2','c');
		$agentClasification3 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '11', 'CIDVALORCLASIFICACION3','c');
		$agentClasification4 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '12', 'CIDVALORCLASIFICACION4','c');
		$agentClasification5 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '13', 'CIDVALORCLASIFICACION5','c');
		$agentClasification6 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '14', 'CIDVALORCLASIFICACION6','c');

		/********************CBX CLIENTES*****************************/
		$cbx_clas1client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '15', 'CIDVALORCLASIFCLIENTE1','cli');
		$cbx_typeclient = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '16', 'CIDVALORCLASIFCLIENTE2','cli');
		$cbx_agent = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '17', 'CIDVALORCLASIFCLIENTE4','cli');
		$cbx_zone = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '18', 'CIDVALORCLASIFCLIENTE3','cli');
		$cbx_clas5client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '19', 'CIDVALORCLASIFCLIENTE5','cli');
		$cbx_clas6client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '20', 'CIDVALORCLASIFCLIENTE6','cli');


		$query = "
			SELECT  
			C.CIDAGENTE,C.CCODIGOAGENTE, c.CNOMBREAGENTE,
			iif(m.CAFECTAEXISTENCIA = 1, sum((m.CNETO * (-1))),sum(m.CNETO))  as 'Neto', 
			iif(m.CAFECTAEXISTENCIA = 1, ((d.CDESCUENTODOC1 + d.CDESCUENTODOC2 + sum(m.CDESCUENTO1+m.CDESCUENTO2+m.CDESCUENTO3))*(-1))
			,(d.CDESCUENTODOC1 + d.CDESCUENTODOC2 + sum(m.CDESCUENTO1+m.CDESCUENTO2+m.CDESCUENTO3)))  as 'Descuento', 
				isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
				iif(m.CAFECTAEXISTENCIA = 1, ISNULL(m1.CCOSTOESPECIFICO, p3.CCOSTOESTANDAR)*(-1),ISNULL(m1.CCOSTOESPECIFICO, p3.CCOSTOESTANDAR))
				, iif(m.CAFECTAEXISTENCIA = 1, ISNULL(m.CCOSTOESPECIFICO, 0)*(-1),ISNULL(m.CCOSTOESPECIFICO, 0)))) 
				from admMovimientos m 
					inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO 
					left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
					and (m1.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1) 
					LEFT JOIN admProductos p3 ON p3.CIDPRODUCTO = m1.CIDPRODUCTO
				where m.CIDDOCUMENTO = d.CIDDOCUMENTO  and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)  
					".$cbx_LineGeneral." 
			".$cbx_LineDetailed." 
			".$cbx_CommissionIndicator." 
			".$cbx_TypeClassification." 
			".$cbx_Rotation." 
			".$cbx_DailyReview." ),0) 'Costo',
			((iif(m.CAFECTAEXISTENCIA = 1, sum((m.CNETO * (-1))),sum(m.CNETO)))
				-(isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
					iif(m.CAFECTAEXISTENCIA = 1, ISNULL(p3.CCOSTOESTANDAR, m1.CCOSTOESTANDAR)*(-1),ISNULL(p3.CCOSTOESTANDAR, m1.CCOSTOESTANDAR))
					, iif(m.CAFECTAEXISTENCIA = 1, ISNULL(m.CCOSTOESPECIFICO, 0)*(-1),ISNULL(m.CCOSTOESPECIFICO, 0)))) 
					from admMovimientos m 
						inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO 
						left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
						and (m1.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1) 
					where m.CIDDOCUMENTO = d.CIDDOCUMENTO  and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)  
						".$cbx_LineGeneral." 
			".$cbx_LineDetailed." 
			".$cbx_CommissionIndicator." 
			".$cbx_TypeClassification." 
			".$cbx_Rotation." 
			".$cbx_DailyReview." ),0))) AS 'Utilidad',
			IIF(iif(m.CAFECTAEXISTENCIA = 1, sum((m.CNETO * (-1))),sum(m.CNETO)) = 0, 0, (((((iif(m.CAFECTAEXISTENCIA = 1, sum((m.CNETO * (-1))),sum(m.CNETO)))
				-(isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
					iif(m.CAFECTAEXISTENCIA = 1, ISNULL(m1.CCOSTOESPECIFICO, p3.CCOSTOESTANDAR)*(-1),ISNULL(m1.CCOSTOESPECIFICO, p3.CCOSTOESTANDAR))
					, iif(m.CAFECTAEXISTENCIA = 1, ISNULL(m.CCOSTOESPECIFICO, 0)*(-1),ISNULL(m.CCOSTOESPECIFICO, 0)))) 
					from admMovimientos m 
						inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO 
						left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
						and (m1.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1) 
						LEFT JOIN admProductos p3 ON p3.CIDPRODUCTO = m1.CIDPRODUCTO
					where m.CIDDOCUMENTO = d.CIDDOCUMENTO  and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)  
						".$cbx_LineGeneral." 
			".$cbx_LineDetailed." 
			".$cbx_CommissionIndicator." 
			".$cbx_TypeClassification." 
			".$cbx_Rotation." 
			".$cbx_DailyReview." ),0))))*100))/iif(m.CAFECTAEXISTENCIA = 1, sum((m.CNETO * (-1))),sum(m.CNETO))) AS 'Margen'

			from admDocumentos as d 
				inner join admAgentes as c on c.cidagente = d.cidagente 
				inner join admClientes as cli on cli.CIDCLIENTEPROVEEDOR = d.cidclienteproveedor
				inner join admMovimientos as m on m.CIDDOCUMENTO = d.CIDDOCUMENTO 
				inner join admProductos p on m.CIDPRODUCTO = p.CIDPRODUCTO 
				inner join admConceptos con on d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO and con.CSISTORIG <> 101
					and con.CCARTAPOR = 0
			where 
				(
			        ( EXISTS (
			            SELECT * FROM admMovimientos as m1 
							WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(DATETIME, '".$startdate."', 103)  
							AND m1.CFECHA <= Convert(DATETIME, '".$endate."', 103)
			        ))
			        OR 		       
					(d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
			    ) and
			d.CCANCELADO = 0 and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)
				and c.CCODIGOAGENTE BETWEEN '".$startagentval."' AND '".$endagentval."'
				and d.CFECHA >= Convert(datetime,'".$startdate."',103)  
				and d.CFECHA <= Convert(datetime,'".$endate."',103)       
				and d.CIDCONCEPTODOCUMENTO in (".$concepts.")    
			".$cbx_LineGeneral."
			".$cbx_LineDetailed."
			".$cbx_CommissionIndicator."
			".$cbx_TypeClassification."
			".$cbx_Rotation."
			".$cbx_DailyReview."
			".$cbx_clas1client."
			".$cbx_typeclient."
			".$cbx_agent."
			".$cbx_zone."
			".$cbx_clas5client."
			".$cbx_clas6client."
			".$cbxAgent1."
			".$agentClasification2."
			".$agentClasification3."
			".$agentClasification4."
			".$agentClasification5."
			".$agentClasification6."
			group by C.CIDAGENTE,d.CIDDOCUMENTO, d.CDESCUENTODOC1, d.CDESCUENTODOC2, m.CAFECTAEXISTENCIA, d.CDEVUELTO, C.CCODIGOAGENTE,c.CNOMBREAGENTE
			order by c.CNOMBREAGENTE,d.CIDDOCUMENTO
		";


		$stmt = Conexion::ConnecBdDinamico()->prepare($query);
		if ($stmt->execute()){
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$result = array();
			foreach ($data as $row) {
				$CIDAGENTE = $row['CIDAGENTE'];
				$CCODIGOAGENTE = $row['CCODIGOAGENTE'];
				$CNOMBREAGENTE = $row['CNOMBREAGENTE'];
				$Neto = round($row['Neto'],2);
				$Descuento = round($row['Descuento'],2);
				$Utilidad = (round($row['Utilidad'],2));
				$costo = round($row['Costo'],2);
		        // Verificar si el código de cliente ya existe en el array
				if (isset($result[$CCODIGOAGENTE])) {
		            // El código de cliente ya existe, sumar los valores
					$result[$CCODIGOAGENTE]['Neto'] += $Neto;
					$result[$CCODIGOAGENTE]['Descuento'] += $Descuento;
					$result[$CCODIGOAGENTE]['Utilidad'] += $Utilidad;
					$result[$CCODIGOAGENTE]['Costo'] += $costo;
				} else {
		            // El código de cliente no existe, agregar una nueva entrada al array
					$result[$CCODIGOAGENTE] = array(
						'IDAGENTE' => $CIDAGENTE,
						'CODIGOAGENTE' => $CCODIGOAGENTE,
						'NOMBREAGENTE' => $CNOMBREAGENTE,
						'Neto' => $Neto,
						'Descuento' => $Descuento,
						'Costo' => $costo,
						'Utilidad' => $Utilidad
					);
				}
			}
			return $result;
		} else {
			return "error";
		}
	}

	static public function InsertarTblUtilidadPorAgenteSoloProductosModelo($data){
		//*******************CBX CONCEPTOS*****************************/
		$concepts = "'" . implode("','", $data['0']) . "'";

		//******************* FECHAS Y CODIGO*****************************/
		$startdate = $data['21'];
		$endate = $data['22'];
		$Codigo = $data['23'];

		//******************* AGENTES *****************************/
		$startagentval = $data['1'];
		$endagentval = $data['2'];


		/********************CBX PRODUCTOS*****************************/
		$cbx_LineGeneral = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '3', 'CIDVALORCLASIFICACION1','P');
		$cbx_LineDetailed = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '4', 'CIDVALORCLASIFICACION2','P');
		$cbx_CommissionIndicator = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '5', 'CIDVALORCLASIFICACION4','P');
		$cbx_TypeClassification = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '6', 'CIDVALORCLASIFICACION3','P');
		$cbx_Rotation = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '7', 'CIDVALORCLASIFICACION5','P');
		$cbx_DailyReview = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '8', 'CIDVALORCLASIFICACION6','P');

		/********************CBX AGENTES*****************************/
		$cbxAgent1 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '9', 'CIDVALORCLASIFICACION1','Ag');
		$agentClasification2 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '10', 'CIDVALORCLASIFICACION2','Ag');
		$agentClasification3 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '11', 'CIDVALORCLASIFICACION3','Ag');
		$agentClasification4 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '12', 'CIDVALORCLASIFICACION4','Ag');
		$agentClasification5 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '13', 'CIDVALORCLASIFICACION5','Ag');
		$agentClasification6 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '14', 'CIDVALORCLASIFICACION6','Ag');

		/********************CBX CLIENTES*****************************/
		$cbx_clas1client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '15', 'CIDVALORCLASIFCLIENTE1','c');
		$cbx_typeclient = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '16', 'CIDVALORCLASIFCLIENTE2','c');
		$cbx_agent = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '17', 'CIDVALORCLASIFCLIENTE4','c');
		$cbx_zone = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '18', 'CIDVALORCLASIFCLIENTE3','c');
		$cbx_clas5client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '19', 'CIDVALORCLASIFCLIENTE5','c');
		$cbx_clas6client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '20', 'CIDVALORCLASIFCLIENTE6','c');


		$query = "
		SELECT p.CCODIGOPRODUCTO as 'Código', p.CNOMBREPRODUCTO as 'Producto', 
		 sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CUNIDADESCAPTURADAS * (-1)), m.CUNIDADESCAPTURADAS)) Unidades,
                sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO )) as 'Neto', 
                sum((m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3) / m.CUNIDADESCAPTURADAS * m.CUNIDADESPENDIENTES) as Descuento, 
				sum(iif(m.CAFECTAEXISTENCIA = 1, ( m.CCOSTOESPECIFICO * (-1)), m.CCOSTOESPECIFICO )) as 'Costo',
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION1) cla1,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION2) cla2,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION3) cla3,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION4) cla4,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION5) cla5,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION6) cla6
		from admMovimientos as m 
		inner join admProductos as p on p.CIDPRODUCTO = m.CIDPRODUCTO 
		inner join admDocumentos as d on d.ciddocumento = m.ciddocumento 
		inner join admAgentes as ag on ag.cidagente = d.cidagente 
		inner join admClientes as c on c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR
		inner join admConceptos con on d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO and con.CSISTORIG <> 101
		and con.CCARTAPOR = 0
		where 
			(
		        ( EXISTS (
		            SELECT * FROM admMovimientos as m1 
						WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(DATETIME, '".$startdate."', 103)  
						AND m1.CFECHA <= Convert(DATETIME, '".$endate."', 103)
		        ))
		        OR 		       
				(d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
		    ) and
		p.CTIPOPRODUCTO <> 2 and m.CMOVTOOCULTO = 0 and d.CCANCELADO = 0 
		and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)    
		AND d.CIDCONCEPTODOCUMENTO in (".$concepts.")
		".$cbx_LineGeneral."
		".$cbx_LineDetailed."
		".$cbx_CommissionIndicator."
		".$cbx_TypeClassification."
		".$cbx_Rotation."
		".$cbx_DailyReview."
		".$cbx_clas1client."
		".$cbx_typeclient."
		".$cbx_agent."
		".$cbx_zone."
		".$cbx_clas5client."
		".$cbx_clas6client."
		".$cbxAgent1."
		".$agentClasification2."
		".$agentClasification3."
		".$agentClasification4."
		".$agentClasification5."
		".$agentClasification6."
		and ag.CCODIGOAGENTE =  '".$Codigo."'   
		and d.CFECHA >= Convert(datetime,'".$startdate."',103)  
		and d.CFECHA <= Convert(datetime,'".$endate."',103)   
		group by p.CIMPORTEEXTRA1,p.CCODIGOPRODUCTO, p.CIMPORTEEXTRA1,p.CNOMBREPRODUCTO, m.CAFECTAEXISTENCIA, d.CDEVUELTO, 
		p.CIDVALORCLASIFICACION1, p.CIDVALORCLASIFICACION2, p.CIDVALORCLASIFICACION3, p.CIDVALORCLASIFICACION4,
		p.CIDVALORCLASIFICACION5, p.CIDVALORCLASIFICACION6  
		union 
		select p.CCODIGOPRODUCTO as 'Código', p.CNOMBREPRODUCTO as 'Producto', 
		sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CUNIDADESCAPTURADAS * (-1)), m.CUNIDADESCAPTURADAS)) as Unidades, 
                sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) as 'Neto', 
                sum(m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3) as Descuento, 
                ISNULL((select sum(iif(m1.CAFECTAEXISTENCIA = 1, (m1.CCOSTOESPECIFICO * (-1)), m1.CCOSTOESPECIFICO))
                    from admMovimientos as m1 
                    where m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
                        and (m1.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)),0)  as 'Costo',
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION1) cla1,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION2) cla2,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION3) cla3,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION4) cla4,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION5) cla5,
		(select iif(cv.CIDVALORCLASIFICACION = 0, '' ,cv.CVALORCLASIFICACION) from admClasificacionesValores cv where cv.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION6) cla6
		from admMovimientos as m 
		inner join admProductos as p on p.CIDPRODUCTO = m.CIDPRODUCTO 
		inner join admDocumentos as d on d.ciddocumento = m.ciddocumento 
		inner join admAgentes as ag on ag.cidagente = d.cidagente 
		inner join admClientes as c on c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR
		inner join admConceptos con on d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO and con.CSISTORIG <> 101
		and con.CCARTAPOR = 0
		where 
			(
		        ( EXISTS (
		            SELECT * FROM admMovimientos as m1 
						WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(DATETIME, '".$startdate."', 103)  
						AND m1.CFECHA <= Convert(DATETIME, '".$endate."', 103)
		        ))
		        OR 		       
				(d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
		    ) and
		p.CTIPOPRODUCTO = 2 and d.CCANCELADO = 0 
		and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)  
		".$cbx_LineGeneral."
		".$cbx_LineDetailed."
		".$cbx_CommissionIndicator."
		".$cbx_TypeClassification."
		".$cbx_Rotation."
		".$cbx_DailyReview."
		".$cbx_clas1client."
		".$cbx_typeclient."
		".$cbx_agent."
		".$cbx_zone."
		".$cbx_clas5client."
		".$cbx_clas6client."
		".$cbxAgent1."
		".$agentClasification2."
		".$agentClasification3."
		".$agentClasification4."
		".$agentClasification5."
		".$agentClasification6."
		AND d.CIDCONCEPTODOCUMENTO in (".$concepts.")
		and ag.CCODIGOAGENTE =  '".$Codigo."'   
		and d.CFECHA >= Convert(datetime,'".$startdate."',103)  
		and d.CFECHA <= Convert(datetime,'".$endate."',103)    
		group by p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO, m.CIDMOVIMIENTO, m.CIDALMACEN, d.CDEVUELTO, 
		p.CIDVALORCLASIFICACION1, p.CIDVALORCLASIFICACION2, p.CIDVALORCLASIFICACION3, p.CIDVALORCLASIFICACION4,
		p.CIDVALORCLASIFICACION5, p.CIDVALORCLASIFICACION6 
		order by p.CCODIGOPRODUCTO 
		";

		$stmt = Conexion::ConnecBdDinamico()->prepare($query);
		if ($stmt->execute()){
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		} else {
			return "error";
		}
	}



	static public function InsertarTblUtilidadPorAgenteSoloDocumentosModelo($data){
		//*******************CBX CONCEPTOS*****************************/
		$concepts = "'" . implode("','", $data['0']) . "'";

		//******************* FECHAS Y CODIGO*****************************/
		$startdate = $data['21'];
		$endate = $data['22'];
		$Codigo = $data['23'];

		//******************* AGENTES *****************************/
		$startagentval = $data['1'];
		$endagentval = $data['2'];


		/********************CBX PRODUCTOS*****************************/
		$cbx_LineGeneral = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '3', 'CIDVALORCLASIFICACION1','P');
		$cbx_LineDetailed = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '4', 'CIDVALORCLASIFICACION2','P');
		$cbx_CommissionIndicator = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '5', 'CIDVALORCLASIFICACION4','P');
		$cbx_TypeClassification = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '6', 'CIDVALORCLASIFICACION3','P');
		$cbx_Rotation = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '7', 'CIDVALORCLASIFICACION5','P');
		$cbx_DailyReview = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '8', 'CIDVALORCLASIFICACION6','P');

		/********************CBX AGENTES*****************************/
		$cbxAgent1 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '9', 'CIDVALORCLASIFICACION1','Ag');
		$agentClasification2 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '10', 'CIDVALORCLASIFICACION2','Ag');
		$agentClasification3 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '11', 'CIDVALORCLASIFICACION3','Ag');
		$agentClasification4 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '12', 'CIDVALORCLASIFICACION4','Ag');
		$agentClasification5 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '13', 'CIDVALORCLASIFICACION5','Ag');
		$agentClasification6 = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '14', 'CIDVALORCLASIFICACION6','Ag');

		/********************CBX CLIENTES*****************************/
		$cbx_clas1client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '15', 'CIDVALORCLASIFCLIENTE1','c');
		$cbx_typeclient = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '16', 'CIDVALORCLASIFCLIENTE2','c');
		$cbx_agent = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '17', 'CIDVALORCLASIFCLIENTE4','c');
		$cbx_zone = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '18', 'CIDVALORCLASIFCLIENTE3','c');
		$cbx_clas5client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '19', 'CIDVALORCLASIFCLIENTE5','c');
		$cbx_clas6client = ConceptosReutilizablesModelo::CreadorDeConcicionBd($data, '20', 'CIDVALORCLASIFCLIENTE6','c');


		$query = "
			SELECT  d.CIDDOCUMENTO, ag.CNOMBREAGENTE, d.CSERIEDOCUMENTO, d.CFOLIO, d.CRAZONSOCIAL,
			                iif(m.CAFECTAEXISTENCIA = 1, sum(m.CNETO * (-1)), sum(m.CNETO )) as 'Neto',
	                (d.CDESCUENTODOC1 + d.CDESCUENTODOC2 +sum(m.CDESCUENTO1+m.CDESCUENTO2+m.CDESCUENTO3)) 'Descuento',
			iif(m.CAFECTAEXISTENCIA = 1, (isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
		                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR), ISNULL(m.CCOSTOESPECIFICO, 0)))
		                from admMovimientos m
		                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
		                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                        and (m1.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)       
		                where m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1) 
						".$cbx_LineGeneral."
			".$cbx_LineDetailed."
			".$cbx_CommissionIndicator."
			".$cbx_TypeClassification."
			".$cbx_Rotation."
			".$cbx_DailyReview." ),0) * (-1)), isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
		                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR), ISNULL(m.CCOSTOESPECIFICO, 0)))
		                from admMovimientos m
		                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
		                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                        and (m1.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)       
		                where m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1) 
						".$cbx_LineGeneral."
			".$cbx_LineDetailed."
			".$cbx_CommissionIndicator."
			".$cbx_TypeClassification."
			".$cbx_Rotation."
			".$cbx_DailyReview." ),0)) 'Costo' 
			from admDocumentos as d 
			inner join admAgentes as ag on ag.cidagente = d.cidagente 
			join admclientes as c on c.CIDCLIENTEPROVEEDOR = d.cidclienteproveedor
			inner join admMovimientos as m on m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)
			inner join admProductos p on m.CIDPRODUCTO = p.CIDPRODUCTO
			inner join admConceptos con on d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO and con.CSISTORIG <> 101
			and con.CCARTAPOR = 0
			where 
				(
			        ( EXISTS (
			            SELECT * FROM admMovimientos as m1 
							WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(DATETIME, '".$startdate."', 103)  
							AND m1.CFECHA <= Convert(DATETIME, '".$endate."', 103)
			        ))
			        OR 		       
					(d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
			    ) and
			d.CCANCELADO = 0  
			and d.CFECHA >= Convert(datetime,'".$startdate."',103)  
				and d.CFECHA <= Convert(datetime,'".$endate."',103)    
				and d.CIDCONCEPTODOCUMENTO in (".$concepts.")     
			and ag.CCODIGOAGENTE='".$Codigo."' 
			".$cbx_LineGeneral."
			".$cbx_LineDetailed."
			".$cbx_CommissionIndicator."
			".$cbx_TypeClassification."
			".$cbx_Rotation."
			".$cbx_DailyReview."
			".$cbx_clas1client."
			".$cbx_typeclient."
			".$cbx_agent."
			".$cbx_zone."
			".$cbx_clas5client."
			".$cbx_clas6client."
			".$cbxAgent1."
			".$agentClasification2."
			".$agentClasification3."
			".$agentClasification4."
			".$agentClasification5."
			".$agentClasification6."
			 group by d.CRAZONSOCIAL,d.CIDDOCUMENTO, d.CFOLIO,d.CSERIEDOCUMENTO, ag.CNOMBREAGENTE, m.CAFECTAEXISTENCIA, d.CDESCUENTODOC1, d.CDESCUENTODOC2, m.CAFECTAEXISTENCIA, d.CDEVUELTO 
	                order by d.CIDDOCUMENTO
		";


		$stmt = Conexion::ConnecBdDinamico()->prepare($query);
		if ($stmt->execute()){
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		} else {
			return "error";
		}
	}



//***************************
//		  FIN DE CLASE
//***************************
}





