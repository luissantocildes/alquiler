<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require ('funciones.php');
require ('panel.php');

// Protecci?n contra ejecuci?n sin usuario registrado. Salta autom?ticamente en cuanto se incluye el fichero
// Verifica que la sesi?n est? activa y que el usuario sea correcto.
global $nombreSesion;
if (!(Clogin::existe_sesion() && Clogin::existe_usuario($_SESSION[$nombreSesion]['id']))) {
	global $url;
	header("Location: http://$url");
}

// funci�n principal del m�dulo
function funcion_modulo() {
	global $modulos;

	$task = strtolower($modulos->getParam($_POST, 'task', ''));
	if ($task == '')
		$task = strtolower($modulos->getParam($_GET, 'task', ''));

	switch ($task) {
		case 'confirmacionborrar':
			confirmacion_borrar_mensaje();
			break;
		case 'borrarmensaje':
			consulta_borrar_mensaje();
			break;
		case 'leermensaje':
			leer_mensaje();
			break;
		case 'enviomensaje':
			enviar_mensaje();
			break;
		default: echo $task;
			lista_mensajes();
			break;
	}
}

/********************************
 * Genera el men� del m�dulo
 ********************************/
function menu_modulo() {
	global $modulos;
	
	$task = strtolower($modulos->getParam($_POST, 'task', ''));
	if ($task == '')
		$task = strtolower($modulos->getParam($_GET, 'task', ''));

	if ($task == 'enviomensaje')
		menus::menu_standar();
	else
		menu_panel();
}

/*************************
 * Funci�n que env�a un mensaje al publicador de un anuncio
 *************************/
function enviar_mensaje() {
	global $modulos, $nombreSesion, $url;

	$idAnuncio = $modulos->getParam($_POST, 'idAnuncio', -1);
	$asunto = htmlentities($modulos->getParam($_POST, 'campoAsunto', ''), ENT_QUOTES, 'UTF-8');
	$contenido = htmlentities(cortar_palabras($modulos->getParam($_POST, 'campoContenido', ''), 30, ' '), ENT_QUOTES, 'UTF-8');

	$remitente = $_SESSION[$nombreSesion]['id'];
	$dirVuelta = $_SERVER['HTTP_REFERER'];

	// Verifica si es una respuesta o un primer envío
	$remitenteOriginal = $modulos->getParam($_POST, 'respondeA', '');
	$destinatario = false;
	if ($remitenteOriginal) { // si es respuesta se verifica que el usuario pasado sea correcto y coincida con el del mensaje
		$datosUsuario = leer_usuario($remitenteOriginal);
		if (is_array($datosUsuario)) {
			$datosMensaje = datos_mensaje($idAnuncio);
			if ($datosUsuario['id'] == $datosMensaje['remitente'])
				$destinatario[] = $datosUsuario;
		}
		$dirVuelta = 'http://'.$url.'?opt=mensajes';
	} else { // Sino busca el usuario que ha creado el anuncio y se le envía el mensaje
		$destinatario = $modulos->resultado_sql('SQL_PUBLICADOR_ANUNCIO', $idAnuncio);
	}
	
	if (is_array($destinatario) && count($destinatario)) {
		$resultado = $modulos->inserta_sql ('SQL_NUEVO_MENSAJE', Array($remitente, $destinatario[0]['id'], $asunto, $contenido));
		if (!PEAR::isError($resultado)) {
			?>
				<h3>Mensaje enviado</h3>
				<a href="#" onclick="document.location='<?php echo $dirVuelta; ?>'">Volver al anuncio</a>
			<?php
		} else {
			?>
				<h3>Ha ocurrido un error al enviar el mensajes<br>
				Int&eacute;ntelo m&aacute;s tarde.
				<h3>
				<a href="#" onclick="document.location='<?php echo $dirVuelta; ?>'">Volver al anuncio</a>
			<?php
		}
	} else {
		//$modulos->mostrar($destinatario);
	}
}

/***************************
 * Muestra un listado con todos los mensajes
 ***************************/
