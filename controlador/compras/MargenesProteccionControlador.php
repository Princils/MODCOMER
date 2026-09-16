<?php

class MargenesProteccionControlador
{
    // Lectura y validación de los filtros enviados por el formulario.
    static public function LeerFiltrosControlador($post)
    {
        $fecha = isset($post['endate']) && is_string($post['endate']) ? $post['endate'] : '';
        $fechaValida = DateTime::createFromFormat('!Y-m-d', $fecha);
        if (!$fechaValida || $fechaValida->format('Y-m-d') !== $fecha) {
            throw new InvalidArgumentException('Selecciona una fecha de corte válida.');
        }
        $lista = filter_var($post['cbx_listprice'] ?? null, FILTER_VALIDATE_INT);
        if ($lista === false || $lista < 1 || $lista > 5) {
            throw new InvalidArgumentException('Selecciona una lista de precios del 1 al 5.');
        }
        $data = array(
            'fecha' => $fechaValida->format('d-m-Y'),
            'lista' => $lista,
            'inactivos' => isset($post['noinac']),
            'suprimirceros' => isset($post['chxno0']),
            'inicial' => '',
            'final' => '',
            'clasificaciones' => array()
        );
        foreach (array('inicial' => 'startproductval', 'final' => 'endproductval') as $campo => $nombre) {
            if (isset($post[$nombre]) && !is_string($post[$nombre])) {
                throw new InvalidArgumentException('Código de producto inválido.');
            }
            $data[$campo] = trim($post[$nombre] ?? '');
        }
        $clasificaciones = array(
            1 => 'cbx_LineGeneral',
            2 => 'cbx_LineDetailed',
            3 => 'cbx_TypeClassification',
            4 => 'cbx_CommissionIndicator',
            5 => 'cbx_Rotation',
            6 => 'cbx_DailyReview'
        );
        foreach ($clasificaciones as $numero => $campo) {
            if (isset($post[$campo]) && !is_string($post[$campo])) {
                throw new InvalidArgumentException('Clasificación inválida.');
            }
            $valores = isset($post[$campo]) && is_string($post[$campo])
                ? json_decode($post[$campo], true) : array();
            if (!is_array($valores)) {
                throw new InvalidArgumentException('Clasificación inválida.');
            }
            foreach ($valores as &$valor) {
                $valor = filter_var($valor, FILTER_VALIDATE_INT);
                if ($valor === false || $valor < 0) {
                    throw new InvalidArgumentException('Clasificación inválida.');
                }
            }
            unset($valor);
            $data['clasificaciones'][$numero] = array_values(array_unique($valores));
        }
        return $data;
    }

    // El sistema anterior redondea hacia abajo en pasos de medio punto.
    static public function CalcularMargenesControlador($filas)
    {
        foreach ($filas as &$fila) {
            foreach (array('Precio', 'Costo', 'MargenActual', 'Precio1', 'Precio2', 'Precio3', 'Precio4', 'Precio5', 'Existencia') as $campo) {
                $fila[$campo] = (float) ($fila[$campo] ?? 0);
            }
            $precio = round($fila['Precio'], 2);
            $costo = round($fila['Costo'], 2);
            $margen = ($precio == 0 || $costo == 0) ? 0 : (($precio - $costo) / $precio) * 100;
            $revision = $margen <= 0 || $precio <= 0 || $costo <= 0;
            $fila['MargenRecomendado'] = $revision ? $margen : floor($margen * 2) / 2;
            $fila['Estatus'] = 'CORRECTO';
            if ($revision) {
                $fila['Estatus'] = 'REVISIÓN';
            } elseif ($fila['MargenRecomendado'] < $fila['MargenActual']) {
                $fila['Estatus'] = 'REQUIERE BAJAR';
            } elseif ($fila['MargenRecomendado'] > $fila['MargenActual']) {
                $fila['Estatus'] = 'REQUIERE SUBIR';
            }
        }
        unset($fila);
        return $filas;
    }

    static public function PrepararCambiosControlador($filas, $tipo)
    {
        $cambios = array();
        foreach ($filas as $fila) {
            $nuevo = $tipo === 'ceros' ? 0 : $fila['MargenRecomendado'];
            // Actualización automática: lista 4, margen vigente no negativo y recomendado >= 1.
            if ($tipo !== 'ceros' && ($fila['MargenActual'] < 0 || $fila['Precio'] <= 0
                || $fila['Costo'] <= 0 || $nuevo < 1 || $fila['Estatus'] === 'REVISIÓN')) {
                continue;
            }
            if (abs($fila['MargenActual'] - $nuevo) < 0.000001) {
                continue;
            }
            $fila['MargenNuevo'] = $nuevo;
            $cambios[] = $fila;
        }
        return $cambios;
    }

