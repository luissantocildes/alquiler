<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

/*********
 * Genera una nueva imagen, copia de la imagen pasada, de un tama�o m�s peque�o, para ser
 * previsualizada, y la guarda en dirDestino
 * Par�metros:
 *	$nombreImagen: Nombre de la imagen a reducir
 *	$dirOrigen: Directorio donde se encuentra la imagen a reducir
 *	$dirDestino: Directorio donde guardar la imagen reducida
 *	$alto, $ancho: Dimensiones de la nueva imagen.
 * Devuelve true si todo va bien
 ***/
function genera_thumb ($nombreImagen, $dirOrigen, $dirDestino, $ancho=100, $alto=0) {
	// Inicializa las variables a utilizar
	$imagenOrigen = $dirOrigen . $nombreImagen;
	$imagenDestino = $dirDestino . $nombreImagen;
	
	// Verifica que exista la imagen origen, la destino no importa, se sobreescribe
	if (file_exists ($imagenOrigen)) {
		// Ok, existe, se verifica que sea de un tipo que se pueda utilizar
		$datosImagen = getimagesize($imagenOrigen);
		if ($datosImagen[2] && (IMAGETYPE_JPEG | IMAGETYPE_PNG | IMAGETYPE_GIF )) {
			// OK, ahora se calcula el tama�o de la imagen resultante
			if ($ancho) {
				$reduccionX = $ancho / $datosImagen[0];
				$alto = $datosImagen[1] * $reduccionX;
			} else if ($alto) {
				$reduccionY = $alto / $datosImagen[1];
				$ancho = $datosImagen[0] * $reduccionY;
			} else {
				$ancho = $datosImagen[0];
				$alto = $datosImagen[1];
			}
			
			// Se reduce la imagen
			switch ($datosImagen[2]) {
				case IMAGETYPE_JPEG:
					$imagen = @imagecreatefromjpeg($imagenOrigen);
					break;
				case IMAGETYPE_PNG:
					$imagen = @imagecreatefrompng($imagenOrigen);
					break;
				case IMAGETYPE_GIF:
					$imagen = @imagecreatefromgif($imagenOrigen);
					break;
			}
			if ($imagen) {
				$thumb = imagecreatetruecolor($ancho, $alto);
				imagecopyresampled ($thumb, $imagen, 0, 0, 0, 0, $ancho, $alto, $datosImagen[0], $datosImagen[1]);
				
				// guarda la imagen
				switch ($datosImagen[2]) {
					case IMAGETYPE_JPEG:
						$aux=imagejpeg($thumb, $imagenDestino);
						break;
					case IMAGETYPE_PNG:
						$aux=imagepng($thumb, $imagenDestino);
						break;
					case IMAGETYPE_GIF:
						$aux=imagegif($thumb, $imagenDestino);
						break;
				}
				
				if ($aux)
					return $datosImagen[2];
				else return FALSE;
			}
		} else
			return false;
	} else
		return false;
}

function paginador ($paginaActual, $totalPaginas, $enlace, $extraLink = Array()) {
	$cadena = '';
	for ($c = 0; $c < $totalPaginas; $c++) {
		if ($c == $paginaActual)
			$cadena .= "<b>" . ($c+1) . "</b>";
		else {
			$aux = str_replace('@', $c, $enlace);
			$cadena .= '<a href=\''.$aux;
			if (count($extraLink))
				$cadena .= "&" . implode("&", $extraLink);
			$cadena .='\'>' . ($c+1) . '</a>';
		}
		if ($c < $totalPaginas-1)
			$cadena .= " - ";
	}
	return $cadena;
}

function paginador2 ($paginaActual, $totalElementos, $elemPorPagina, $opt, $extraLink = Array()) {
	return paginador ($paginaActual, ceil ($totalElementos / $elemPorPagina), $opt, $extraLink);
}

/***********************
 * Incrementa en 1 el valor del campo $nombreCampo en la tabla $nombreTabla
 * en todos los elementos que $nombreCampo >= $valorInicio.
 * Par�metros:
 *		$nombreTabla: Tabla a modificar
 *		$nombreCampo: Campo a modificar
 *		$valorInicio: Valor a partir del que realizar el cambio
 ****************/
function cambia_orden_elementos ($nombreTabla, $nombreCampo, $valorInicio) {
	global $modulos;
	
	$update = procesaSql("update $nombreTabla set $nombreCampo = $nombreCampo + 1 where $nombreCampo >= ?");
	$aux = $modulos->db->query($update, $valorInicio);
	
	if (PEAR::isError($aux))
		mostrar_error_sql ($aux);
}

/**********************************
 * Comprueba que exista una entrada determinada en una tabla
 * Par�metros:
 *		$nombreTabla:	Tabla a comprobar
 *		$nombreCampo:	Campo por el que buscar
 *		$valor:			Valor a buscar
 * Devuelve: true si existe la fila, false en caso contrario o si hay error
 *********/
function existe_fila ($nombreTabla, $nombreCampo, $valor) {
	global $modulos;
	
	$select = procesaSql("SELECT * FROM $nombreTabla WHERE $nombreCampo = ?");
	$aux = $modulos->db->query($select, $valor);
	
	if (PEAR::isError($aux))
		return false;
	else if ($aux->numRows())
		return true;
	else return false;
}

/**********************************
 * Devuelve una cabecera para ficheros XML
 **********************************/
