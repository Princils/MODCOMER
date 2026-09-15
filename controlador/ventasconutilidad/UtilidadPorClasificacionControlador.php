<?php
class UtilidadPorClasificacionControlador
{
    static public function Pdf($data, $datacontroller, $detalle)
    {
        require_once __DIR__.'/../../vista/vendor/fpdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A3');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(false);
        $columns = $detalle
        ? [['Codigo','Código',35], ['Producto','Producto',120], ['Unidades','Unidades',30]]
        : [['Clasificacion','Clasificación',60], ['Agente','Agente',65], ['Cliente','Razón social',85]];
        foreach (['Ventas','Descuento','Costo','Utilidad','Margen'] as $key) {
            $columns[] = [$key, $key === 'Margen' ? 'Margen (%)' : $key, 35];
        }
        $text = function ($value) {
            return iconv('UTF-8', 'windows-1252//TRANSLIT', (string)$value);
        }
        ;
        $header = function () use ($pdf, $datacontroller, $detalle, $columns, $text) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $text('Utilidad por clasificación'.($detalle ? ' - Productos' : '')), 0, 1);
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 7, $text($datacontroller['inicio'].' al '.$datacontroller['fin']), 0, 1);
            $pdf->SetFont('Arial', 'B', 9);
            foreach ($columns as $col) {
                $pdf->Cell($col[2], 7, $text($col[1]), 1);
            }
            $pdf->Ln();
        }
        ;
        $header();
        $totals = ['Ventas'=>0, 'Descuento'=>0, 'Costo'=>0, 'Utilidad'=>0];
        foreach ($data as $fila) {
            if ($pdf->GetY() > 275) {
                $header();
            }
            $pdf->SetFont('Arial', '', 8);
            foreach ($columns as $col) {
                $numeric = in_array($col[0], ['Unidades','Ventas','Descuento','Costo','Utilidad','Margen'], true);
                $value = $numeric ? number_format($fila[$col[0]], 2, '.', ',') : ($fila[$col[0]] ?? '');
                $value = $text($value);
                while ($pdf->GetStringWidth($value) > $col[2] - 3) {
                    $value = substr($value, 0, -1);
                }
                $pdf->Cell($col[2], 6, $value, 1, 0, $numeric ? 'R' : 'L');
            }
            $pdf->Ln();
            foreach ($totals as $key => $value) {
                $totals[$key] += $fila[$key];
            }
        }
        if ($pdf->GetY() > 265) {
            $header();
        }
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Ln(4);
        $pdf->Cell(0, 7, $text('Filas: '.count($data).'   Ventas: '.number_format($totals['Ventas'],2).
        '   Descuento: '.number_format($totals['Descuento'],2).'   Costo: '.number_format($totals['Costo'],2).
        '   Utilidad: '.number_format($totals['Utilidad'],2).'   Margen: '.number_format($totals['Ventas'] == 0 ? 0 : $totals['Utilidad'] / $totals['Ventas'] * 100,2).'%'), 0, 1);
        header('Content-Type: application/pdf');
        $pdf->Output('I', 'UtilidadPorClasificacion.pdf');
    }
    static public function LeerFiltros($datosFormulario, $detalle)
    {
        $datacontroller = [];
        foreach (['inicio'=>'startdate', 'fin'=>'endate'] as $key => $name) {
            $value = $datosFormulario[$name] ?? '';
            $date = is_string($value) ? DateTime::createFromFormat('!Y-m-d', $value) : false;
            if (!$date || $date->format('Y-m-d') !== $value) {
                throw new InvalidArgumentException('Selecciona fechas válidas.');
            }
            $datacontroller[$key] = $value;
        }
        if ($datacontroller['inicio'] > $datacontroller['fin']) {
            throw new InvalidArgumentException('La fecha final debe ser igual o posterior a la inicial.');
        }
        $datacontroller['clasificacion'] = filter_var($datosFormulario['clasificacion'] ?? null, FILTER_VALIDATE_INT);
        $datacontroller['modo'] = filter_var($datosFormulario['modo'] ?? null, FILTER_VALIDATE_INT);
        if ($datacontroller['clasificacion'] < 25 || $datacontroller['clasificacion'] > 30 || !in_array($datacontroller['modo'], [0,1,2], true)) {
            throw new InvalidArgumentException('Selecciona una clasificación y agrupación válidas.');
        }
        $datacontroller['conceptos'] = self::Ids($datosFormulario['checkboxValues'] ?? '[]');
        if (!$datacontroller['conceptos']) {
            throw new InvalidArgumentException('Selecciona al menos un concepto.');
        }
        $datacontroller['documento'] = filter_var($datosFormulario['cbx_document'] ?? 0, FILTER_VALIDATE_INT);
        if ($datacontroller['documento'] === false || $datacontroller['documento'] < 0) {
            throw new InvalidArgumentException('Documento inválido.');
        }
        foreach (['agente','cliente'] as $key) {
            if (isset($datosFormulario[$key]) && !is_string($datosFormulario[$key])) {
                throw new InvalidArgumentException('Filtro inválido.');
            }
            $datacontroller[$key] = trim($datosFormulario[$key] ?? '');
        }
        $datacontroller['servicios'] = ($datosFormulario['servicios'] ?? '0') === '1';
        $datacontroller['ceros'] = ($datosFormulario['ceros'] ?? '0') === '1';
        $datacontroller['filtros'] = [];
        foreach (['cbx_clas1client','cbx_typeclient','cbx_agent','cbx_zone','cbx_clas5client','cbx_clas6client',
        'cbxAgent1','agentClasification2','agentClasification3','agentClasification4','agentClasification5','agentClasification6'] as $key) {
            $datacontroller['filtros'][$key] = self::Ids($datosFormulario[$key] ?? '[]');
        }
        if ($detalle) {
            foreach (['valor','idagente','idcliente'] as $key) {
                $datacontroller[$key] = filter_var($datosFormulario[$key] ?? null, FILTER_VALIDATE_INT);
                if ($datacontroller[$key] === false || $datacontroller[$key] < 0) {
                    throw new InvalidArgumentException('Selecciona una fila válida.');
                }
            }
        }
        return $datacontroller;
    }
    static private function Ids($value)
    {
        $ids = is_string($value) ? json_decode($value, true) : null;
        if (!is_array($ids)) {
            throw new InvalidArgumentException('Selección inválida.');
        }
        foreach ($ids as &$id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id < 0) {
                throw new InvalidArgumentException('Selección inválida.');
            }
        }
        unset($id);
        return array_values(array_unique($ids));
    }
}
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    session_start();
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (empty($_SESSION['codigo_usuario']) || empty($_SESSION['database'])) {
            http_response_code(403);
            echo json_encode(['error'=>'Inicia sesión y selecciona una empresa.']);
            exit;
        }
        require_once __DIR__.'/../../configuracion/configuracion.php';
        require_once __DIR__.'/../../configuracion/conexion.php';
        require_once __DIR__.'/../../modelo/ModeloPrincipal.php';
        require_once __DIR__.'/../../modelo/ventasconutilidad/UtilidadPorClasificacionModelo.php';
        $id = ModeloPrincipal::BusacrReporteModelo('Utilidad por Clasificación', 'Ventas Con Utilidad');
        if (!$id || empty($_SESSION['lvl'.$id])) {
            http_response_code(403);
            echo json_encode(['error'=>'No tienes acceso a este reporte.']);
            exit;
        }
        $accion = $_POST['accionajax'] ?? '';
        if (!in_array($accion, ['Consultar','Productos','Pdf','PdfProductos'], true)) {
            throw new InvalidArgumentException('Acción inválida.');
        }
        $detalle = in_array($accion, ['Productos','PdfProductos'], true);
        $datacontroller = UtilidadPorClasificacionControlador::LeerFiltros($_POST, $detalle);
        $data = UtilidadPorClasificacionModelo::Consultar($datacontroller, $detalle);
        if (in_array($accion, ['Pdf','PdfProductos'], true)) {
            UtilidadPorClasificacionControlador::Pdf($data, $datacontroller, $detalle);
        }
        else {
            $respuesta = array(
            'filas' => $data
            );
            echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }
    }
    catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo json_encode(['error'=>$e->getMessage()]);
    }
    catch (Throwable $e) {
        error_log('Utilidad por clasificación: '.$e->getMessage());
        http_response_code(500);
        echo json_encode(array(
        'error' => 'No se pudo ejecutar la consulta del reporte. Revisa el registro de errores para conocer el detalle.'
        ), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
