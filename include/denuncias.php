<?php

// Protección contra ejecución incorrecta
defined('PATH_BASE') or die();

define ('C_DENUNCIA_PENDIENTE', 0);
define ('C_DENUNCIA_ACEPTADA', 1);
define ('C_DENUNCIA_DENEGADA', 2);

require_once ('peticiones_sql.php');

/*************************************************
 * Devuelve los datos de una denuncia
 * Parámetros:
 *	$idAnuncio = id de la denuncia
 * Devuelve un array con los datos de la denuncia. False en caso contrario
 *************************************************/
function leer_denuncia($idDenuncia) {
	global $modulos;

	if (is_numeric($idDenuncia)) {
		$resultado = $modulos->resultado_sql ('SQL_DENUNCIA', $idDenuncia);
		if (!PEAR::isError($modulos->errorSql) && count($resultado)) {
			return $resultado[0];
		} else
			return false;
	} else
		return false;
}

/***********************************************
 * Genera una nueva denuncia vacía y devuelve el ID
 * Parámetros: ninguno
 * Devuelve el id de la nueva denuncia o false en caso de error
 ***********************************************/
function nueva_denuncia() {
	global $modulos;
	
	$id = $modulos->db->nextId('denuncia');
	// Verifica que el ID esté disponible
	//$resultado = leer_denuncia($id);
	//while (is_array($resultado)) { // Mientras encuentre denuncias, entonces sigue buscando nuevos ID
	//	$id = $modulos->db->nextId('denuncia');
	//	$resultado = leer_denuncia($id);
	//}
	
	// Ok, ahora genera el anuncio vacío
	//$resultado = $modulos->inserta_sql('SQL_NUEVA_DENUNCIA_VACIA', $id);
	return $id;
}

/**************************************
 * Inserta una denuncia en la base de datos
 * Parámetros:
 *	$datosDenuncia: datos de la denuncia
 * Devuelve el id de la nueva denuncia o FALSE en caso de error
 **************************************/
function inserta_denuncia ($datosDenuncia) {
	global $modulos;
	
	$idDenuncia = nueva_denuncia();
	if ($idDenuncia) {
		$resultado = $modulos->inserta_sql('SQL_NUEVA_DENUNCIA', Array($datosDenuncia['asunto'], $datosDenuncia['contenido'], 
																$datosDenuncia['idUsuario'], $datosDenuncia['nombre'], 
																$datosDenuncia['apellidos'], $datosDenuncia['email'], 
																$datosDenuncia['ip'], C_DENUNCIA_PENDIENTE, 
																$datosDenuncia['idAnuncio'], $idDenuncia));
		if ($modulos->errorSql)
			return false;
		else return $idDenuncia;
	} else
		return false;
}

/***********************************
 * Devuelve el total de denuncias
 * Parámetros:
 *	$estado: TRUE: Cuenta todas las denuncias.
 *			 C_DENUNCIA_PENDIENTE: Las pendientes
 * 			 C_DENUNCIA_ACEPTADA: Las aceptadas
 *			 C_DENUNCIA_DENEGADA: Las denegadas
 * Devuelve el nº de denuncias
 **********************************/
function total_denuncias ($estado = TRUE) {
	global $modulos;
	
	if ($estado === TRUE) {
		$resultado = $modulos->resultado_sql('SQL_TOTAL_DENUNCIAS', Array());
		if ($modulos->errorSql)
			return false;
		else return $resultado[0]['total'];
	} else {
		return false;
	}
}

/************************************************
 * Devuelve una lista con las denuncias
 * Parámetros:
 *	$inicio = elemento por el que comenzar el listado
 *	$total = total de mensaje a buscar
 * Devuelve un array con las denuncias. False en caso de error.
 ************************************************/
function lee_denuncias($total = 0, $primero = null) {
	global $modulos;

	if ($total)
		$modulos->db->setLimit($total, $primero);
	$listaDenuncias = $modulos->resultado_sql('SQL_DENUNCIAS', Array());
	$modulos->db->setLimit(0);
	if ($modulos->errorSql)
		return false;
	else return $listaDenuncias;
}

?>