function cabeceraXML() {
    header('Content-Type: text/xml');
    header("Cache-Control: no-cache, must-revalidate");
    //A date in the past
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    echo '<?xml version="1.0" encoding="iso-8859-1" ?>';
    echo "\r\n";
}

/********************************
 * Verifica que una fecha sea correcta.
 * Solo acepta fechas con el formato dia/mes/a�o, o la cadena vac�a
 ***********************/
function verifica_fecha ($cadenaFecha) {
	if (is_string($cadenaFecha)) {
		$fecha = explode ('/', $cadenaFecha);
		$partes = count($fecha);
		if ($partes == 3 && checkdate($fecha[1], $fecha[0], $fecha[2]))
			return $fecha;
		else {
			return false;
		}
	} else
		return (is_string($cadenaFecha) && $cadenaFecha == '');
}

/*********************************
 * Convierte una fecha en el formato dia/mes/a�o al formato a�o-mes-dia
 *************************/
function convierte_fecha ($cadenaFecha) {
	$fecha = explode ('/', $cadenaFecha);
	if (count($fecha) != 3)
		return false;
	else return date('Y-m-d', mktime(0, 0, 0, $fecha[1], $fecha[0], $fecha[2]));
}

function convierte_fecha_esp($cadenaFecha) {
	$fecha = explode ('-', $cadenaFecha);
	if (count($fecha) != 3)
		return false;
	else return date('d/m/Y', mktime(0, 0, 0, $fecha[1], $fecha[2], $fecha[0]));
}

function convierte_fecha_a_unix ($cadenaFecha) {
	$fecha = explode ('/', $cadenaFecha);
	if (count($fecha) != 3)
		return false;
	else return mktime(0, 0, 0, $fecha[1], $fecha[0], $fecha[2]);
}

/**********************************
 * Lee los campos de un formulario y los devuelve dentro de un Array
 * Parámetros:
 *	$campos = Array con los nombres de los campos a leer. Opcional, si no se
 *		pone se leen todos los campos
 * Devuelve: Un array asociativo con los contenidos de los campos. Si un campo no
 *			existe se pone como una cadena vacía.
 **********************************/
function leer_formulario ($campos = Array()) {
	if (!is_array($campos))
		$campos = Array();

	$todos = (count($campos) == 0);
	$datos = Array();
	foreach ($_POST as $indice => $valor) {
		if ($todos || in_array($indice, $campos)) {
			$datos[$indice] = $valor;
		}
	}
	
	return $datos;
}

/*************************************
 * Verifica que la cadena pasada sea un email correcto
 * Parámetros:
 *	$email: dirección de email
 * Devuelve true si la cadena es un email válido
 *************************************/
function es_email($email) {
	return (ereg("^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@+([_a-zA-Z0-9-]+\.)*[a-zA-Z0-9-]{2,200}\.[a-zA-Z]{2,6}$", $email ));
}

/***********************************
 * genera una cadena aleatoria de longitud n
 * Parámetros:
 *	$longitud: longitud de la cadena a obtener
 * Devuelve una cadena de la longitud solicitada
 ***********************************/
function cadena_aleatoria ($longitud) {
	$caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	$totalCaracteres = strlen($caracteres)-1;

	$resultado = '';
	$posAnterior = -1;
	for ($c = 0; $c < $longitud; $c++) {
		$pos = rand(0, $totalCaracteres);
		while ($pos == $posAnterior)
			$pos = rand(0, $totalCaracteres);
		$resultado .= $caracteres[$pos];
	}
	return $resultado;
}

/***********************************
 * Devuelve la lista de las provincias
 * Parámetros: No tiene
 * Devuelve un array con la lista de las provincias y su id. False en caso de error
 ***********************************/
function lista_provincias() {
	global $modulos;
	
	$aux = &$modulos->resultado_sql('SQL_LISTA_PROVINCIAS', Array());
	if ($modulos->errorSql || count($aux) == 0)
		return false;
	else {
		$resultado = Array();
		foreach($aux as $provincia)
			$resultado[$provincia['id']] = $provincia;
		return $resultado;
	}
}

function recursive_array_search($needle,$haystack) {
    foreach($haystack as $key=>$value) {
        $current_key=$key;
        if($needle===$value OR (is_array($value) && recursive_array_search($needle,$value))) {
            return $current_key;
        }
    }
    return false;
}

/**************************************
 * Recorta las palabras de una cadena si alguna de estas superan una cantidad de caracteres determinada
 * Parámetros:
 *	$cadena: cadena a procesar
 *	$maxLetras: Nº máximo de letras por palabra
 *	$separador: Caracter usado para separar las palabras
 * Resultado: Una cadena con las palabras troceadas
 **************************************/
function recorta_palabras ($cadena, $maxLetras = 30, $separador = ' ') {
	$palabras = explode(' ', $cadena);
	$resultado = '';
	foreach ($palabras as $aux) {
		if (strlen($aux) > 30) {
			while (strlen($aux) > 30) {
				$aux1 = substr($aux, 0, 30);
				$resultado .= $aux1.' ';
				$aux = substr($aux, 30);
			}
			$resultado .= $aux.' ';
		}
		else
			$resultado .= $aux.' ';
	}
	return $resultado;
}

function recortar_palabras ($cadena, $maxLetras = 30, $separador = ' ') {
	return recorta_palabras ($cadena, $maxLetras = 30, $separador = ' ');
}

function cortar_palabras ($cadena, $maxLetras = 30, $separador = ' ') {
	return recorta_palabras ($cadena, $maxLetras = 30, $separador = ' ');
}

?>