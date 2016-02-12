<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

/*************************
 * Funciones para el control de las categorias
 *************************/
 
/**************************
 * Verifica si una categor�a existe
 * Entrada: $idCategoria: id de la categor�a a verificar
 * Salida: TRUE si la categoria existe, FALSE si no existe o si $idCategor�a no es num�rico
 **************************/
function existe_categoria ($idCategoria) {
	global $modulos;
	
	if (is_numeric($idCategoria)) {
		$categoria = $modulos->resultado_sql ('SQL_CATEGORIA', $idCategoria);
		if (is_array($categoria) && count ($categoria) == 1)
			return true;
		else return false;
	} else
		return false;
}

/****************************
 * Devuelve los datos de la categoria
 * Entrada: $idCategoria: id de la categoria a leer
 * Salida: Array con los datos de la categor�a, FALSE si la categor�a no existe o hay un error
 *****************************/
function datos_categoria ($idCategoria) {
	global $modulos;

	if (is_numeric($idCategoria)) {
		$datosCategoria = $modulos->resultado_sql ('SQL_CATEGORIA', $idCategoria);
		if (is_array($datosCategoria) && count($datosCategoria) == 1)
			return $datosCategoria;
		else return FALSE;
	} else return FALSE;
}

/*******************************
 * Devuelve un array con todas las categorias
 * Salida: Array con las categorias ordenadas por id y padre
 *******************************/
function lista_categorias($padre=0){
	global $modulos;
	$resultados = Array();
	$listaCategorias = $modulos->resultado_sql('SQL_CATEGORIA_PADRE', $padre);
	foreach($listaCategorias as $categoria) {
		$resultados[] = $categoria;
		$resultados = array_merge($resultados, lista_categorias($categoria['id']));
	}
	return $resultados;
}

/*******************************
 * Cuenta el total de subcategorias
 *******************************/
function total_subcategorias($padre=0) {
	global $modulos;

	$totalCategorias = $modulos->resultado_sql('SQL_TOTAL_SUBCATEGORIAS', $padre);
	
	if (PEAR::isError ($totalCategorias))
		return FALSE;
	else return $totalCategorias[0]['total'];
}
?>