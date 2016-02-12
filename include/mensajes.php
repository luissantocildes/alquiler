<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

/*************************************************
 * Devuelve el total de mensajes de un usuario
 * Par�metros:
 *	$idUsuario = id del usuario a contar los mensajes
 *	$noLeidos = si TRUE cuenta los mensajes sin leer, sino cuenta solo los mensajes leidos
 *  $
 * Devuelve: un entero con el total de mensajes. FALSE si hay alg�n error
 *************************************************/
function total_mensajes_usuario ($idUsuario, $todos = true, $noLeidos = true) {
	global $modulos;

	if ($todos)
		$totalMensajes = $modulos->resultado_sql('SQL_TOTAL_MENSAJES_USUARIO', $idUsuario);
	else if ($noLeidos)
		$totalMensajes = $modulos->resultado_sql('SQL_TOTAL_MENSAJES_NUEVOS_USUARIO', $idUsuario);
	else
		$totalMensajes = $modulos->resultado_sql('SQL_TOTAL_MENSAJES_LEIDOS_USUARIO', $idUsuario);
	
	if (PEAR::isError ($totalMensajes))
		return FALSE;
	else return $totalMensajes[0]['total'];
}

/************************************************
 * Devuelve una lista con los mensajes del usuario
 * Par�metros:
 *	$idUsuario = id del usuario a consultar
 *	$inicio = elemento por el que comenzar el listado
 *	$total = total de mensaje a buscar
 * Devuelve un array con los mensaje. False en caso de error.
 ************************************************/
function mensajes_usuario($idUsuario, $total = 0, $primero = null) {
	global $modulos;

	if (is_numeric($idUsuario)) {
		if ($total)
			$modulos->db->setLimit($total, $primero);
		$listaAnuncios = $modulos->resultado_sql('SQL_MENSAJES_USUARIO', $idUsuario);
		$modulos->db->setLimit(0);
		if ($modulos->errorSql)
			return false;
		else return $listaAnuncios;
	}
}

/************************************************
 * Genera una lista HTML con los mensajes pasados
 * Par�mentros: 
 * 	$listaMensajes: Array con los mensajes.
 * Devuelve: nada
 ************************************************/
function indice_listado_mensajes ($listaMensajes) {
	if (is_array($listaMensajes) && count($listaMensajes)) {
		?>
			<ul>
			<?php
				foreach ($listaMensajes as $mensaje) {
					?>
						<li><a href="?opt=mensajes&task=leerMensaje&id=<?php echo $mensaje['id']; ?>"><?php echo $mensaje['asunto'];?></a></li>
					<?php
				}
			?>
			</ul>
		<?php
	} else {
		echo 'Error: paramentro $listaMensajes no es un Array.';
	}
}

/************************************************
 * Devuelve los datos de un mensaje
 * Par�metros:
 *	$idMensaje: id del mensaje a buscar
 * Devuelve: un array con los datos del mensaje,
 *			 un array vac�o si el mensaje no existe,
 *			 FALSE si hay un error
 ************************************************/
function datos_mensaje($idMensaje) {
	global $modulos;
	
	if (is_numeric($idMensaje)) {
		$datosMensaje = $modulos->resultado_sql ('SQL_MENSAJE', $idMensaje);
		if ($modulos->errorSql)
			return false;
		else {
			if (count($datosMensaje))
				return $datosMensaje[0];
			else return Array();
		}
	} else
		return false;
}

/*************************************************
 * Comprueba si un mensaje pertenece a un usuario
 * Par�metros:
 *	$idUsuario = id del usuario a verificar
 *	$idMensaje = id del mensaje a verificar
 * Devuelve: true si el mensaje pertenece al usuario, false en caso contrario o de error
 *************************************************/
function pertenece_mensaje_usuario ($idUsuario, $idMensaje) {
	global $modulos;
	
	if (is_numeric($idUsuario) && is_numeric($idMensaje)) {
		$resultado = $modulos->resultado_sql ('SQL_MENSAJE_USUARIO', Array($idUsuario, $idMensaje));
		return (!PEAR::isError($resultado) && count($resultado) && $resultado[0]['total']);
	} else
		return false;
}

/********************************************
 * Marca un mensaje como le�do
 * Par�metros:
 *	$idMensaje: id del mensaje
 *	$leido: true para marcarlo como le�do, false para no leido
 * Devuelve: true si todo ha ido bien, false en caso contrario
 ********************************************/
function marcar_mensaje_leido ($idMensaje, $leido = true) {
	global $modulos;
	
	if (is_numeric($idMensaje) && is_bool($leido)) {
		$resultado = $modulos->inserta_sql ('SQL_MARCAR_MENSAJE_LEIDO', Array($leido ? 1 : 0, $idMensaje));
		return (!PEAR::isError($resultado));
	} else
		return false;
}

/********************************************
 * Borra un mensaje
 * Par�metros:
 *	$idMensaje: mensaje a borrar
 * Devuelve true si todo est� bien, false en caso contrario
 ********************************************/
function borrar_mensaje($idMensaje) {
	global $modulos;
	
	if (is_numeric($idMensaje)) {
		$resultado = $modulos->inserta_sql ('SQL_BORRAR_MENSAJE', $idMensaje);
		if (PEAR::isError($resultado))
			return false;
		else return true;
	} else
		return false;
}

?>