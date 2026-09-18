<?php
require_once __DIR__.'/UtilidadConsulta.php';

//***************************
//		INICIO DE CLASE
//***************************
class UtilidadPorDocumentosModelo extends Conexion{

static public function InsertarTblUtilidadPorDocumentosModelo($client, $startdate, $endate, $checkboxValues)
    {
        $parametros = [];
        $condiciones = UtilidadConsulta::Documentos($startdate, $endate, $checkboxValues, $parametros);
        $condiciones .= ' AND d.CIDDOCUMENTODE IN (3,4,5,6)
            AND (m.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)';
        if ($client != 0 && $client !== '') {
            $condiciones .= ' AND d.CIDCLIENTEPROVEEDOR = ?';
            $parametros[] = $client;
        }
        $sql = "WITH Documentos AS (
            SELECT d.CIDDOCUMENTO, d.CDEVUELTO, d.CSERIEDOCUMENTO AS Serie, d.CFOLIO AS Folio,
                d.CFECHA AS FechaDocumento, c.CCODIGOCLIENTE AS ID_Cliente, c.CRAZONSOCIAL AS [Razón Social],
                a.CCODIGOAGENTE AS ID_Agente, a.CNOMBREAGENTE AS Agente,
                SUM(CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -ISNULL(m.CNETO,0) ELSE ISNULL(m.CNETO,0) END) AS Ventas,
                ISNULL(d.CDESCUENTODOC1,0) + ISNULL(d.CDESCUENTODOC2,0)
                    + SUM(ISNULL(m.CDESCUENTO1,0) + ISNULL(m.CDESCUENTO2,0) + ISNULL(m.CDESCUENTO3,0)) AS Descuento
            FROM admDocumentos d
            INNER JOIN admClientes c ON c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR
            INNER JOIN admAgentes a ON a.CIDAGENTE = d.CIDAGENTE
            INNER JOIN admMovimientos m ON m.CIDDOCUMENTO = d.CIDDOCUMENTO
            INNER JOIN admConceptos con ON con.CIDCONCEPTODOCUMENTO = d.CIDCONCEPTODOCUMENTO
                AND con.CSISTORIG <> 101 AND con.CCARTAPOR = 0
            WHERE $condiciones
            GROUP BY d.CIDDOCUMENTO, d.CDEVUELTO, d.CSERIEDOCUMENTO, d.CFOLIO, d.CFECHA,
                c.CCODIGOCLIENTE, c.CRAZONSOCIAL, a.CCODIGOAGENTE, a.CNOMBREAGENTE,
                d.CDESCUENTODOC1, d.CDESCUENTODOC2
        ), Costos AS (
            SELECT d.*, ISNULL(costo.Importe,0) * CASE WHEN d.Ventas < 0 THEN -1 ELSE 1 END AS Costo
            FROM Documentos d
            OUTER APPLY (
                SELECT SUM(CASE WHEN p.CTIPOPRODUCTO = 2
                    THEN ISNULL(h.CCOSTOESPECIFICO,p.CCOSTOESTANDAR)
                    ELSE ISNULL(m.CCOSTOESPECIFICO,0) END) AS Importe
                FROM admMovimientos m
                INNER JOIN admProductos p ON p.CIDPRODUCTO = m.CIDPRODUCTO
                LEFT JOIN admMovimientos h ON p.CTIPOPRODUCTO = 2
                    AND h.CIDMOVTOOWNER = m.CIDMOVIMIENTO AND h.CIDALMACEN = m.CIDALMACEN
                WHERE m.CIDDOCUMENTO = d.CIDDOCUMENTO
                    AND (d.Ventas < 0 OR m.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)
            ) costo
        )
        SELECT Serie, Folio, CONVERT(varchar, FechaDocumento,103) AS Fecha,
            ID_Cliente, [Razón Social], ID_Agente, Agente, Ventas AS [Importe Ventas], Descuento,
            Costo AS [Importe de Costo], Ventas - Costo AS utilidad,
            ISNULL((Ventas - Costo) * 100.0 / NULLIF(Ventas,0),0) AS porcentajeutilidad
        FROM Costos ORDER BY Serie, Folio, FechaDocumento, [Razón Social], Agente";
        return UtilidadConsulta::Ejecutar($sql, $parametros);
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





