<?php
require_once __DIR__.'/UtilidadConsulta.php';

class UtilidadPorProductosModelo extends Conexion
{
    static public function InsertarTblUtilidadPorProductosModelo($data)
    {
        return self::Consultar($data);
    }

    static public function EjecutarSubConsultaUtilidadPorProductosModelo($data)
    {
        $filtros = [$data[0], $data[0], $data[1], $data[2], 1, 1, 0, $data[3], [], [], [], [], [], [], $data[4] ?? '0'];
        return self::Consultar($filtros, true);
    }

    static private function Consultar($data, $detalle = false)
    {
        $parametros = [];
        $condiciones = UtilidadConsulta::Documentos($data[2], $data[3], $data[7], $parametros);
        $condiciones .= ' AND m.CMOVTOOCULTO = 0 AND (m.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)
            AND p.CCODIGOPRODUCTO BETWEEN ? AND ?';
        $parametros[] = $data[0];
        $parametros[] = $data[1];
        $condiciones .= $data[4] == '0' ? ' AND p.CTIPOPRODUCTO IN (1,2)' : '';
        foreach ([8=>1, 9=>2, 10=>4, 11=>3, 12=>5, 13=>6] as $indice=>$clasificacion) {
            $condiciones .= UtilidadConsulta::Lista('p.CIDVALORCLASIFICACION'.$clasificacion, $data[$indice], $parametros);
        }
        if (!empty($data[14]) && $data[14] !== '0') {
            $condiciones .= ' AND a.CCODIGOAGENTE = ?';
            $parametros[] = $data[14];
        }

        // Una fila por movimiento padre: los componentes nunca multiplican ventas ni descuentos.
        $sql = "WITH Movimientos AS (
            SELECT p.CIDPRODUCTO, p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO, p.CTIPOPRODUCTO,
                d.CIDDOCUMENTO, d.CSERIEDOCUMENTO, d.CFOLIO, d.CFECHA,
                c.CCODIGOCLIENTE, c.CRAZONSOCIAL, a.CCODIGOAGENTE, a.CNOMBREAGENTE,
                u.CABREVIATURA, m.CIDMOVIMIENTO,
                CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -1 ELSE 1 END AS Signo,
                ISNULL(m.CUNIDADESCAPTURADAS,0) AS Unidades,
                ISNULL(m.CNETO,0) * factor.Proporcion AS Ventas,
                (ISNULL(m.CDESCUENTO1,0) + ISNULL(m.CDESCUENTO2,0) + ISNULL(m.CDESCUENTO3,0)) * factor.Proporcion AS Descuento,
                CASE WHEN p.CTIPOPRODUCTO = 2 THEN
                    CASE WHEN d.CDEVUELTO = 1 THEN ISNULL(paquete.CostoCompleto,0) ELSE ISNULL(paquete.CostoPendiente,0) END
                    ELSE ISNULL(m.CCOSTOESPECIFICO, p.CCOSTOESTANDAR)
                        * CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -1 ELSE 1 END END AS Costo
            FROM admDocumentos d
            INNER JOIN admMovimientos m ON m.CIDDOCUMENTO = d.CIDDOCUMENTO
            INNER JOIN admProductos p ON p.CIDPRODUCTO = m.CIDPRODUCTO
            INNER JOIN admAgentes a ON a.CIDAGENTE = d.CIDAGENTE
            LEFT JOIN admClientes c ON c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR
            LEFT JOIN admUnidadesMedidaPeso u ON u.CIDUNIDAD = m.CIDUNIDAD
            INNER JOIN admConceptos con ON con.CIDCONCEPTODOCUMENTO = d.CIDCONCEPTODOCUMENTO
                AND con.CSISTORIG <> 101 AND con.CCARTAPOR = 0
            CROSS APPLY (SELECT CASE WHEN p.CTIPOPRODUCTO = 2 AND d.CDEVUELTO <> 1
                THEN ISNULL(m.CUNIDADESPENDIENTES / NULLIF(m.CUNIDADESCAPTURADAS,0),0)
                ELSE 1 END AS Proporcion) factor
            OUTER APPLY (
                SELECT SUM(ISNULL(h.CCOSTOESPECIFICO,0)
                    * CASE WHEN h.CAFECTAEXISTENCIA = 1 THEN -1 ELSE 1 END) AS CostoCompleto,
                    SUM(ISNULL(h.CCOSTOESPECIFICO,0)
                    * CASE WHEN h.CAFECTAEXISTENCIA = 1 THEN -1 ELSE 1 END
                    * ISNULL(h.CUNIDADESPENDIENTES / NULLIF(h.CUNIDADESCAPTURADAS,0),0)) AS CostoPendiente
                FROM admMovimientos h
                WHERE p.CTIPOPRODUCTO = 2 AND h.CIDMOVTOOWNER = m.CIDMOVIMIENTO
                    AND h.CIDALMACEN = m.CIDALMACEN
                    AND (h.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)
            ) paquete
            WHERE $condiciones
        ) ";
        if ($detalle) {
            $sql .= "SELECT CSERIEDOCUMENTO AS Serie, CFOLIO AS Folio,
                CONVERT(varchar, CFECHA, 103) AS Fecha, CCODIGOCLIENTE, CRAZONSOCIAL AS [Razón Social],
                CCODIGOAGENTE, CNOMBREAGENTE AS Agente, Unidades * Signo AS Unidades, CABREVIATURA AS UM,
                Ventas * Signo AS [Importe Ventas], Descuento * Signo AS Descuento, Costo AS [Importe de Costo]
                FROM Movimientos ORDER BY CSERIEDOCUMENTO, CFOLIO, CFECHA, CIDMOVIMIENTO";
        } else {
            $sql .= "SELECT CIDPRODUCTO, CCODIGOPRODUCTO AS [Código], CNOMBREPRODUCTO AS Producto,
                SUM(Unidades * Signo) AS Unidades, SUM(Ventas * Signo) AS [Importe Ventas],
                SUM(Descuento * Signo) AS Descuento, SUM(Costo) AS [Importe Costo],
                CASE WHEN CTIPOPRODUCTO = 2 THEN '2' ELSE '1' END AS tipo
                FROM Movimientos GROUP BY CIDPRODUCTO, CCODIGOPRODUCTO, CNOMBREPRODUCTO, CTIPOPRODUCTO
                ORDER BY tipo DESC, CCODIGOPRODUCTO";
        }
        return UtilidadConsulta::Ejecutar($sql, $parametros);
    }
}
