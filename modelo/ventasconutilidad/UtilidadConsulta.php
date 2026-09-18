<?php

// Fragmentos compartidos por los reportes; los valores del formulario se enlazan en PDO.
class UtilidadConsulta
{
    static public function Lista($campo, $valores, &$parametros, $obligatoria = false)
    {
        if (empty($valores)) {
            return $obligatoria ? ' AND 1 = 0' : '';
        }
        if (!is_array($valores)) {
            throw new InvalidArgumentException('La selección debe ser una lista.');
        }
        $parametros = array_merge($parametros, array_values($valores));
        return ' AND '.$campo.' IN ('.implode(',', array_fill(0, count($valores), '?')).')';
    }

    static public function Documentos($inicio, $fin, $conceptos, &$parametros)
    {
        $parametros = [$inicio, $fin, $inicio, $fin];
        return "d.CCANCELADO = 0
            AND d.CFECHA >= CONVERT(date, ?, 103)
            AND d.CFECHA < DATEADD(day, 1, CONVERT(date, ?, 103))
            AND (EXISTS (SELECT 1 FROM admMovimientos origen
                WHERE origen.CIDMOVTOORIGEN = d.CIDDOCUMENTO
                AND origen.CFECHA >= CONVERT(date, ?, 103)
                AND origen.CFECHA < DATEADD(day, 1, CONVERT(date, ?, 103)))
                OR d.CIDDOCUMENTODE IN (4,5,6)
                OR con.CIDCONCEPTODOCUMENTO IN (3,3070,3155,3156,3168))"
            .self::Lista('d.CIDCONCEPTODOCUMENTO', $conceptos, $parametros, true);
    }

    static public function Ejecutar($sql, $parametros)
    {
        $stmt = Conexion::ConnecBdDinamico()->prepare($sql);
        $stmt->execute($parametros);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // La base incluye todas las partidas visibles del documento, antes de filtrar productos.
    // Se prorratea por neto absoluto; si todos los netos son cero, por número de partidas.
    static public function BaseDescuento()
    {
        return "SELECT SUM(ABS(ISNULL(md.CNETO,0))) AS BaseDescuento,
            COUNT(*) AS PartidasDescuento
            FROM admMovimientos md
            WHERE md.CIDDOCUMENTO = d.CIDDOCUMENTO AND md.CMOVTOOCULTO = 0";
    }

    static public function DescuentoDocumento()
    {
        return '(ISNULL(d.CDESCUENTODOC1,0) + ISNULL(d.CDESCUENTODOC2,0))
            * CASE WHEN bd.BaseDescuento > 0
                THEN ABS(ISNULL(m.CNETO,0)) / NULLIF(bd.BaseDescuento,0)
                ELSE 1.0 / NULLIF(bd.PartidasDescuento,0) END';
    }
}
