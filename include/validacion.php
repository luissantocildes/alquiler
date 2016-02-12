<?php

// Protección contra ejecución incorrecta
defined('PATH_BASE') or die();

/***************************
 * Verifica si existe una cadena de validación en la base de datos
 * Parámetros:
 *	$cadenaValidacion = cadena a buscar
 * Devuelve un array con el id del usuario a validar y la cadena de validación. FALSE si ha ocirrido un error
 ***************************/
function existe_cadena_validacion($cadenaValidacion) {
	global $modulos;
	
	if (is_string($cadenaValidacion) && $cadenaValidacion) {
		$resultado = $modulos->resultado_sql ('SQL_BUSCA_VERIFICACION', $cadenaValidacion);
		if (is_array($resultado))
			if (count($resultado))
				return $resultado[0];
			else return Array();
		else return false;
	} else return false;
}

/*****************************
 * Elimina una cadena de validacion
 * Parámetros:
 *	$cadenaValidacion = Cadena de validación a eliminar
 * Devuelve true si todo a sido correcto
 *****************************/
function elimina_datos_validacion ($cadenaValidacion) {
	global $modulos;
	
	$datosValidacion = existe_cadena_validacion($cadenaValidacion);
	if (is_array($datosValidacion) && count ($datosValidacion)) {
		$resultado = $modulos->inserta_sql('SQL_BORRA_VERIFICACION', $cadenaValidacion);
		if ($modulos->errorSql)
			return false;
		else return true;
	}
}
?>