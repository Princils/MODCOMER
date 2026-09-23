<?php

class InventarioControlador
{
    static public function GenerarPdfControlador($filas, $fecha)
    {
        require_once __DIR__ . '/../../vista/vendor/fpdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A3');
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(false);
        $campos = array('Codigo', 'Producto', 'Unidad', 'Impuesto', 'Familia', 'Detallada',
            'Existencia', 'Costo', 'Minimo', 'Maximo', 'ReordenMin', 'ReordenMax');
        $titulos = array('Codigo', 'Producto', 'UM', 'Imp.', 'Familia', 'Detallada',
            'Existencia', 'Costo prom.', 'Minimo', 'Maximo', 'Reorden min.', 'Reorden max.');
        $anchos = array(23, 55, 12, 12, 24, 24, 22, 22, 15, 15, 20, 20);
        for ($n = 1; $n <= 10; $n++) {
            $campos[] = 'Precio' . $n;
            $titulos[] = 'Precio ' . $n;
            $anchos[] = 14;
        }
        $encabezado = function () use ($pdf, $titulos, $anchos, $fecha) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 7, 'MODCOMERCIAL - Inventario - Corte: ' . $fecha, 0, 1);
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell(0, 5, 'Precios: listas vigentes. Minimos y maximos: solo al seleccionar un almacen.', 0, 1);
            $pdf->SetFillColor(45, 72, 140);
            $pdf->SetTextColor(255);
            $pdf->SetFont('Arial', 'B', 6);
            foreach ($titulos as $i => $titulo) { $pdf->Cell($anchos[$i], 7, $titulo, 1, 0, 'C', true); }
            $pdf->Ln();
            $pdf->SetTextColor(0);
            $pdf->SetFont('Arial', '', 6);
        };
        $encabezado();
        foreach ($filas as $fila) {
            if ($pdf->GetY() > 280) { $encabezado(); }
            foreach ($campos as $i => $campo) {
                $valor = $fila[$campo] ?? null;
                $numerico = $i === 3 || $i >= 6;
                $texto = $valor === null ? '-' : ($numerico ? number_format($valor, 2, '.', ',') : (string) $valor);
                $texto = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $texto);
                while ($pdf->GetStringWidth($texto) > $anchos[$i] - 2 && strlen($texto) > 0) { $texto = substr($texto, 0, -1); }
                $pdf->Cell($anchos[$i], 5, $texto, 1, 0, $numerico ? 'R' : 'L');
            }
            $pdf->Ln();
        }
        header('Content-Type: application/pdf');
        $pdf->Output('I', 'Inventario-' . $fecha . '.pdf');
    }

    static public function LeerFiltrosControlador($post)
    {
        $fecha = $post['endate'] ?? '';
        $corte = is_string($fecha) ? DateTimeImmutable::createFromFormat('!Y-m-d', $fecha) : false;
        if (!$corte || $corte->format('Y-m-d') !== $fecha || $fecha >= '9999-12-31') {
            throw new InvalidArgumentException('Selecciona una fecha de corte válida.');
        }
        $estado = filter_var($post['estado'] ?? 1, FILTER_VALIDATE_INT);
        if (!in_array($estado, array(1, 2, 3), true)) {
            throw new InvalidArgumentException('Selecciona activos, inactivos o ambos.');
        }
        $data = array('fecha' => $fecha, 'estado' => $estado,
            'soloexistencia' => isset($post['soloexistencia']), 'clasificaciones' => array());
        foreach (array('inicial', 'final') as $campo) {
            if (isset($post[$campo]) && (!is_string($post[$campo]) || strlen($post[$campo]) > 100)) {
                throw new InvalidArgumentException('Código de producto inválido.');
            }
            $data[$campo] = trim($post[$campo] ?? '');
        }
        $data['almacenes'] = self::LeerLista($post['almacenes'] ?? '[]');
        for ($numero = 1; $numero <= 6; $numero++) {
            $data['clasificaciones'][$numero] = self::LeerLista($post['clasificacion' . $numero] ?? '[]');
        }
        return $data;
    }

    static public function LeerLista($entrada)
    {
        $lista = is_string($entrada) ? json_decode($entrada, true) : null;
        if (!is_array($lista) || !array_is_list($lista) || count($lista) > 200) {
            throw new InvalidArgumentException('Selección de filtros inválida.');
        }
        foreach ($lista as &$id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id === false || $id < 0) {
                throw new InvalidArgumentException('Selección de filtros inválida.');
            }
        }
        unset($id);
        return array_values(array_unique($lista));
    }

    static public function PrepararFilasControlador($filas)
    {
        foreach ($filas as &$fila) {
            $campos = array('Impuesto', 'Existencia', 'Costo', 'Minimo', 'Maximo');
            for ($lista = 1; $lista <= 10; $lista++) { $campos[] = 'Precio' . $lista; }
            foreach ($campos as $campo) {
                $fila[$campo] = $fila[$campo] === null ? null : (float) $fila[$campo];
            }
            $fila['ReordenMin'] = $fila['Minimo'] === null ? null : max(0, $fila['Minimo'] - $fila['Existencia']);
            $fila['ReordenMax'] = $fila['Maximo'] === null ? null : max(0, $fila['Maximo'] - $fila['Existencia']);
        }
        unset($fila);
        return $filas;
    }
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    session_start();
    header('Content-Type: application/json; charset=utf-8');
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            throw new RuntimeException('Usa POST.');
        }
        if (empty($_SESSION['id_usuario']) || empty($_SESSION['database'])) {
            http_response_code(403);
            throw new RuntimeException('Inicia sesión y selecciona una empresa.');
        }
        require_once __DIR__ . '/../../configuracion/configuracion.php';
        require_once __DIR__ . '/../../configuracion/conexion.php';
        require_once __DIR__ . '/../../modelo/ModeloPrincipal.php';
        require_once __DIR__ . '/../../modelo/inventario/InventarioModelo.php';
        $reporte = ModeloPrincipal::BusacrReporteModelo('Inventario', 'Inventario');
        if (!$reporte || empty($_SESSION['lvl' . $reporte])) {
            http_response_code(403);
            throw new RuntimeException('No tienes permiso para Inventario. Vuelve a iniciar sesión si se acaba de habilitar.');
        }
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || empty($_SESSION['csrf_inventario']) || !hash_equals($_SESSION['csrf_inventario'], $token)) {
            http_response_code(403);
            throw new RuntimeException('La sesión cambió. Recarga la página.');
        }
        $accion = $_POST['accionajax'] ?? '';
        if ($accion === 'Catalogos') {
            $respuesta = InventarioModelo::CatalogosModelo();
        } elseif ($accion === 'Productos') {
            $termino = $_POST['term'] ?? '';
            if (!is_string($termino) || strlen($termino) > 100) {
                throw new InvalidArgumentException('Búsqueda inválida.');
            }
            $respuesta = InventarioModelo::ProductosModelo($termino);
        } elseif ($accion === 'ConsultarInventario' || $accion === 'ExportarPdf') {
            $data = InventarioControlador::LeerFiltrosControlador($_POST);
            $filas = InventarioModelo::ConsultarInventarioModelo($data);
            $respuesta = array('filas' => InventarioControlador::PrepararFilasControlador($filas), 'fecha' => $data['fecha']);
            if ($accion === 'ExportarPdf') {
                InventarioControlador::GenerarPdfControlador($respuesta['filas'], $data['fecha']);
                exit;
            }
        } else {
            throw new InvalidArgumentException('Acción inválida.');
        }
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
    } catch (Throwable $error) {
        if (http_response_code() < 400) {
            http_response_code($error instanceof InvalidArgumentException ? 400 : 500);
        }
        error_log('Inventario: ' . $error->getMessage());
        $mensaje = $error instanceof PDOException ? 'No se pudo consultar el inventario. Revisa el registro de errores.' : $error->getMessage();
        echo json_encode(array('error' => $mensaje), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
