<?php

//***************************
//		INICIO DE CLASE
//***************************
class UtilidadPorProductosModelo extends Conexion{

	static public function BuscarPaqueteUtilidadPorProductoModelo($codigo,$startdate,$endate){

		$query = "
		SELECT p.CCODIGOPRODUCTO as 'Código', p.CNOMBREPRODUCTO as 'Producto',
		sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CUNIDADESCAPTURADAS * (-1)),m.CUNIDADESCAPTURADAS)) as Unidades, 
		sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1))/m.CUNIDADESCAPTURADAS*m.CUNIDADESPENDIENTES,
			m.CNETO/m.CUNIDADESCAPTURADAS*m.CUNIDADESPENDIENTES)) as 'Importe Ventas',
		sum(iif(m.CAFECTAEXISTENCIA = 1,((m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3)/m.CUNIDADESCAPTURADAS*m.CUNIDADESPENDIENTES)*(-1),
		(m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3) / m.CUNIDADESCAPTURADAS * m.CUNIDADESPENDIENTES)) as Descuento, 
		ISNULL((select sum(iif(m1.CAFECTAEXISTENCIA = 1, (m1.CCOSTOESPECIFICO * (-1))/m1.CUNIDADESCAPTURADAS*m1.CUNIDADESPENDIENTES,
		m1.CCOSTOESPECIFICO/m1.CUNIDADESCAPTURADAS*m1.CUNIDADESPENDIENTES)) 
		from admMovimientos as m1 where m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO 
		and m1.CIDALMACEN = m.CIDALMACEN and m1.CUNIDADESPENDIENTES > 0 ),0)  as 'Importe Costo' 
		from admMovimientos as m 
		inner join admProductos as p on p.CIDPRODUCTO = m.CIDPRODUCTO 
		inner join admDocumentos as d on d.ciddocumento = m.ciddocumento 
		inner join admConceptos con on d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO and con.CSISTORIG <> 101
		and con.CCARTAPOR = 0
		where p.CTIPOPRODUCTO = 2 and d.CCANCELADO = 0 and m.CUNIDADESPENDIENTES > 0 
		and d.CFECHA >= Convert(datetime,'".$startdate."',103)  
		and d.CFECHA <= Convert(datetime,'".$endate."',103)    
		and p.CCODIGOPRODUCTO = '".$codigo."'
		group by m.CIDMOVIMIENTO, p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO, m.CIDALMACEN 
		";


		$stmt = Conexion::ConnecBdDinamico()->prepare($query);
		if ($stmt->execute()){
			$data = $stmt->fetch(PDO::FETCH_ASSOC);
			return $data;
		} else {
			return "error";
		}
	}

	static public function EjecutarSubConsultaUtilidadPorProductosModelo($data){
        //agramos los conceptos elegidos y se crea como si fuera array 
		$concepts = "'" . implode("','", $data['3']) . "'";

		$query = "SELECT d.CSERIEDOCUMENTO 'Serie', d.CFOLIO 'Folio' , CONVERT(varchar, d.CFECHA, 103) 'Fecha', c.CCODIGOCLIENTE, 
		c.CRAZONSOCIAL 'Razón Social', trim(a.CCODIGOAGENTE) CCODIGOAGENTE , a.CNOMBREAGENTE 'Agente', 
		m.CUNIDADESCAPTURADAS Unidades, u.CABREVIATURA 'UM',
		sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) 'Importe Ventas', 
		sum(iif(m.CAFECTAEXISTENCIA = 1,(m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3)*(-1),
		(m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3))) 'Descuento' ,
		isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
		iif(m1.CAFECTAEXISTENCIA = 1,
		ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR)*(-1),ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR)), 
		iif(m2.CAFECTAEXISTENCIA = 1,
		ISNULL(m2.CCOSTOESPECIFICO, p.CCOSTOESTANDAR)*(-1),ISNULL(m2.CCOSTOESPECIFICO, p.CCOSTOESTANDAR))))
		from admMovimientos m2
		inner join admProductos p on p.CIDPRODUCTO = m2.CIDPRODUCTO  and p.CCODIGOPRODUCTO = '".$data['0']."' 
		left join admMovimientos m1 on m1.CIDMOVTOOWNER = m2.CIDMOVIMIENTO and m1.CIDALMACEN = m2.CIDALMACEN and (m1.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)
		where m2.CIDDOCUMENTO = D.CIDDOCUMENTO and (m2.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1) 
		and m2.CIDMOVIMIENTO = m.CIDMOVIMIENTO ),0) 'Importe de Costo' 
		from admDocumentos as d 
		inner join admClientes as c on c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR 
		inner join admAgentes as a on a.CIDAGENTE = d.CIDAGENTE 
		inner join admMovimientos as m on m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)
		inner join admConceptos con on d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO and con.CSISTORIG <> 101
		and con.CCARTAPOR = 0
		inner join admProductos p on m.CIDPRODUCTO = p.CIDPRODUCTO
		inner join admUnidadesMedidaPeso u on m.CIDUNIDAD = u.CIDUNIDAD
		WHERE 		
			(
		        ( EXISTS (
		            SELECT * FROM admMovimientos as m1 
						WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(DATETIME, '".$data['1']."', 103)  
						AND m1.CFECHA <= Convert(DATETIME, '".$data['2']."', 103)
		        ))
		        OR 		       
				(d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
		    ) and
		    d.CCANCELADO = 0
		and d.CIDCONCEPTODOCUMENTO in (".$concepts.")   
		and d.CFECHA >= Convert(datetime,'". $data['1']."',103) 
		and d.CFECHA <= Convert(datetime,'". $data['2']."',103)
		and p.CCODIGOPRODUCTO = '".$data['0']."'

		group by d.CIDDOCUMENTO, d.CSERIEDOCUMENTO, d.CFOLIO, d.CFECHA, c.CCODIGOCLIENTE, c.CRAZONSOCIAL,
		a.CCODIGOAGENTE, a.CNOMBREAGENTE, m.CUNIDADESCAPTURADAS, u.CABREVIATURA, m.CIDMOVIMIENTO, d.CIDDOCUMENTO, d.CDEVUELTO
		order by d.CSERIEDOCUMENTO, d.CFOLIO, d.CFECHA, c.CRAZONSOCIAL, a.CNOMBREAGENTE
		";
		$stmt = Conexion::ConnecBdDinamico()->prepare($query);
		if ($stmt->execute()){
			$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $data;
		} else {
			return "error";
		}
	}



	static public function InsertarTblUtilidadPorProductosModelo($data){
		$query = "";
			//agramos los conceptos elegidos y se crea como si fuera array 
		$concepts = "'" . implode("','", $data['7']) . "'";

        	//agramos los conceptos elegidos y se crea como si fuera array 
		if (empty($data['8'])) {
			    // El array está vacío
			$cbx_LineGeneral = ""; 
		} else {
			    // El array tiene elementos
			$cbx_LineGeneral = "'" . implode("','", $data['8']) . "'";
			$cbx_LineGeneral = "AND P.CIDVALORCLASIFICACION1 in($cbx_LineGeneral)";
		}

		if (empty($data['9'])) {
			    // El array está vacío
			$cbx_LineDetailed = ""; 
		} else {
			    // El array tiene elementos
			$cbx_LineDetailed = "'" . implode("','", $data['9']) . "'";
			$cbx_LineDetailed = "AND P.CIDVALORCLASIFICACION2 in($cbx_LineDetailed)";
		}

		if (empty($data['10'])) {
			    // El array está vacío
			$cbx_CommissionIndicator = ""; 
		} else {
			    // El array tiene elementos
			$cbx_CommissionIndicator = "'" . implode("','", $data['10']) . "'";
			$cbx_CommissionIndicator = "AND P.CIDVALORCLASIFICACION4 in($cbx_CommissionIndicator)";
		}

		if (empty($data['11'])) {
			    // El array está vacío
			$cbx_TypeClassification = ""; 
		} else {
			    // El array tiene elementos
			$cbx_TypeClassification = "'" . implode("','", $data['11']) . "'";
			$cbx_TypeClassification = "AND P.CIDVALORCLASIFICACION3 in($cbx_TypeClassification)";
		}

		if (empty($data['12'])) {
			    // El array está vacío
			$cbx_Rotation = ""; 
		} else {
			    // El array tiene elementos
			$cbx_Rotation = "'" . implode("','", $data['12']) . "'";
			$cbx_Rotation = "AND P.CIDVALORCLASIFICACION5 in($cbx_Rotation)";
		}

		if (empty($data['13'])) {
			    // El array está vacío
			$cbx_DailyReview = ""; 
		} else {
			    // El array tiene elementos
			$cbx_DailyReview = "'" . implode("','", $data['13']) . "'";
			$cbx_DailyReview = "AND P.CIDVALORCLASIFICACION6 in($cbx_DailyReview)";
		}

        	//filtro para que se agren los servicios, cuando es diferente a 0 se agregan los servicios
		if ($data['4'] == '0') {
			$tipoproduct= '= 1';
		}else{
			$tipoproduct= '<> 2';
		}

		$agente = '';
		if ($data['14'] != 0) {
			$agente = "	and ag.CCODIGOAGENTE = '".$data['14']."' ";
		}


		$query = "
		SELECT p.CIDPRODUCTO, p.CCODIGOPRODUCTO as 'Código', p.CNOMBREPRODUCTO as 'Producto', 
		SUM(IIF(m.CAFECTAEXISTENCIA = 1, (m.CUNIDADESCAPTURADAS * (-1)), m.CUNIDADESCAPTURADAS)) as Unidades,
		SUM(IIF(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) as 'Importe Ventas', 
		SUM(IIF(m.CAFECTAEXISTENCIA = 1, (m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3) * (-1), 
			m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3)) as Descuento,  
		SUM(IIF(m.CAFECTAEXISTENCIA = 1, (ISNULL(m.CCOSTOESPECIFICO, p.CCOSTOESTANDAR) * (-1)), ISNULL(m.CCOSTOESPECIFICO, p.CCOSTOESTANDAR))) AS 'Importe Costo',
		'1' tipo
		FROM admMovimientos as m 
		INNER JOIN admProductos as p ON p.CIDPRODUCTO = m.CIDPRODUCTO 
		INNER JOIN admDocumentos as d ON d.ciddocumento = m.ciddocumento 
		INNER join admAgentes as ag on ag.CIDAGENTE = d.CIDAGENTE
		INNER JOIN admConceptos con ON d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO 
		AND con.CSISTORIG <> 101 AND con.CCARTAPOR = 0
		WHERE 
		(
	        ( EXISTS (
	            SELECT * FROM admMovimientos as m1 
					WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(DATETIME, '".$data['2']."', 103)  
					AND m1.CFECHA <= Convert(DATETIME, '".$data['3']."', 103)
	        ))
	        OR 		       
			(d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
	    ) and
		p.CTIPOPRODUCTO $tipoproduct AND m.CMOVTOOCULTO = 0 AND d.CCANCELADO = 0

		AND (m.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)
		AND d.CIDCONCEPTODOCUMENTO in (".$concepts.")
		$cbx_LineGeneral
		$cbx_LineDetailed
		$cbx_CommissionIndicator
		$cbx_TypeClassification
		$cbx_Rotation
		$cbx_DailyReview
		$agente
		AND d.CFECHA >= CONVERT(DATETIME, '".$data['2']."', 103)  
			AND d.CFECHA <= CONVERT(DATETIME, '".$data['3']."', 103)  
			AND p.CCODIGOPRODUCTO >= '".$data['0']."' AND p.CCODIGOPRODUCTO <= '".$data['1']."'  
			GROUP BY p.CIDPRODUCTO,  p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO
			UNION 
			SELECT p.CIDPRODUCTO, p.CCODIGOPRODUCTO as 'Código', p.CNOMBREPRODUCTO as 'Producto', 
			'' as Unidades, 
			'' as 'Importe Ventas', 
			'' as Descuento, 
			'' as 'Importe Coste',
			'2' tipo 
			FROM admMovimientos as m 
			INNER JOIN admProductos as p ON p.CIDPRODUCTO = m.CIDPRODUCTO 
			INNER JOIN admDocumentos as d ON d.ciddocumento = m.ciddocumento 
			INNER join admAgentes as ag on ag.CIDAGENTE = d.CIDAGENTE
			INNER JOIN admConceptos con ON d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO 
			AND con.CSISTORIG <> 101 AND con.CCARTAPOR = 0
			LEFT JOIN admMovimientos as m1 ON m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO AND m1.CIDDOCUMENTODE != 3 
			WHERE 		
			(
		        ( EXISTS (
		            SELECT * FROM admMovimientos as m1 
						WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(DATETIME, '".$data['2']."', 103)  
						AND m1.CFECHA <= Convert(DATETIME, '".$data['3']."', 103)
		        ))
		        OR 		       
				(d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
		    ) and
	    	 p.CTIPOPRODUCTO = 2 AND d.CCANCELADO = 0 
			AND d.CIDCONCEPTODOCUMENTO in (".$concepts.")
		$cbx_LineGeneral
		$cbx_LineDetailed
		$cbx_CommissionIndicator
		$cbx_TypeClassification
		$cbx_Rotation
		$cbx_DailyReview
		$agente
		AND d.CFECHA >= CONVERT(DATETIME, '".$data['2']."', 103)  
			AND d.CFECHA <= CONVERT(DATETIME, '".$data['3']."', 103)   
			AND p.CCODIGOPRODUCTO >= '".$data['0']."' AND p.CCODIGOPRODUCTO <= '".$data['1']."'  
			GROUP BY p.CIDPRODUCTO,p.CCODIGOPRODUCTO,p.CIMPORTEEXTRA1 , p.CNOMBREPRODUCTO
			ORDER BY tipo DESC, p.CCODIGOPRODUCTO;
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





