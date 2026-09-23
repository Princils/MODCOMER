<?php

class InventarioModelo extends Conexion
{
    static public function ConsultarInventarioModelo($data)
    {
        $parametros = array($data['fecha']);
        $almacenes = '';
        if ($data['almacenes']) {
            $almacenes = ' AND a.CIDALMACEN IN (' . implode(',', array_fill(0, count($data['almacenes']), '?')) . ')';
            $parametros = array_merge($parametros, $data['almacenes']);
        }
        $parametros[] = $data['fecha'];
        $condiciones = 'p.CTIPOPRODUCTO = 1';
        if ($data['estado'] !== 3) {
            $condiciones .= ' AND p.CSTATUSPRODUCTO = ?';
            $parametros[] = $data['estado'] === 1 ? 1 : 0;
        }
        foreach (array('inicial' => '>=', 'final' => '<=') as $campo => $operador) {
            if ($data[$campo] !== '') {
                $condiciones .= ' AND p.CCODIGOPRODUCTO ' . $operador . ' ?';
                $parametros[] = $data[$campo];
            }
        }
        foreach ($data['clasificaciones'] as $numero => $valores) {
            if ($valores) {
                $condiciones .= ' AND p.CIDVALORCLASIFICACION' . $numero . ' IN (' . implode(',', array_fill(0, count($valores), '?')) . ')';
                $parametros = array_merge($parametros, $valores);
            }
        }
        if ($data['soloexistencia']) {
            $condiciones .= ' AND COALESCE(mc.Existencia, 0) > 0';
        }
        // Los puntos de reorden se interpretan para un almacén concreto.
        $minimos = count($data['almacenes']) === 1;
        $unionMinimos = '';
        if ($minimos) {
            $unionMinimos = ' LEFT JOIN (SELECT CIDPRODUCTO, CIDALMACEN, SUM(CEXISTENCIAMINBASE) AS CEXISTENCIAMINBASE, SUM(CEXISTENCIAMAXBASE) AS CEXISTENCIAMAXBASE FROM admMaximosMinimos GROUP BY CIDPRODUCTO, CIDALMACEN) mm ON mm.CIDPRODUCTO = p.CIDPRODUCTO AND mm.CIDALMACEN = ?';
            // Este parámetro aparece después del costo y antes del WHERE.
            array_splice($parametros, 2 + count($data['almacenes']), 0, $data['almacenes']);
        }
        $minimo = $minimos ? 'COALESCE(mm.CEXISTENCIAMINBASE, 0)' : 'NULL';
        $maximo = $minimos ? 'COALESCE(mm.CEXISTENCIAMAXBASE, 0)' : 'NULL';
        $precios = array();
        for ($lista = 1; $lista <= 10; $lista++) {
            $precios[] = 'p.CPRECIO' . $lista . ' AS Precio' . $lista;
        }
        $precios = implode(",\n                ", $precios);
        $query = "
            WITH MovimientosCorte AS (
                SELECT m.CIDPRODUCTO,
                    ROUND(SUM(CASE m.CAFECTAEXISTENCIA
                        WHEN 1 THEN m.CUNIDADES
                        WHEN 2 THEN -m.CUNIDADES ELSE 0 END), 2) AS Existencia
                FROM admMovimientos m
                INNER JOIN admAlmacenes a ON a.CIDALMACEN = m.CIDALMACEN
                WHERE m.CAFECTADOINVENTARIO <> 0 AND m.CUNIDADES > 0.01
                    AND m.CFECHA < DATEADD(day, 1, CONVERT(date, ?, 23))
                    $almacenes
                GROUP BY m.CIDPRODUCTO
            )
            SELECT p.CIDPRODUCTO AS IdProducto, p.CCODIGOPRODUCTO AS Codigo,
                p.CNOMBREPRODUCTO AS Producto, u.CABREVIATURA AS Unidad,
                p.CIMPUESTO1 AS Impuesto, c1.CVALORCLASIFICACION AS Familia,
                c2.CVALORCLASIFICACION AS Detallada,
                COALESCE(mc.Existencia, 0) AS Existencia,
                CASE WHEN mc.CIDPRODUCTO IS NULL THEN NULL ELSE h.Costo END AS Costo,
                $minimo AS Minimo, $maximo AS Maximo,
                $precios
            FROM admProductos p
            LEFT JOIN admUnidadesMedidaPeso u ON u.CIDUNIDAD = p.CIDUNIDADBASE
            LEFT JOIN admClasificacionesValores c1 ON c1.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION1
            LEFT JOIN admClasificacionesValores c2 ON c2.CIDVALORCLASIFICACION = p.CIDVALORCLASIFICACION2
            LEFT JOIN MovimientosCorte mc ON mc.CIDPRODUCTO = p.CIDPRODUCTO
            OUTER APPLY (
                SELECT TOP 1 ROUND(ch.CCOSTOH, (SELECT CDECIMALESCOSTOS FROM admParametros)) AS Costo
                FROM admCostosHistoricos ch
                WHERE ch.CIDPRODUCTO = p.CIDPRODUCTO AND ch.CIDALMACEN = 0
                    AND ch.CFECHACOSTOH < DATEADD(day, 1, CONVERT(date, ?, 23))
                ORDER BY ch.CFECHACOSTOH DESC, ch.CIDMOVIMIENTO DESC, ch.CIDCOSTOH DESC
            ) h
            $unionMinimos
            WHERE $condiciones
            ORDER BY p.CCODIGOPRODUCTO";
        $consulta = self::ConexionInventario()->prepare($query);
        $consulta->execute($parametros);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    static public function CatalogosModelo()
    {
        $conexion = self::ConexionInventario();
        return array(
            'almacenes' => $conexion->query('SELECT CIDALMACEN AS Id, CCODIGOALMACEN AS Codigo, CNOMBREALMACEN AS Nombre FROM admAlmacenes WHERE CIDALMACEN > 0 ORDER BY CCODIGOALMACEN')->fetchAll(PDO::FETCH_ASSOC),
            'clasificaciones' => $conexion->query('SELECT CIDVALORCLASIFICACION AS Id, CIDCLASIFICACION - 24 AS Numero, CVALORCLASIFICACION AS Nombre FROM admClasificacionesValores WHERE CIDCLASIFICACION BETWEEN 25 AND 30 ORDER BY CVALORCLASIFICACION')->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    static public function ProductosModelo($termino)
    {
        $consulta = self::ConexionInventario()->prepare('SELECT TOP 50 CCODIGOPRODUCTO AS Codigo, CNOMBREPRODUCTO AS Producto FROM admProductos WHERE CTIPOPRODUCTO = 1 AND (CCODIGOPRODUCTO LIKE ? OR CNOMBREPRODUCTO LIKE ?) ORDER BY CCODIGOPRODUCTO');
        $consulta->execute(array('%' . $termino . '%', '%' . $termino . '%'));
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
    static private function ConexionInventario()
    {
        $conexion = Conexion::ConnecBdDinamico();
        if (!$conexion) {
            throw new RuntimeException('No se pudo conectar con la empresa seleccionada.');
        }
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    }
}
