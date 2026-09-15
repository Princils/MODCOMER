<?php

//***************************
//		INICIO DE CLASE
//***************************
class UtilidadPorDocumentosModelo extends Conexion{

static public function InsertarTblUtilidadPorDocumentosModelo($client, $startdate, $endate, $checkboxValues){
		$concepts = "'" . implode("','", $checkboxValues) . "'";
		$whereclients = '';
		if ($client != 0) {
			$whereclients = "d.CIDCLIENTEPROVEEDOR = '".$client."' and";
		}
		$query = "
			SELECT 
			d.CSERIEDOCUMENTO 'Serie', d.CFOLIO 'Folio' , CONVERT(varchar, d.CFECHA, 103) 'Fecha', 
			c.CCODIGOCLIENTE 'ID_Cliente', c.CRAZONSOCIAL 'Razón Social', 
			a.CCODIGOAGENTE 'ID_Agente' , a.CNOMBREAGENTE 'Agente',  
                SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))  'Importe Ventas', 
                (d.CDESCUENTODOC1 + d.CDESCUENTODOC2 + sum(m.CDESCUENTO1+m.CDESCUENTO2+m.CDESCUENTO3)) 'Descuento',
			((SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))) - (iif(SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))<0,isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO),0)*(-1),isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)),0)))) as 'utilidad',
				iif( SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO)) = 0,
				((((SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))) - (iif(SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))<0,isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO),0)*(-1),isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)),0)))) * (100))/ (SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))))*(-1)
				,((((SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))) - (iif(SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))<0,isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO),0)*(-1),isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)),0))))) *(100))/(SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))))
			as 'porcentajeutilidad', 
			iif(SUM(iif(m.CAFECTAEXISTENCIA = 1,(m.CNETO* (-1)),m.CNETO))<0,isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO),0)*(-1),isnull((select sum(iif(p.CTIPOPRODUCTO = 2,
	                ISNULL(m1.CCOSTOESPECIFICO, p.CCOSTOESTANDAR),
	                ISNULL(m.CCOSTOESPECIFICO, 0)))
	                from admMovimientos m 
	                inner join admProductos p on p.CIDPRODUCTO = m.CIDPRODUCTO
	                left join admMovimientos m1 on m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO and m1.CIDALMACEN = m.CIDALMACEN 
	                where m.CIDDOCUMENTO = d.CIDDOCUMENTO and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)),0)) as   'Importe de Costo' 
			from admDocumentos as d 
			inner join admClientes as c on c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR 
			inner join admAgentes as a on a.CIDAGENTE = d.CIDAGENTE 
			inner join admMovimientos as m on m.CIDDOCUMENTO = d.CIDDOCUMENTO 
			inner join admConceptos as con on d.CIDCONCEPTODOCUMENTO = con.CIDCONCEPTODOCUMENTO and con.CSISTORIG <> 101
			and con.CCARTAPOR = 0
			where 
			$whereclients
			d.CCANCELADO = 0  
			AND 
			(
		        ( EXISTS (
		            SELECT * FROM admMovimientos as m1 
						WHERE M1.CIDMOVTOORIGEN = d.CIDDOCUMENTO and m1.CFECHA >= Convert(date,'".$startdate."',103)  
						AND m1.CFECHA <= Convert(date,'".$endate."',103)
		        ))
		        OR 
		        (d.CIDDOCUMENTODE IN ('4', '5', '6')) OR CON.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168)
		    ) 
			and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)
			and d.CIDDOCUMENTODE in ('3','4','5','6')   
			AND	con.CIDCONCEPTODOCUMENTO in(".$concepts.") 		
			and d.CFECHA >= Convert(date,'".$startdate."',103)  
			AND d.CFECHA <= Convert(date,'".$endate."',103)    
			group by d.CSERIEDOCUMENTO, d.CFOLIO, d.CFECHA,c.CCODIGOCLIENTE, c.CRAZONSOCIAL,a.CCODIGOAGENTE,
			a.CNOMBREAGENTE, d.CDESCUENTODOC1, d.CDESCUENTODOC2, d.CIDDOCUMENTO , d.CDEVUELTO
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

	static public function InsertarTblSubConsultaUtilidadPorDocumentosModelo($folio,$serie){

		$query = "
		SELECT 
		p.CCODIGOPRODUCTO as 'Codigo', p.CNOMBREPRODUCTO as 'Producto', 
                 sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) - 
				sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CCOSTOESPECIFICO * (-1)),m.CCOSTOESPECIFICO)) AS 'UTILIDAD',
                SUM(IIF(m.CAFECTAEXISTENCIA = 1, (m.CUNIDADESCAPTURADAS * (-1)), m.CUNIDADESCAPTURADAS)) 'Unidades', 
                sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) as 'Importe Ventas', 
                sum(m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3) as 'Descuento', 
                sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CCOSTOESPECIFICO * (-1)),m.CCOSTOESPECIFICO))as 'Importe Coste'
		from admMovimientos as m 
		inner join admProductos as p on p.CIDPRODUCTO = m.CIDPRODUCTO 
		inner join admDocumentos as d on d.ciddocumento = m.ciddocumento 
		where p.CTIPOPRODUCTO <> 2 and m.CMOVTOOCULTO = 0 and d.CCANCELADO = 0
		and (m.CUNIDADESPENDIENTES >= 0 or d.CDEVUELTO = 1)

		and d.CFOLIO = '".$folio."' and d.CSERIEDOCUMENTO = '".$serie."'
		group by p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO, m.CAFECTAEXISTENCIA 
		union 
		select p.CCODIGOPRODUCTO as 'Codigo', p.CNOMBREPRODUCTO as 'Producto',
		sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) - iif(sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) < 0,ISNULL((select sum(m1.CCOSTOESPECIFICO)  
                from admMovimientos as m1     where m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO 
	                and m1.CIDALMACEN = m.CIDALMACEN  ),0) *(-1),ISNULL((select sum(m1.CCOSTOESPECIFICO)  
                from admMovimientos as m1     where m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO 
	                and m1.CIDALMACEN = m.CIDALMACEN  ),0))  AS 'UTILIDAD',
                sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CUNIDADESCAPTURADAS * (-1)), m.CUNIDADESCAPTURADAS)) as 'Unidades',  
                sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) as 'Importe Ventas', 
                sum(m.CDESCUENTO1 + m.CDESCUENTO2 + m.CDESCUENTO3)  as 'Descuento',

				iif(sum(iif(m.CAFECTAEXISTENCIA = 1, (m.CNETO * (-1)), m.CNETO)) < 0,ISNULL((select sum(m1.CCOSTOESPECIFICO)  
                from admMovimientos as m1     where m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO 
	                and m1.CIDALMACEN = m.CIDALMACEN  ),0) *(-1),ISNULL((select sum(m1.CCOSTOESPECIFICO)  
                from admMovimientos as m1     where m1.CIDMOVTOOWNER = m.CIDMOVIMIENTO 
	                and m1.CIDALMACEN = m.CIDALMACEN  ),0))as 'Importe Coste'
		from admMovimientos as m 
		inner join admProductos as p on p.CIDPRODUCTO = m.CIDPRODUCTO 
		inner join admDocumentos as d on d.ciddocumento = m.ciddocumento 
		where 
		p.CTIPOPRODUCTO = 2 and d.CCANCELADO = 0  
		and (m.CUNIDADESPENDIENTES > 0 or d.CDEVUELTO = 1)

		and d.CFOLIO = '".$folio."' and d.CSERIEDOCUMENTO = '".$serie."'

		group by p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO, m.CIDMOVIMIENTO, m.CIDALMACEN 
		order by p.CCODIGOPRODUCTO ";


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





