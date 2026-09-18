<?php
require_once __DIR__.'/UtilidadConsulta.php';
class UtilidadPorClasificacionModelo extends Conexion
{
    static public function Clasificaciones()
    {
        $db = Conexion::ConnecBdDinamico();
        if (!$db) {
            throw new RuntimeException('No se pudo conectar a la empresa.');
        }
        $stmt = $db->query('SELECT CIDCLASIFICACION, CNOMBRECLASIFICACION FROM admClasificaciones WHERE CIDCLASIFICACION BETWEEN 25 AND 30 ORDER BY CIDCLASIFICACION');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // El resumen y el desglose comparten exactamente los movimientos y filtros.
    static public function Consultar($datamodel, $detalle = false)
    {
        $columna = 'p.CIDVALORCLASIFICACION'.($datamodel['clasificacion'] - 24);
        // Mismo formato de fechas que los reportes por documentos y agentes.
        $startdate = date('d-m-Y', strtotime($datamodel['inicio']));
        $endate = date('d-m-Y', strtotime($datamodel['fin']));
        $parametros = [$startdate, $endate, $startdate, $endate];
        $condiciones = 'd.CCANCELADO = 0 AND m.CMOVTOOCULTO = 0 AND (m.CUNIDADESPENDIENTES > 0 OR d.CDEVUELTO = 1)
        AND d.CFECHA >= CONVERT(date, ?, 103) AND d.CFECHA < DATEADD(day, 1, CONVERT(date, ?, 103))
        AND (EXISTS (SELECT 1 FROM admMovimientos m1 WHERE m1.CIDMOVTOORIGEN = d.CIDDOCUMENTO
        AND m1.CFECHA >= CONVERT(date, ?, 103) AND m1.CFECHA < DATEADD(day, 1, CONVERT(date, ?, 103)))
        OR d.CIDDOCUMENTODE IN (4,5,6) OR con.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168))';
        $condiciones .= $datamodel['servicios'] ? ' AND p.CTIPOPRODUCTO <> 2' : ' AND p.CTIPOPRODUCTO = 1';
        $condiciones .= ' AND d.CIDCONCEPTODOCUMENTO IN ('.implode(',', array_fill(0, count($datamodel['conceptos']), '?')).')';
        $parametros = array_merge($parametros, $datamodel['conceptos']);
        if ($datamodel['documento'] !== 0) {
            $condiciones .= ' AND d.CIDDOCUMENTODE = ?';
            $parametros[] = $datamodel['documento'];
        }
        foreach (['agente' => 'ag.CCODIGOAGENTE', 'cliente' => 'c.CCODIGOCLIENTE'] as $key => $field) {
            if ($datamodel[$key] !== '') {
                $condiciones .= ' AND '.$field.' = ?';
                $parametros[] = $datamodel[$key];
            }
        }
        $campos = ['cbx_clas1client'=> 'c.CIDVALORCLASIFCLIENTE1', 'cbx_typeclient'=>'c.CIDVALORCLASIFCLIENTE2',
        'cbx_agent'=>'c.CIDVALORCLASIFCLIENTE4', 'cbx_zone'=>'c.CIDVALORCLASIFCLIENTE3',
        'cbx_clas5client'=>'c.CIDVALORCLASIFCLIENTE5', 'cbx_clas6client'=>'c.CIDVALORCLASIFCLIENTE6'];
        for ($i = 1; $i <= 6; $i++) {
            $campos[$i === 1 ? 'cbxAgent1' : 'agentClasification'.$i] = 'ag.CIDVALORCLASIFICACION'.$i;
        }
        foreach ($campos as $key => $field) {
            if (!empty($datamodel['filtros'][$key])) {
                $values = $datamodel['filtros'][$key];
                $condiciones .= ' AND '.$field.' IN ('.implode(',', array_fill(0, count($values), '?')).')';
                $parametros = array_merge($parametros, $values);
            }
        }
        if ($detalle) {
            $condiciones .= ' AND cv.CIDVALORCLASIFICACION = ?';
            $parametros[] = $datamodel['valor'];
            if ($datamodel['modo'] !== 2) {
                $condiciones .= ' AND ag.CIDAGENTE = ?';
                $parametros[] = $datamodel['idagente'];
            }
            if ($datamodel['modo'] !== 1) {
                $condiciones .= ' AND c.CIDCLIENTEPROVEEDOR = ?';
                $parametros[] = $datamodel['idcliente'];
            }
            $columnas = 'p.CIDPRODUCTO AS IdProducto, p.CCODIGOPRODUCTO AS Codigo, p.CNOMBREPRODUCTO AS Producto';
            $agrupacion = 'p.CIDPRODUCTO, p.CCODIGOPRODUCTO, p.CNOMBREPRODUCTO';
            $orden = 'p.CNOMBREPRODUCTO, p.CIDPRODUCTO';
        }
        else {
            $columnas = 'cv.CIDVALORCLASIFICACION AS Valor, cv.CVALORCLASIFICACION AS Clasificacion';
            $agrupacion = 'cv.CIDVALORCLASIFICACION, cv.CVALORCLASIFICACION';
            $orden = '';
            if ($datamodel['modo'] !== 2) {
                $columnas .= ', ag.CIDAGENTE AS IdAgente, ag.CNOMBREAGENTE AS Agente';
                $agrupacion .= ', ag.CIDAGENTE, ag.CNOMBREAGENTE';
                $orden .= 'ag.CNOMBREAGENTE, ag.CIDAGENTE, ';
            }
            if ($datamodel['modo'] !== 1) {
                $columnas .= ', c.CIDCLIENTEPROVEEDOR AS IdCliente, c.CRAZONSOCIAL AS Cliente';
                $agrupacion .= ', c.CIDCLIENTEPROVEEDOR, c.CRAZONSOCIAL';
                $orden .= 'c.CRAZONSOCIAL, c.CIDCLIENTEPROVEEDOR, ';
            }
            $orden .= 'cv.CVALORCLASIFICACION, cv.CIDVALORCLASIFICACION';
        }
        $ventas = 'SUM(CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -ISNULL(m.CNETO,0) ELSE ISNULL(m.CNETO,0) END)';
        $baseDescuento = UtilidadConsulta::BaseDescuento();
        $descuentoDocumento = UtilidadConsulta::DescuentoDocumento();
        $query = "SELECT $columnas,
        SUM(CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -ISNULL(m.CUNIDADESCAPTURADAS,0) ELSE ISNULL(m.CUNIDADESCAPTURADAS,0) END) AS Unidades,
        $ventas AS Ventas,
        SUM((ISNULL($descuentoDocumento,0) + ISNULL(m.CDESCUENTO1,0) + ISNULL(m.CDESCUENTO2,0) + ISNULL(m.CDESCUENTO3,0))
            * CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -1 ELSE 1 END) AS Descuento,
        SUM(CASE WHEN m.CAFECTAEXISTENCIA = 1 THEN -ISNULL(m.CCOSTOESPECIFICO,0) ELSE ISNULL(m.CCOSTOESPECIFICO,0) END) AS Costo
        FROM admDocumentos d
        INNER JOIN admClientes c ON c.CIDCLIENTEPROVEEDOR = d.CIDCLIENTEPROVEEDOR
        INNER JOIN admAgentes ag ON ag.CIDAGENTE = d.CIDAGENTE
        INNER JOIN admConceptos con ON con.CIDCONCEPTODOCUMENTO = d.CIDCONCEPTODOCUMENTO AND con.CSISTORIG <> 101 AND con.CCARTAPOR = 0
        INNER JOIN admMovimientos m ON m.CIDDOCUMENTO = d.CIDDOCUMENTO
        INNER JOIN admProductos p ON p.CIDPRODUCTO = m.CIDPRODUCTO
        INNER JOIN admClasificacionesValores cv ON cv.CIDVALORCLASIFICACION = $columna
        OUTER APPLY ($baseDescuento) bd
        WHERE $condiciones GROUP BY $agrupacion";
        if ($datamodel['ceros'] && !$detalle) {
            $query .= " HAVING ROUND($ventas, 2) <> 0";
        }
        $query .= ' ORDER BY '.$orden;
        $db = Conexion::ConnecBdDinamico();
        if (!$db) {
            throw new RuntimeException('No se pudo conectar a la empresa.');
        }
        $stmt = $db->prepare($query);
        if (!$stmt->execute($parametros)) {
            throw new RuntimeException('No se pudo consultar el reporte.');
        }
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($data as &$fila) {
            foreach (['Ventas','Descuento','Costo','Unidades'] as $key) {
                $fila[$key] = (float)$fila[$key];
            }
            $fila['Utilidad'] = $fila['Ventas'] - $fila['Costo'];
            $fila['Margen'] = $fila['Ventas'] == 0 ? 0 : $fila['Utilidad'] / $fila['Ventas'] * 100;
        }
        unset($fila);
        return $data;
    }
}
