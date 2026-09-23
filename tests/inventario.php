<?php
require __DIR__.'/../controlador/inventario/InventarioControlador.php';
function verificar($condicion, $mensaje) { if (!$condicion) throw new RuntimeException($mensaje); echo "OK: $mensaje\n"; }
function rechaza($post) { try { InventarioControlador::LeerFiltrosControlador($post); } catch (InvalidArgumentException $e) {return true;} return false; }
$base=array('endate'=>'2026-01-10','estado'=>'1');
verificar(rechaza(array_replace($base,array('endate'=>'2026-02-30'))), 'rechaza fecha inexistente');
verificar(rechaza(array_replace($base,array('estado'=>'4'))), 'rechaza estado desconocido');
verificar(rechaza($base+array('almacenes'=>'["1); DROP TABLE x"]')), 'rechaza identificadores manipulados');
verificar(rechaza($base+array('clasificacion1'=>'{"a":1}')), 'rechaza objeto en lugar de lista');
$data=InventarioControlador::LeerFiltrosControlador($base+array('almacenes'=>'[2,2,3]', 'soloexistencia'=>'on'));
verificar($data['almacenes']===array(2,3) && $data['soloexistencia'], 'normaliza seleccion multiple');
$fila=array('Impuesto'=>16,'Existencia'=>3,'Costo'=>null,'Minimo'=>5,'Maximo'=>10);
for($n=1;$n<=10;$n++)$fila['Precio'.$n]=$n;
$resultado=InventarioControlador::PrepararFilasControlador(array($fila))[0];
verificar($resultado['ReordenMin']===2.0 && $resultado['ReordenMax']===7.0,'calcula faltantes hasta minimo y maximo');
verificar($resultado['Costo']===null && $resultado['Precio10']===10.0,'distingue costo ausente y conserva precio 10');
$fila['Existencia']=20;$fila['Minimo']=null;$fila['Maximo']=null;
$resultado=InventarioControlador::PrepararFilasControlador(array($fila))[0];
verificar($resultado['ReordenMin']===null,'sin almacen unico no inventa reorden');
