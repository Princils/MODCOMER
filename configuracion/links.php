<?php 
require_once 'conexion.php';
class Paginas extends Conexion{
	static public function RutaPagina($link){
		if ($link == 'login') {
			$modulo = 'vista/modulos/login/iniciarsesion.php';
		}else if ($link == 'conexion_bd') {
			$modulo = 'vista/modulos/configuracion/conexionbd.php';
		}else if ($link == 'conceptosportienda') {
			$modulo = 'vista/modulos/configuracion/conceptosportienda.php';
		}else if ($link == 'SeleccionarEmpresa') {
			$modulo = 'vista/modulos/login/SeleccionarEmpresa.php';
		}else if ($link == 'usuariosypermisos') {
			$modulo = 'vista/modulos/configuracion/usuariosypermisos.php';
		}else if ($link == 'Dashboard') {
			$modulo = 'vista/modulos/dashboard/dashboard.php';
		}else if ($link == 'ventasutilidadpordocumentos') {
			$modulo = 'vista/modulos/reportes/ventas/utilidadpordocumentos.php';
		}else if ($link == 'ventasutilidadporproductos') {
			$modulo = 'vista/modulos/reportes/ventas/utilidadporproductos.php';
		}else if ($link == 'ventasutilidadporagente') {
			$modulo = 'vista/modulos/reportes/ventas/utilidadporagente.php';
		}else if ($link == 'asignarnuevovencimiento') {
			$modulo = 'vista/modulos/configuracion/asignarnuevovencimiento.php';
		}else if ($link == 'ventasutilidadporclasificacion') {
			$modulo = 'vista/modulos/reportes/ventas/utilidadporclasificacion.php';
		}else if ($link == 'inventario') {
			$modulo = 'vista/modulos/reportes/inventario/inventario.php';
		}else if ($link == 'comprasmargenesproteccion') {
			$modulo = 'vista/modulos/reportes/compras/margenesproteccion.php';
		}else{
			$modulo = 'vista/modulos/login/iniciarsesion.php';
		}
		return $modulo;
	}
}
