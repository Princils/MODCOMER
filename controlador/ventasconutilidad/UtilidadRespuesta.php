<?php

class UtilidadRespuesta
{
    static public function Totales($filas, $campoNeto, $campoCosto)
    {
        $totales = ['filas'=>count($filas), 'neto'=>0.0, 'descuento'=>0.0, 'costo'=>0.0];
        foreach ($filas as $fila) {
            $totales['neto'] += (float)$fila[$campoNeto];
            $totales['descuento'] += (float)$fila['Descuento'];
            $totales['costo'] += (float)$fila[$campoCosto];
        }
        $totales['utilidad'] = $totales['neto'] - $totales['costo'];
        $totales['margen'] = $totales['neto'] == 0 ? 0 : $totales['utilidad'] * 100 / $totales['neto'];
        return $totales;
    }

    static public function EnviarTabla($filas, $campoNeto, $campoCosto)
    {
        $html = ob_get_clean();
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'html' => $html,
            'totales' => self::Totales($filas, $campoNeto, $campoCosto)
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
