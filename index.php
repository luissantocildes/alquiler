<?php
	define('PATH_BASE', dirname(__FILE__) );

	include "configuracion.php";
	include "include/modulos.php";

	//ini_set("display_errors", true);
	ini_set("display_errors", false);
	ini_set("error_reporting", E_ALL);
	// Inicializa el sistema de módulos
	$modulos = new Modulo('.');
	// carga la plantilla especificada y le pasa el control
	$modulos->plantilla($plantilla);
	
?>