    static public function GenerarPdfControlador($filas, $data)
    {
        require_once __DIR__ . '/../../vista/vendor/fpdf/fpdf.php';
        $pdf = new FPDF('L', 'mm', 'A3');
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(false);
        $columnas = array(
            array('Codigo', 'Código', 27), array('Producto', 'Producto', 83),
            array('Unidad', 'UM', 18), array('Precio', 'Precio', 24),
            array('Costo', 'Costo', 24), array('MargenActual', 'Margen actual', 25),
            array('MargenRecomendado', 'Recomendado', 25), array('Estatus', 'Estatus', 36),
            array('Precio1', 'Precio 1', 23), array('Precio2', 'Precio 2', 23),
            array('Precio3', 'Precio 3', 23), array('Precio4', 'Precio 4', 23),
            array('Precio5', 'Precio 5', 23), array('Existencia', 'Existencia', 25)
        );
        $texto = function ($valor) {
            return iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $valor);
        };
        $encabezado = function () use ($pdf, $data, $columnas, $texto) {
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 8, $texto('Márgenes de protección - Corte: ' . $data['fecha'] . ' - Lista ' . $data['lista']), 0, 1);
            $pdf->SetFont('Arial', 'B', 8);
            foreach ($columnas as $columna) {
                $pdf->Cell($columna[2], 7, $texto($columna[1]), 1);
            }
            $pdf->Ln();
        };
        $encabezado();
        foreach ($filas as $fila) {
            if ($pdf->GetY() > 275) {
                $encabezado();
            }
            $pdf->SetFont('Arial', '', 7);
            foreach ($columnas as $columna) {
                $valor = $fila[$columna[0]];
                $numerico = is_float($valor) || is_int($valor);
                $valor = $texto($numerico ? number_format($valor, 2, '.', ',') : $valor);
                while ($pdf->GetStringWidth($valor) > $columna[2] - 2) {
                    $valor = substr($valor, 0, -1);
                }
                $pdf->Cell($columna[2], 5, $valor, 1, 0, $numerico ? 'R' : 'L');
            }
            $pdf->Ln();
        }
        header('Content-Type: application/pdf');
        $pdf->Output('I', 'MargenesProteccion.pdf');
    }
}

// Solicitudes AJAX: la empresa y los permisos se toman de la sesión del sistema.
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    session_start();
    header('Content-Type: application/json; charset=utf-8');
    try {
        if (empty($_SESSION['id_usuario']) || empty($_SESSION['database'])) {
            http_response_code(403);
            throw new RuntimeException('Inicia sesión y selecciona una empresa.');
        }
        require_once __DIR__ . '/../../configuracion/configuracion.php';
        require_once __DIR__ . '/../../configuracion/conexion.php';
        require_once __DIR__ . '/../../modelo/ModeloPrincipal.php';
        require_once __DIR__ . '/../../modelo/compras/MargenesProteccionModelo.php';
        $idReporte = ModeloPrincipal::BusacrReporteModelo('Revisión de Margenes de Protección', 'Compras');
        if (!$idReporte || empty($_SESSION['lvl' . $idReporte])) {
            http_response_code(403);
            throw new RuntimeException('No tienes permiso para este reporte. Vuelve a iniciar sesión si se acaba de habilitar.');
        }
        $token = $_POST['csrf'] ?? '';
        if (!is_string($token) || empty($_SESSION['csrf_margenes']) || !hash_equals($_SESSION['csrf_margenes'], $token)) {
            http_response_code(403);
            throw new RuntimeException('La sesión cambió. Recarga la página.');
        }
        $accion = $_POST['accionajax'] ?? '';
        if ($accion === 'AplicarMargenes') {
            $vistaPrevia = $_SESSION['vista_previa_margenes'] ?? null;
            $idVista = $_POST['vista'] ?? '';
            if (!$vistaPrevia || !is_string($idVista) || !hash_equals($vistaPrevia['id'], $idVista)
                || $vistaPrevia['empresa'] !== txtdatabase || $vistaPrevia['vence'] < time()) {
                throw new InvalidArgumentException('La vista previa venció. Genera una nueva antes de aplicar.');
            }
            $cantidad = MargenesProteccionModelo::AplicarMargenesModelo($vistaPrevia['cambios']);
            unset($_SESSION['vista_previa_margenes']);
            $respuesta = array('actualizados' => $cantidad);
        } else {
            if (!in_array($accion, array('ConsultarMargenes', 'PdfMargenes', 'PrepararCeros', 'PrepararActualizacion'), true)) {
                throw new InvalidArgumentException('Acción inválida.');
            }
            $post = $_POST;
            if ($accion === 'PrepararActualizacion') {
                // Conserva la selección de productos del formulario; calcula con lista 4 y corte de hoy.
                $hoy = new DateTimeImmutable('now', new DateTimeZone('America/Mazatlan'));
                $post['endate'] = $hoy->format('Y-m-d');
                $post['cbx_listprice'] = '4';
            }
            $data = MargenesProteccionControlador::LeerFiltrosControlador($post);
            $filas = MargenesProteccionModelo::ConsultarMargenesModelo($data);
            $filas = MargenesProteccionControlador::CalcularMargenesControlador($filas);
            $respuesta = array('filas' => $filas);
            if ($accion === 'PdfMargenes') {
                MargenesProteccionControlador::GenerarPdfControlador($filas, $data);
                exit;
            }
            if ($accion === 'PrepararCeros' || $accion === 'PrepararActualizacion') {
                $tipo = $accion === 'PrepararCeros' ? 'ceros' : 'automatico';
                $cambios = MargenesProteccionControlador::PrepararCambiosControlador($filas, $tipo);
                $idVista = bin2hex(random_bytes(24));
                $_SESSION['vista_previa_margenes'] = array(
                    'id' => $idVista,
                    'empresa' => txtdatabase,
                    'vence' => time() + 600,
                    'cambios' => $cambios
                );
                $respuesta = array('filas' => $cambios, 'vista' => $idVista);
            }
        }
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE);
    } catch (Throwable $error) {
        if (http_response_code() < 400) {
            http_response_code($error instanceof InvalidArgumentException ? 400 : 500);
        }
        error_log('Márgenes de protección: ' . $error->getMessage());
        $mensaje = $error instanceof PDOException ? 'No se pudo ejecutar la consulta. Revisa el registro de errores.' : $error->getMessage();
        echo json_encode(array('error' => $mensaje), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
