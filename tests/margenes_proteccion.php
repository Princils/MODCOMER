<?php
// Ejecutar: php tests/margenes_proteccion.php
// No utiliza una base de datos real ni modifica productos.
require_once __DIR__ . '/../controlador/compras/MargenesProteccionControlador.php';

function verificar($condicion, $mensaje)
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

$base = array('endate' => '2026-01-10', 'cbx_listprice' => '4');
$filtros = MargenesProteccionControlador::LeerFiltrosControlador($base);
verificar($filtros['fecha'] === '10-01-2026', 'Formato de fecha SQL Server');
verificar(!$filtros['inactivos'] && !$filtros['suprimirceros'], 'Casillas desmarcadas');
foreach (array(
    array('endate' => '2026-02-30'),
    array('cbx_listprice' => '6'),
    array('cbx_listprice' => '4; DROP TABLE admProductos'),
    array('cbx_LineGeneral' => '["1 OR 1=1"]'),
    array('cbx_LineGeneral' => 'null'),
    array('cbx_LineGeneral' => array(1))
) as $invalido) {
    try {
        MargenesProteccionControlador::LeerFiltrosControlador(array_merge($base, $invalido));
        throw new RuntimeException('Se aceptó un filtro inválido');
    } catch (InvalidArgumentException $esperado) {
    }
}

$casos = array(
    array(100, 80, 20, 20, 'CORRECTO'),
    array(100, 79.4, 22, 20.5, 'REQUIERE BAJAR'),
    array(100, 79.1, 15, 20.5, 'REQUIERE SUBIR'),
    array(100, 80.5, 19.5, 19.5, 'CORRECTO'),
    array(100, 120, 10, -20, 'REVISIÓN'),
    array(0, 80, 10, 0, 'REVISIÓN'),
    array(100, null, 10, 0, 'REVISIÓN'),
    array(100, 99.3, 2, 0.5, 'REQUIERE BAJAR')
);
foreach ($casos as $caso) {
    $fila = array('IdProducto' => 1, 'Precio' => $caso[0], 'Costo' => $caso[1], 'MargenActual' => $caso[2]);
    $resultado = MargenesProteccionControlador::CalcularMargenesControlador(array($fila));
    verificar(abs($resultado[0]['MargenRecomendado'] - $caso[3]) < 0.000001, 'Margen recomendado incorrecto');
    verificar($resultado[0]['Estatus'] === $caso[4], 'Estatus incorrecto');
    $cambios = MargenesProteccionControlador::PrepararCambiosControlador($resultado, 'automatico');
    if ($caso[4] === 'REVISIÓN' || $caso[3] < 1 || $caso[2] == $caso[3]) {
        verificar(count($cambios) === 0, 'Se propuso un cambio automático no elegible');
    }
    $ceros = MargenesProteccionControlador::PrepararCambiosControlador($resultado, 'ceros');
    verificar(count($ceros) === 1 && $ceros[0]['MargenNuevo'] === 0, 'Restablecimiento a cero incorrecto');
}

class Conexion
{
    static public $prueba;
    static public function ConnecBdDinamico()
    {
        return self::$prueba;
    }
}
class ConexionPrueba
{
    public $transaccion = false;
    public $confirmada = false;
    public $revertida = false;
    public $consultas = array();
    public $fallar = false;
    public function setAttribute($nombre, $valor) {}
    public function beginTransaction() { $this->transaccion = true; }
    public function inTransaction() { return $this->transaccion; }
    public function commit() { $this->confirmada = true; $this->transaccion = false; }
    public function rollBack() { $this->revertida = true; $this->transaccion = false; }
    public function prepare($query) { return $this; }
    public function execute($params) { $this->consultas[] = $params; }
    public function rowCount() { return $this->fallar && count($this->consultas) === 2 ? 0 : 1; }
}
require_once __DIR__ . '/../modelo/compras/MargenesProteccionModelo.php';
$cambios = array(
    array('IdProducto' => 10, 'MargenActual' => 15, 'MargenNuevo' => 20),
    array('IdProducto' => 20, 'MargenActual' => 10, 'MargenNuevo' => 0)
);
Conexion::$prueba = new ConexionPrueba();
verificar(MargenesProteccionModelo::AplicarMargenesModelo($cambios) === 2, 'Cantidad aplicada');
verificar(Conexion::$prueba->confirmada, 'Falta confirmar la transacción');
verificar(Conexion::$prueba->consultas[0] === array(20, 10, 15), 'Parámetros del cambio');
Conexion::$prueba = new ConexionPrueba();
Conexion::$prueba->fallar = true;
try {
    MargenesProteccionModelo::AplicarMargenesModelo($cambios);
    throw new LogicException('Se aceptó un conflicto concurrente');
} catch (RuntimeException $esperado) {
    verificar(Conexion::$prueba->revertida && !Conexion::$prueba->confirmada, 'No se revirtió la actualización completa');
}

$filaPdf = array(
    'Codigo' => 'P001', 'Producto' => 'Producto de prueba', 'Unidad' => 'PZA',
    'Precio' => 100.0, 'Costo' => 80.0, 'MargenActual' => 20.0,
    'MargenRecomendado' => 20.0, 'Estatus' => 'CORRECTO',
    'Precio1' => 100.0, 'Precio2' => 100.0, 'Precio3' => 100.0,
    'Precio4' => 100.0, 'Precio5' => 100.0, 'Existencia' => 1.0
);
ob_start();
MargenesProteccionControlador::GenerarPdfControlador(array_fill(0, 120, $filaPdf), $filtros);
$pdf = ob_get_clean();
verificar(substr($pdf, 0, 5) === '%PDF-', 'Exportación PDF');
verificar(strpos($pdf, '/Count 3') !== false, 'Paginación del PDF');
echo "OK: filtros, cálculo, estados, vista previa, transacción, conflicto concurrente y PDF.\n";
