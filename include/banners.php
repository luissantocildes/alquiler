<?php

defined('PATH_BASE') or die();

require_once ('anuncios.php');

define ('C_BANNER_SUPERIOR', 0);
define ('C_BANNER_LATERAL', 1);

define ('C_BANNER_PUBLICADO', 1);
define ('C_BANNER_NO_PUBLICADO', 0);

/*******************************
 * Cuenta el total de banners
 * Parametros: ninguno
 * Devuelve el total de banners o false en caso de error
 *******************************/
function total_banners() {
		global $modulos;

	$totalBanners = $modulos->resultado_sql('SQL_TOTAL_BANNERS', Array());
	if (PEAR::isError ($totalBanners))
		return FALSE;
	else return $totalBanners[0]['total'];
}

/***********************************************
 * Guarda los datos del nuevo banner.
 * Par?metros: Datos del banner
 * Salida: Id del nuevo banner
 ***********************************************/
function grabar_banner($datosBanner, $publicado = true, $listaImagenes = Array()) {
	global $modulos, $dirImagenes, $dirMinis;

	// guarda el banner en la bbdd
	$datosBanner['titulo'] = htmlentities($datosBanner['titulo'], ENT_QUOTES, 'UTF-8');
	$datosBanner['url'] = urlencode($datosBanner['url']);
	if ($datosBanner['fecha_fin'] == '')
		$datosBanner['fecha_fin'] = 0;
	if ($datosBanner['id'] == -1)
		$id = nuevo_banner();
	else $id = $datosBanner['id'];

	$resultado = $modulos->inserta_sql ('SQL_NUEVO_BANNER', Array($datosBanner['fecha_fin'], $datosBanner['tipo'],
										$datosBanner['categoria'], $datosBanner['titulo'], $datosBanner['url'],
										$publicado, $datosBanner['imagen'], $datosBanner['tipo_imagen'],
										$id));

	if (PEAR::isError($resultado)) {
		return false;
	} else { // Si todo va bien, entonces guarda el nombre de la imagen en la bbdd
		if (is_array($listaImagenes) && !$listaImagenes[0]['error']) {
			// Se borra la otra imagen del banner si este tiene otra
			$resultado = $modulos->resultado_sql('SQL_IMAGENES_ANUNCIO', Array($id, 'b'));
			if (is_array($resultado) && !$modulos->error_sql() && count($resultado)) {
				unlink($dirImagenes.$resultado[0]['fichero']);
				unlink($dirMinis.$resultado[0]['fichero']);
				$resultado = $modulos->inserta_sql('SQL_BORRAR_IMAGEN', $resultado[0]['id']);
			}
		
			$resultado = $modulos->inserta_sql ('SQL_NUEVA_IMAGEN_ANUNCIO', Array($id, $listaImagenes[0]['name'], 'b'));
			// Ahora se busca la primer imagen del auncio en la base de datos y la asigna como imagen principal
			$imagenes = leer_imagenes_anuncio($id, 'b');
			$aux = strtolower(substr(strrchr($imagenes[0]['fichero'], '.'), 1));
			$tipo = $aux == 'swf' ? 1 : 0;
			if (is_array($imagenes) && count($imagenes))
				$resultado = $modulos->inserta_sql('SQL_BANNER_MODIFICA_IMAGEN', Array($imagenes[0]['id'], $tipo, $id));
		} else
			return $id;
	}
}

/***********************************************
 * Genera un nuevo banner vac�o y devuelve el ID
 * Par�metros: ninguno
 * Devuelve el id del nuevo banner o false en caso de error
 ***********************************************/
function nuevo_banner() {
	global $modulos;
	
	$id = $modulos->db->nextId('banner');
	// Verifica que el ID est� disponible
	$resultado = leer_banner($id);
	while (is_array($resultado)) { // Mientras encuentre anuncios, entonces sigue buscando nuevos ID
		$id = $modulos->db->nextId('banner');
		$resultado = leer_banner($id);
	}
	
	// Ok, ahora genera el anuncio vac�o
	$resultado = $modulos->inserta_sql('SQL_NUEVO_BANNER_VACIO', $id);	
	return $id;
}

/**********************************************
 * Lee los datos del banner indicado
 * Par�metros:
 *	$id: id del banner a leer
 * Devuelve un array con los datos del banner
 **********************************************/
function leer_banner($idBanner) {
	global $modulos;

	if (is_numeric($idBanner)) {
		$resultado = $modulos->resultado_sql ('SQL_BANNER', $idBanner);
		if (!PEAR::isError($modulos->errorSql) && count($resultado)) {
			return $resultado[0];
		} else
			return false;
	} else
		return false;
}

/**************************************
 * Devuelve un array con los banners
 **************************************/
function listado_banners($total = 0, $primero = null) {
	global $modulos;

	$modulos->db->setLimit($total, $primero);
	$listaAnuncios = $modulos->resultado_sql('SQL_BANNERS', Array());
	$modulos->db->setLimit(0);
	if ($modulos->errorSql)
		return false;
	else return $listaAnuncios;
}

/**************************************
 * Lee un banner superior al azar
 **************************************/
function banner_superior_azar() {
	global $modulos;
	
	$banners = $modulos->resultado_sql('SQL_BANNERS_SUPERIOR', Array());
	if (count($banners)) {
		$num = rand(0, count($banners)-1);
		$imagen = leer_imagenes_anuncio($banners[$num]['id'], 'b');
		if (count($imagen))
			return Array($imagen[0]['fichero'], $banners[$num]['url']);
		else return false;
	} else
		return false;
}

/*****************************************
 * Muestra hasta cinco banners laterales
 *****************************************/
function banner_laterales ($categoria = -1) {
	global $modulos, $limiteBannersLaterales;
	
	$modulos->db->setLimit($limiteBannersLaterales, 0);
	if ($categoria > -1) {
		$banners = $modulos->resultado_sql('SQL_BANNERS_LATERAL_CATEGORIA', $categoria);
		if (is_array($banners) && count($banners) == 0)
			$banners = $modulos->resultado_sql('SQL_BANNERS_LATERAL', Array());
	} else
		$banners = $modulos->resultado_sql('SQL_BANNERS_LATERAL', Array());
	$modulos->db->setLimit(0);

	if (is_array($banners) && count($banners)) {
		$imagenes = Array();
		foreach ($banners as $banner) {
			$aux = leer_imagenes_anuncio($banner['id'], 'b');
			if (isset($aux[0]))
				$imagenes[] = Array($aux[0], 'enlace' => $banner['url']);
		}
		return $imagenes;
	} else
		return false;
}

/******************************************
 * Cambia el estado de publicaci�n de un banner
 * Par�metros:
 *	$idBanner: ID del banner a modificar
 *	$estado: estado de publicacion
 * Devuelve TRUE si todo ha sido correcto, FALSE en caso contrario.
 ******************************************/
function publicar_banner ($idBanner, $estado) {
	global $modulos;
	
	if (is_numeric($idBanner)) {
		$datosBanner = leer_banner($idBanner);
		if (is_array($datosBanner) && $datosBanner['publicado'] != $estado && ($estado == C_PUBLICADO || $estado == C_NO_PUBLICADO)) {
			$resultado = $modulos->inserta_sql('SQL_BANNER_CAMBIA_ESTADO', Array($estado, $idBanner));
			if ($modulos->errorSql) {
				print_r ($modulos->errorSql);
				return false;
			}
			else return true;
		} else
			return false;
	} else
		return false;
}


?>