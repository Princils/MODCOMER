<?php

class MargenesProteccionModelo extends Conexion
{
    // Consulta el costo histórico del almacén 0 y las existencias al corte.
    static public function ConsultarMargenesModelo($data)
    {
        $precio = 'p.CPRECIO' . $data['lista'];
        $parametros = array($data['fecha'], $data['fecha']);
        $condiciones = 'p.CTIPOPRODUCTO = 1';

        if (!$data['inactivos']) {
            $condiciones .= ' AND p.CSTATUSPRODUCTO = 1';
        }
        if ($data['suprimirceros']) {
            $condiciones .= ' AND ' . $precio . ' > 0';
        }
        foreach (array('inicial' => '>=', 'final' => '<=') as $campo => $operador) {
            if ($data[$campo] !== '') {
                $condiciones .= ' AND p.CCODIGOPRODUCTO ' . $operador . ' ?';
                $parametros[] = $data[$campo];
            }
        }
        foreach ($data['clasificaciones'] as $numero => $valores) {
            if (count($valores) > 0) {
                $condiciones .= ' AND p.CIDVALORCLASIFICACION' . $numero;
                $condiciones .= ' IN (' . implode(',', array_fill(0, count($valores), '?')) . ')';
                $parametros = array_merge($parametros, $valores);
            }
        }

        $query = "
            WITH MovimientosCorte AS (
                SELECT m.CIDPRODUCTO,
                    ROUND(SUM(CASE m.CAFECTAEXISTENCIA
                        WHEN 1 THEN m.CUNIDADES
                        WHEN 2 THEN m.CUNIDADES * (-1)
                        ELSE 0 END), 2) AS Existencia
                FROM admMovimientos m
                INNER JOIN admAlmacenes a ON a.CIDALMACEN = m.CIDALMACEN
                WHERE m.CAFECTADOINVENTARIO <> 0
                    AND m.CUNIDADES > 0.01
                    AND m.CFECHA <= CONVERT(date, ?, 103)
                GROUP BY m.CIDPRODUCTO
            )
            SELECT p.CIDPRODUCTO AS IdProducto,
                p.CCODIGOPRODUCTO AS Codigo,
                p.CNOMBREPRODUCTO AS Producto,
                u.CNOMBREUNIDAD AS Unidad,
                $precio AS Precio,
                CASE WHEN mc.CIDPRODUCTO IS NULL THEN NULL ELSE h.Costo END AS Costo,
                p.CMARGENUTILIDAD AS MargenActual,
                p.CPRECIO1 AS Precio1, p.CPRECIO2 AS Precio2,
                p.CPRECIO3 AS Precio3, p.CPRECIO4 AS Precio4,
                p.CPRECIO5 AS Precio5,
                mc.Existencia
            FROM admProductos p
            INNER JOIN admUnidadesMedidaPeso u ON u.CIDUNIDAD = p.CIDUNIDADBASE
            LEFT JOIN MovimientosCorte mc ON mc.CIDPRODUCTO = p.CIDPRODUCTO
            OUTER APPLY (
                SELECT TOP 1 ROUND(ch.CCOSTOH,
                    (SELECT CDECIMALESCOSTOS FROM admParametros)) AS Costo
                FROM admCostosHistoricos ch
                WHERE ch.CIDPRODUCTO = p.CIDPRODUCTO
                    AND ch.CIDALMACEN = 0
                    AND ch.CFECHACOSTOH <= CONVERT(date, ?, 103)
                ORDER BY ch.CFECHACOSTOH DESC
            ) h
            WHERE $condiciones
            ORDER BY p.CCODIGOPRODUCTO
        ";

        $conexion = Conexion::ConnecBdDinamico();
        if (!$conexion) {
            throw new RuntimeException('No se pudo conectar con la empresa seleccionada.');
        }
        $stmt = $conexion->prepare($query);
        if (!$stmt->execute($parametros)) {
            throw new RuntimeException('No se pudo consultar el costo histórico.');
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Aplica exclusivamente los cambios aprobados en la vista previa.
    static public function AplicarMargenesModelo($cambios)
    {
        $conexion = Conexion::ConnecBdDinamico();
        if (!$conexion) {
            throw new RuntimeException('No se pudo conectar con la empresa seleccionada.');
        }
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conexion->beginTransaction();
        try {
            $stmt = $conexion->prepare('UPDATE admProductos
                SET CMARGENUTILIDAD = ?
                WHERE CIDPRODUCTO = ? AND CMARGENUTILIDAD = ?');

            foreach ($cambios as $fila) {
                $stmt->execute(array($fila['MargenNuevo'], $fila['IdProducto'], $fila['MargenActual']));
                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('Un producto cambió desde la vista previa. Vuelve a calcular antes de aplicar.');
                }
            }
            $conexion->commit();
            return count($cambios);
        } catch (Throwable $error) {
            if ($conexion->inTransaction()) {
                $conexion->rollBack();
            }
            throw $error;
        }
    }
}