function lista_mensajes() {
	global $modulos, $lineasListado, $nombreSesion;

	$idUsuario = $_SESSION[$nombreSesion]['id'];
	$totalMensajes = total_mensajes_usuario($idUsuario);
	$totalPaginas = $totalMensajes / $lineasListado;
	$pagina = $modulos->getParam($_GET, 'pag', 0);
	if ($pagina > 0)
		$inicio = $pagina * $lineasListado;
	else $inicio = 0;
	$listaMensajes = mensajes_usuario($idUsuario, $lineasListado, $inicio);
	
	if ($totalMensajes) {
		?><form>
			Mostrando <?php echo count($listaMensajes); ?> de <?php echo $totalMensajes; ?> anuncios.
			<table class='listado'>
				<TR class='cabecera'>
					<Th></Th>
					<th>Asunto</th>
					<th>Texto</th>
					<th>Fecha de Env&iacute;o</th>
					<th>Remitente</th>
				</TR>
				<?php
					$linea = 1;
					foreach ($listaMensajes as $mensaje) {
						$clase = ($linea++ & 1) ? 'linea1' : 'linea2';
						if (!$mensaje['leido'])
							$clase .= '_nuevo';
							
						if (strlen($mensaje['asunto']>20))
							$asunto = substr($mensaje['asunto'], 0, 20) . '...';
						else
							$asunto = $mensaje['asunto'];
							
						$contenido = nl2br(substr(html_entity_decode($mensaje['contenido'], ENT_QUOTES, 'UTF-8'), 0, 20));
						if (strlen($mensaje['contenido']) > 20)
							$contenido .= '...';
					?>
						<tr class="<?php echo $clase; ?>">
							<TD>
								<input type="hidden" name="id[]" value="<?php echo $mensaje['id']; ?>">
								<a href="?opt=mensajes&task=borrarMensaje&id=<?php echo $mensaje['id']; ?>"><img src="<?php echo $modulos->rutaPlantilla; ?>/images/listados/borrar.png" border="0"></a>
							</td>
							<TD><a href="?opt=mensajes&task=leerMensaje&id=<?php echo $mensaje['id']; ?>"><?php echo $asunto; ?></a></TD>
							<TD><?php echo $contenido; ?></TD>
							<TD class="centrado"><?php echo $mensaje['fechaenvio']; ?></TD>
							<TD class="centrado"><?php echo $mensaje['login']; ?></TD>
						</tr>
					<?php
					}
				?>
				<tr>
					<TD colspan="7" class="centrado"><?php
						echo paginador ($pagina, $totalPaginas, '?opt=anuncios&pag=@');
					?></TD>
				</tr>
			</table>
		</form>
		<?php
	} else {
		?>
			No tiene mensajes en su buz&oacute;n.
		<?php
	}
}

/**********************************
 * Muestra el contenido del mensaje y el formulario para responder
 **********************************/
