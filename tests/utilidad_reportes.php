<?php
// php tests/utilidad_reportes.php (sin conexión a una empresa).
class Conexion
{
    public static $consultas = [];
    public static $filas = [];
    static public function ConnecBdDinamico() { return new self(); }
    public function prepare($sql) { self::$consultas[] = [$sql, []]; return $this; }
    public function execute($params) { self::$consultas[count(self::$consultas)-1][1] = $params; return true; }
    public function fetchAll($modo) { return self::$filas; }
}
require __DIR__.'/../modelo/ventasconutilidad/UtilidadPorProductosModelo.php';
require __DIR__.'/../modelo/ventasconutilidad/UtilidadPorAgenteModelo.php';
require __DIR__.'/../modelo/ventasconutilidad/UtilidadPorDocumentosModelo.php';
require __DIR__.'/../controlador/ventasconutilidad/UtilidadPorProductosControlador.php';
function verificar($condicion, $mensaje) { if (!$condicion) throw new RuntimeException($mensaje); }
function cerca($a,$b) { return abs($a-$b)<0.000001; }
$filas=array_fill(0,250,['neto'=>'10.005','costo'=>'4.001','Descuento'=>'0.123']);
$t=UtilidadRespuesta::Totales($filas,'neto','costo');
verificar($t['filas']===250 && cerca($t['neto'],2501.25), 'Debe sumar más de una página sin redondear las filas.');
verificar(cerca($t['utilidad'],1501), 'La utilidad debe derivarse de los importes completos.');
$t=UtilidadRespuesta::Totales([['neto'=>10,'costo'=>6,'Descuento'=>0],['neto'=>-10,'costo'=>-6,'Descuento'=>0]],'neto','costo');
verificar($t['margen']===0, 'Una venta y su devolución no deben dividir por cero.');
$codigo="A' OR 1=1 --";
$f=[$codigo,$codigo,'01-01-2026','10-01-2026',1,1,0,[3,4],[7],[],[],[],[],[],'A18'];
UtilidadPorProductosModelo::InsertarTblUtilidadPorProductosModelo($f);
[$sql,$params]=end(Conexion::$consultas);
verificar(strpos($sql,$codigo)===false && in_array($codigo,$params,true), 'Los códigos se enlazan, nunca se concatenan.');
verificar(in_array('A18',$params,true) && in_array(7,$params,true), 'Los filtros se deben aplicar a todos los productos, incluidos paquetes.');
UtilidadPorProductosModelo::EjecutarSubConsultaUtilidadPorProductosModelo([$codigo,$f[2],$f[3],[3,4],'A18']);
[$sql,$params]=end(Conexion::$consultas);
verificar(in_array('A18',$params,true), 'El desglose debe conservar el agente.');
UtilidadPorDocumentosModelo::InsertarTblUtilidadPorDocumentosModelo(0,$f[2],$f[3],[]);
[$sql,$params]=end(Conexion::$consultas);
verificar(strpos($sql,'1 = 0')!==false, 'Sin conceptos no se deben mostrar todos los documentos.');
// Contrato completo del controlador: filas vacías y más de 100 resultados.
$_POST=['startproductval'=>'A','endproductval'=>'Z','startdate'=>'2026-01-01','endate'=>'2026-01-10','checkboxValues'=>'[3]'];
foreach ([0,150] as $cantidad) {
    Conexion::$filas=array_fill(0,$cantidad,['Código'=>'P01','Producto'=>'Producto de prueba','Unidades'=>1,'Importe Ventas'=>10.005,'Importe Costo'=>4.001,'Descuento'=>0,'tipo'=>2]);
    $antes=count(Conexion::$consultas);
    ob_start(); UtilidadPorProductosControlador::InsertarTblUtilidadPorProductosControlador(); $json=json_decode(ob_get_clean(),true);
    verificar(is_array($json) && $json['totales']['filas']===$cantidad,'Respuesta JSON inválida.');
    verificar(substr_count($json['html'],'<tr ')===$cantidad, 'Debe incluir todas las filas o un cuerpo vacío válido.');
    verificar(count(Conexion::$consultas)===$antes+1,'Los paquetes no deben generar consultas por fila.');
    verificar(cerca($json['totales']['neto'],$cantidad*10.005),'Los totales no deben depender del HTML redondeado.');
}
echo "OK: totales, ceros, devoluciones, filtros enlazados, JSON y paquetes sin consultas por fila.\n";
