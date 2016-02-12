<?php

// Protección contra ejecución incorrecta
defined('PATH_BASE') or die();

require ('peticiones_sql.php');
require ('MDB2.php');

function conexion_db() {
	global $dsn;

	$db =& MDB2::factory($dsn);
	if (PEAR::isError($db)) {
   		die($db->getMessage());
	    return false;
	}
	$db->setFetchMode(MDB2_FETCHMODE_ASSOC);
	return $db;
}

function desconexion_db ($idConexion) {
	$idConexion->disconnect();
}

function procesaSql ($sql) {
	global $tablePrefix;
	
	return str_replace ('#_', $tablePrefix.'_', $sql);
}

function procesa_sql ($sql) {
	return procesaSql($sql);
}

function mostrar_error_sql ($objeto) {
	echo 'Standard Message: ' . $objeto->getMessage() . "<br>"; 
	echo 'Standard Code: ' . $objeto->getCode() . "<br>"; 
	echo 'DBMS/User Message: ' . $objeto->getUserInfo() . "<br>"; 
	echo 'DBMS/Debug Message: ' . $objeto->getDebugInfo() . "<br>"; 
}

/**************************************
 * Genera una sentencia SQL y la ejecuta
 *
 */
function ejecuta_select ($campos, $tablas, $condiciones = Array(), $operador = "AND", $orden = Array(), $limite = Array()) {
	$select = 'SELECT ';

	// Prepara los campos a leer
	foreach ($campos as $clave=>$valor) {
		if (!is_numeric($clave))
			$campos[$clave] = "$valor as $clave";
	}
	$select .= implode (",", $campos) . " FROM ";
	
	// Prepara las tablas de las que leer
	foreach ($tablas as $clave=>$valor) {
		if (!is_numeric($clave))
			$tablas[$clave] = "$valor $clave";
	}
	$select .= implode (",", $tablas);
	
	// Prepara las condiciones de los where
	if (count($condiciones)) {
		foreach ($condiciones as $clave=>$valor) {
			if (!is_numeric($clave))
				$condiciones[$clave] = "$valor $clave";
		}
		$select .= " WHERE " . implode (" $operador ", $condiciones);
	}
	
	// A�ade el ORDER BY
	if (count ($orden)) {
		$select .= " ORDER BY " . implode (",", $orden);
	}
	
	// Y el limit
	if (count ($limite)) {
		$select .= " LIMIT " . implode (",", $limite); 
	}
	return $select;
}

?>