function leer_mensaje() {
	global $modulos, $nombreSesion;
	
	$datosUsuario = $_SESSION[$nombreSesion];
	$idMensaje = $modulos->getParam($_GET, 'id', -1);
	if (is_numeric($idMensaje)) {
		$datosMensaje = datos_mensaje($idMensaje);
		if (is_array($datosMensaje)) {
			?>
				<script language="Javascript" type="text/javascript">
					function envio_mensaje(nombreFormulario) {
						var formulario = document.getElementById(nombreFormulario);
						var titulo = formulario.campoAsunto;
						var mensaje = formulario.campoContenido;
						if (titulo.value != '' && mensaje.value != '') {
							formulario.submit();
						} else {
							alert ('Escriba el contenido del mensaje');
						}
					}
				</script>
				<h3>&nbsp;&nbsp;Leer Mensaje</h3>
				&nbsp;&nbsp;&nbsp;<b>Asunto</b>: <?php echo $datosMensaje['asunto']; ?><br>
				&nbsp;&nbsp;&nbsp;<b>Remitente</b>: <?php echo $datosMensaje['login']; ?><br>
				&nbsp;&nbsp;&nbsp;<b>Fecha de Env&iacute;o</b>: <?php echo ucfirst(htmlentities(strftime('%A %d/%m/%Y, %H:%M:%S', $datosMensaje['fechaenvio']), ENT_QUOTES, 'iso-8859-1')); ?><br>
				&nbsp;&nbsp;&nbsp;<b>Contenido</b>: <div class="cuadroMensaje"><?php echo nl2br($datosMensaje['contenido']); ?></div><br>
				<form id="formMensaje" method="post" action="index.php" enctype="application/x-www-form-urlencoded" class="formulario">
					<input type="hidden" id="opt" name="opt" value="mensajes">
					<input type="hidden" id="task" name="task" value="envioMensaje">
					<input type="hidden" id="idAnuncio" name="idAnuncio" value="<?php echo $idMensaje; ?>">
					<input type="hidden" id="respondeA" name="respondeA" value="<?php echo $datosMensaje['remitente']; ?>">
					<fieldset>
						<legend>Escriba su respuesta</legend>
						<div class='mensajeRespuesta' id='capaMensaje'>
							<div class="contenido">
								<label>Asunto:</label> <input type="text" id="campoAsunto" name="campoAsunto" value=""><br>
								<label>Contenido:</label> <textarea id='campoContenido' name="campoContenido"></textarea>
							</div>
							<p class="cierre"><input type="button" value="Enviar mensaje" onclick="envio_mensaje('formMensaje');"></p>
						</div>
					</fieldset>
				</form>
				<p class="cierre"><input type="button" value="Volver atr&aacute;s" onclick="window.history.back();"></p>
			<?php
			
			// Marca el mensaje como leido
			if ($datosMensaje['leido'] == 0)
				marcar_mensaje_leido($idMensaje, true);
		} else {
			if ($modulos->errorSql) {
				?>
				Error al leer el mensaje.<br>
				Int&eacute;ntelo m&aacute;s tarde.<br>
				<a href="#" onclick="window.history.back();">Volver atras</a>
				<?php
			}
		}
	} else {
		echo "error, el par&aacute;metro no es num&eacute;rico";
	}
}

/************************************
 * Borra el mensaje seleccionado
 ************************************/
function consulta_borrar_mensaje() {
	global $modulos, $nombreSesion;

	$idMensaje = $modulos->getParam($_GET, 'id', -1);

	// Primero se comprueba si el anuncio es del usuario, solo si
	// el usuario es un usuario normal
	if (pertenece_mensaje_usuario($_SESSION[$nombreSesion]['id'], $idMensaje)) {
		if (!formulario_borrar_mensaje($idMensaje))
			lista_mensajes();
	} else
		lista_mensajes();
}

/***********************************
 * Muestra el formulario con los datos y la confirmaci�n de borrar el mensaje
 ***********************************/
function formulario_borrar_mensaje($idMensaje) {
	global $modulos;

	// Carga los datos del mensaje
	$datosMensaje = datos_mensaje($idMensaje);
	if ($datosMensaje === FALSE) {
		return false;
	} else {
		?>
			<form id="formMensaje" method="post" action="index.php" enctype="application/x-www-form-urlencoded">
				<input type="hidden" id="opt" name="opt" value="mensajes">
				<input type="hidden" id="task" name="task" value="confirmacionBorrar">
				<input type="hidden" id="idAnuncio" name="idAnuncio" value="<?php echo $idMensaje; ?>">
				<h3>Leer Mensaje</h3>
				<b>Asunto</b>: <?php echo $datosMensaje['asunto']; ?><br>
				<b>Remitente</b>: <?php echo $datosMensaje['login']; ?><br>
				<b>Fecha de Env&iacute;o</b>: <?php echo strftime('%A %d/%m/%Y, %H:%M:%S', $datosMensaje['fechaenvio']); ?><br>
				<b>Contenido</b>: <div class="cuadroMensaje"><?php echo nl2br($datosMensaje['contenido']); ?></div><br>
				<div><button type="submit">Borrar mensaje</button> <button type="button" onclick="window.history.back();">Cancelar</button></div>
			</form>
		<?php
		return true;
	}
}

/********************************
 * Borra el mensaje de la base de datos
 ********************************/
function confirmacion_borrar_mensaje() {
	global $modulos, $nombreSesion;
	
	$idMensaje = $modulos->getParam($_POST, 'idAnuncio', -1);

	// Primero se comprueba si el mensaje es del usuario
	if (pertenece_mensaje_usuario($_SESSION[$nombreSesion]['id'], $idMensaje)) {
		borrar_mensaje($idMensaje);
	}
	lista_mensajes();
}
?>