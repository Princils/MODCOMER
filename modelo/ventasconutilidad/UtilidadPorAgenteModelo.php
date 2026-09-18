<?php
require_once __DIR__.'/UtilidadConsulta.php';

class UtilidadPorAgenteModelo extends Conexion
{
    static public function InsertarTblUtilidadPorAgenteModelo($data)
    {
        return self::Consultar($data, 'agentes');
    }

    static public function InsertarTblUtilidadPorAgenteSoloProductosModelo($data)
    {
        return self::Consultar($data, 'productos');
    }

    static public function InsertarTblUtilidadPorAgenteSoloDocumentosModelo($data)
    {
        return self::Consultar($data, 'documentos');
    }

    // El resumen y ambos desgloses usan los mismos movimientos y la misma fórmula de costo.
    static private function Consultar($data, $modo)
    {
        $parametros = [];
        $condiciones = UtilidadConsulta::Documentos($data[21], $data[22], $data[0], $parametros);
        $condiciones .= ' AND m.CMOVTOOCULTO = 0 AND (m.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)';
        if ($modo === 'agentes') {
            $condiciones .= ' AND ag.CCODIGOAGENTE BETWEEN ? AND ?';
            $parametros[] = $data[1];
            $parametros[] = $data[2];
        } else {
            $condiciones .= ' AND ag.CCODIGOAGENTE = ?';
            $parametros[] = $data[23];
        }
        foreach ([3=>1,4=>2,5=>4,6=>3,7=>5,8=>6] as $indice=>$clasificacion) {
            $condiciones .= UtilidadConsulta::Lista('p.CIDVALORCLASIFICACION'.$clasificacion, $data[$indice], $parametros);
        }
        for ($i=1; $i<=6; $i++) {
            $condiciones .= UtilidadConsulta::Lista('ag.CIDVALORCLASIFICACION'.$i, $data[8+$i], $parametros);
        }
        foreach ([15=>1,16=>2,17=>4,18=>3,19=>5,20=>6] as $indice=>$clasificacion) {
            $condiciones .= UtilidadConsulta::Lista('c.CIDVALORCLASIFCLIENTE'.$clasificacion, $data[$indice], $parametros);
        }
        $baseDescuento = UtilidadConsulta::BaseDescuento();
        $descuentoDocumento = UtilidadConsulta::DescuentoDocumento();
        $sql = "WITH Movimientos AS (
            SELECT ag.CIDAGENTE, ag.CCODIGOAGENTE, ag.CNOMBREAGENTE,
                d.CIDDOCUMENTO, d.CSERIEDOCUMENTO, d.CFOLIO, d.CRAZONSOCIAL,
                p.CIDPRODUCTO, p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO,
                CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -1 ELSE 1 END AS Signo,
                ISNULL(m.CUNIDADESCAPTURADAS,0) AS Unidades, ISNULL(m.CNETO,0) AS Neto,
                ISNULL(m.CDESCUENTO1,0) + ISNULL(m.CDESCUENTO2,0) + ISNULL(m.CDESCUENTO3,0)
                    + ISNULL($descuentoDocumento,0) AS Descuento,
                CASE WHEN p.CTIPOPRODUCTO = 2 THEN ISNULL(paquete.Costo,0)
                    ELSE ISNULL(m.CCOSTOESPECIFICO,0) END AS Costo
            FROM admDocumentos d
            INNER JOIN admAgentes ag ON ag.CIDAGENTE = d.CIDAGENTE
            INNER JOIN admClientes c ON c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR
            INNER JOIN admMovimientos m ON m.CIDDOCUMENTO = d.CIDDOCUMENTO
            INNER JOIN admProductos p ON p.CIDPRODUCTO = m.CIDPRODUCTO
            INNER JOIN admConceptos con ON con.CIDCONCEPTODOCUMENTO = d.CIDCONCEPTODOCUMENTO
                AND con.CSISTORIG <> 101 AND con.CCARTAPOR = 0
            OUTER APPLY ($baseDescuento) bd
            OUTER APPLY (
                SELECT SUM(ISNULL(h.CCOSTOESPECIFICO, ph.CCOSTOESTANDAR)) AS Costo
                FROM admMovimientos h
                INNER JOIN admProductos ph ON ph.CIDPRODUCTO = h.CIDPRODUCTO
                WHERE p.CTIPOPRODUCTO = 2 AND h.CIDMOVTOOWNER = m.CIDMOVIMIENTO
                    AND h.CIDALMACEN = m.CIDALMACEN
                    AND (h.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)
            ) paquete
            WHERE $condiciones
        ) ";
        if ($modo === 'agentes') {
            $campos = 'CIDAGENTE AS IDAGENTE, CCODIGOAGENTE AS CODIGOAGENTE, CNOMBREAGENTE AS NOMBREAGENTE';
            $grupo = 'CIDAGENTE, CCODIGOAGENTE, CNOMBREAGENTE';
            $orden = 'CNOMBREAGENTE, CCODIGOAGENTE';
        } elseif ($modo === 'documentos') {
            $campos = 'CIDDOCUMENTO, CNOMBREAGENTE, CSERIEDOCUMENTO, CFOLIO, CRAZONSOCIAL';
            $grupo = $campos;
            $orden = 'CIDDOCUMENTO';
        } else {
            $campos = 'CCODIGOPRODUCTO AS [Código], CNOMBREPRODUCTO AS Producto, SUM(Unidades * Signo) AS Unidades';
            $grupo = 'CIDPRODUCTO, CCODIGOPRODUCTO, CNOMBREPRODUCTO';
            $orden = 'CCODIGOPRODUCTO';
        }
        $sql .= "SELECT $campos, SUM(Neto * Signo) AS Neto, SUM(Descuento * Signo) AS Descuento,
            SUM(Costo * Signo) AS Costo, SUM((Neto - Costo) * Signo) AS Utilidad
            FROM Movimientos GROUP BY $grupo ORDER BY $orden";
        return UtilidadConsulta::Ejecutar($sql, $parametros);
    }
}
