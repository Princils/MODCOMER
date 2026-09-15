<?php 

    session_start();	
	require_once 'configuracion/configuracion.php';
	require_once 'controlador/ControladorPrincipal.php';
	require_once 'controlador/ConceptosReutilizablesControlador.php';
	require_once 'configuracion/links.php';
	require_once 'modelo/ModeloPrincipal.php';
	require_once 'modelo/ConceptosReutilizablesModelo.php';
	$ControladorPrincipal = new ControladorPrincipal(); 
	$ControladorPrincipal->VerificarPaginasControlador();
	$ControladorPrincipal->PaginaControlador();
?>