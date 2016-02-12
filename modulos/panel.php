<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('mensajes.php');
require_once ('anuncios.php');
//require_once ('const_usuarios.php');
require_once ('panel.php');

// Protecci�n contra ejecuci�n sin usuario registrado. Salta autom�ticamente en cuanto se incluye el fichero
// Verifica que la sesi�n est� activa y que el usuario sea correcto.
global $nombreSesion;
if (!(Clogin::existe_sesion() && Clogin::existe_usuario($_SESSION[$nombreSesion]['id']))) {
	global $url;
	header("Location: http://$url");
}

// funci�n principal del m�dulo
function funcion_modulo() {
	global $modulos, $nombreSesion;

	$task = strtolower($modulos->getParam($_POST, 'task', ''));
	if ($task == '')
		$task = strtolower($modulos->getParam($_GET, 'task', ''));
	
	switch ($task) {
		case 'cambiopassword':
			cambio_password();
			break;
		case 'cambiodatospersonales':
			cambio_datos_personales();
			break;
		case 'cuenta':
			mostrar_datos_usuario();
			break;
		default:
			indice_panel();
			break;
	}

}

/********************************
 * Genera el men� del m�dulo
 ********************************/
function menu_modulo() {
	menu_panel();
}

/********************************
 * Muestra el �ndice de la administraci�n del usuario
 ********************************/
function indice_panel() {
	global $modulos, $idUsuario, $diasParaCaducar, $limiteAnunciosPanel;

	$idUsuario = $_SESSION['datosUsuario']['id'];
	$_SESSION['opt_anterior'] = $modulos->opt;
	
	// Lee los anuncios y mensajes del usuario, tanto los nuevos como los antiguos
	$totalAnuncios = total_anuncios_usuario($idUsuario);
	$totalAnunciosCaducados = total_anuncios_usuario($idUsuario, false, true);
	$totalAnunciosPorCaducar = total_anuncios_usuario($idUsuario, false, true, $diasParaCaducar);
	$ultimosAnuncios = anuncios_usuario($idUsuario, $limiteAnunciosPanel, 0, true);
	$anunciosPorCaducar = anuncios_usuario($idUsuario, $limiteAnunciosPanel, 0, false, true, $diasParaCaducar);
	
	$totalMensajes = total_mensajes_usuario($idUsuario, true);
	$totalMensajesNuevos = total_mensajes_usuario($idUsuario, false, true);
	$ultimosMensajes = $modulos->resultado_sql('SQL_MENSAJES_NUEVOS_USUARIO_LIMIT', Array($idUsuario, $limiteAnunciosPanel));
	$mensajesLeidos = $modulos->resultado_sql('SQL_MENSAJES_USUARIO_LIMIT', Array($idUsuario, $limiteAnunciosPanel));
	
	// Devuelve los resultados
	?>
		<div>
			<fieldset class='columnaIzq'>
				<legend>Mis anuncios</legend>
				<?php
					if ($totalAnunciosPorCaducar) { ?>
						Tiene <?php echo $totalAnunciosPorCaducar; ?> anuncios por caducar.
						<?php
							indice_listado_anuncios ($anunciosPorCaducar);
							if ($totalAnunciosPorCaducar > $limiteAnunciosPanel) { ?>
								<a href="?opt=anuncios&task=indice&filtro=porCaducar">Ver todos</a><br><br>
							<?php
							}
						?>
					<?php
					} else {
					}
					
					if ($totalAnuncios) { ?>
						Tiene <?php echo $totalAnuncios; ?> anuncios.
						<?php
							indice_listado_anuncios ($ultimosAnuncios);
							if ($totalAnuncios > $limiteAnunciosPanel) { ?>
								<a href="?opt=anuncios&task=indice">Ver todos</a><br><br>
							<?php
							}
						?>
					<?php
					}
				?>
				<a href="?opt=anuncios&task=nuevo">Poner un nuevo anuncio</a>
			</fieldset>
			<fieldset class='columnaDer'>
				<legend>Mensajes</legend>
				<?php
					if ($totalMensajes) {
					?>
						Tiene <?php echo $totalMensajes; ?> mensajes en su buz&oacute;n<?php
						if ($totalMensajesNuevos) {
							?>, <?php echo $totalMensajesNuevos; ?> nuevo <?php echo $totalMensajesNuevos > 1 ? 's': ''; ?><?php
						}
						?>.
						<br><br><?php
					} else {
						?>
							No tiene mensajes.<br><br>
						<?php
					}
					if ($totalMensajes) {
						if ($totalMensajesNuevos) { ?>
							Mensajes nuevos:
						<?php
							indice_listado_mensajes ($ultimosMensajes);
							if ($totalMensajesNuevos > $limiteAnunciosPanel) { ?>
								<a href="?opt=mensajes&task=indice&filtro=nuevos">Ver todos</a><br><br>
							<?php
							}
						}
						
						// Muestra los mensajes ya le�dos
						if ($totalMensajes) {
							$totalLeidos = $totalMensajes - $totalMensajesNuevos;
							?>
							Mensajes le&iacute;dos:
						<?php
							indice_listado_mensajes ($mensajesLeidos);
							if ($totalLeidos > $limiteAnunciosPanel) { ?>
								<a href="?opt=mensajes&task=indice&filtro=leidos">Ver todos</a><br><br>
							<?php
							}
						} else { ?>
							No tiene mensajes antiguos.
						<?php
						}
					} else {
						?>
						No tiene ning&uacute;n mensaje.
						<?php
					}
				?>
			</fieldset>
			<div></div>
		</div>
	<?php
}

/********************************************
 * Muestra un formulario con los datos del usuario
 ********************************************/
function mostrar_datos_usuario($error = OK) {
	$datosUsuario = $_SESSION['datosUsuario'];

	if ($error) echo $error;
	?>
		<script language="javascript" src="js/funciones_panel.js"type="text/javascript">
		</script>
		<form name="formUsuario" id="formUsuario" method="post" class="formulario">
			<input type="hidden" name="opt" value="login">
			<input type="hidden" name="task" value="">
			Datos del usuario <?php echo $datosUsuario['login']; ?>.
			<fieldset>
				<legend>Cambiar contrase&ntilde;a</legend>
				<label>Contrase&ntilde;a actual:</label> <input type="password" name="f_passwd_old" value=""><br>
				<label>Nueva contrase&ntilde;a:</label> <input type="password" name="f_passwd1" value=""><br>
				<label>Repita la contrase&ntilde;a nueva:</label> <input type="password" name="f_passwd2" value=""><br>
				<div><button class="botonLargo" type="button" onclick="verifica_passwd('formUsuario');">Cambiar la contrase&ntilde;a</button></div>
			</fieldset>
			<fieldset>
				<legend>Datos personales</legend>
				<fieldset>
					<legend>El cambio del nombre y apellidos debe de ser aprobado por el administrador</legend>
					<label>Nombre:</label> <input type="text" name="f_nombre" value="<?php echo $datosUsuario['nombre']; ?>"><br>
					<label>Apellidos:</label> <input type="text" name="f_apellidos" value="<?php echo $datosUsuario['apellidos']; ?>"><br>
				</fieldset>
				<label>E-mail:</label> <input type="text" name="f_email" value="<?echo $datosUsuario['email']; ?>">
				<div style="margin-top: 10px;"><button class="botonLargo" type="button" onclick="verifica_datos_personales('formUsuario');">Cambiar datos personales</button></div>
			</fieldset>
			<p class="cierre"><button class="botonLargo" type="reset">Restaurar datos</button> <button class="botonLargo" type="button" onclick="document.location='?opt=panel';">Volver al panel de control</button></p>
		</form>
	<?php
}

/*************************************
 * Realiza el cambio de contrase�a del usuario
 *************************************/
function cambio_password() {
	global $modulos;
	
	$passwd_old = $modulos->getParam($_POST, 'f_passwd_old', '');
	$passwd_nuevo = $modulos->getParam($_POST, 'f_passwd1', '');
	$passwd_verificacion = $modulos->getParam($_POST, 'f_passwd2', '');
	
	// Verifica que los datos sean correctos
	$error = OK;
	if ($_SESSION['datosUsuario']['password'] != $passwd_old) // El password antiguo es incorrecto
		$error = E_OLD_PASSWD;
	else if ($passwd_old == $passwd_nuevo) // Los passwords nuevo y viejo son iguales
		$error = E_PASSWD_OLD_NEW_DIFF;
	else if ($passwd_nuevo != $passwd_verificacion) // El password nuevo y su verificaci�n son diferentes
		$error = E_PASSWD_DIFF;
	else if ($passwd_nuevo == '') // No se ha puesto un nuevo password
		$error = E_NO_PASSWD;
	
	// Si hay un error se vuelve a mostrar el formulario indicando los errores, sino se procede a realizar los cambios
	if ($error) {
		mostrar_datos_usuario($error);
	} else {
		$resultado = $modulos->inserta_sql('SQL_CAMBIO_PASSWORD', Array($passwd_nuevo, $_SESSION['datosUsuario']['id']));
		if (PEAR::isError($resultado)) {
			?> Ha habido un error al cambiar su contrase&ntilde;a. Por favor, int&eacute;ntelo m&aacute;s tarde. <?php
		} else { // La contrase�a ha cambiado correctamente. Tambi�n cambia los datos en la sesi�n
			?> Ok, datos cambiados correctamente. <?php
			$_SESSION['datosUsuario']['password'] = $passwd_nuevo;
		}
		mostrar_datos_usuario();
	}
}

/*************************************
 * Realiza el cambio de lso datos personales
 *************************************/
function cambio_datos_personales() {
	global $modulos;
	
	$nombre = $modulos->getParam($_POST, 'f_nombre', '');
	$apellidos = $modulos->getParam($_POST, 'f_apellidos', '');
	$email = $modulos->getParam($_POST, 'f_email', '');
	
	// Verifica que los datos sean correctos
	$error = OK;
	if ($nombre == '' || $apellidos == '')
		$error = E_NO_NAME;
	if ($nombre != $_SESSION['datosUsuario']['nombre'] || $apellidos != $_SESSION['datosUsuario']['apellidos'])
		$error = E_NEW_NAME;
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email))
		$error = E_NO_EMAIL;
	
	// Si hay un error se vuelve a mostrar el formulario indicando los errores, sino se procede a realizar los cambios
	if ($error && $error != E_NEW_NAME) {
		mostrar_datos_usuario($error);
	} else {
		$resultado = $modulos->inserta_sql('SQL_CAMBIO_DATOS', Array($nombre, $apellidos, $email, $_SESSION['datosUsuario']['id']));
		if (PEAR::isError($resultado)) {
			?> Ha habido un error al cambiar los datos personales. Por favor, int&eacute;ntelo m&aacute;s tarde. <?php
		} else { // La contrase�a ha cambiado correctamente. Tambi�n cambia los datos en la sesi�n
			?> Ok, datos cambiados correctamente. <?php
			$_SESSION['datosUsuario']['nombre'] = $nombre;
			$_SESSION['datosUsuario']['apellidos'] = $apellidos;
			$_SESSION['datosUsuario']['email'] = $email;
		}
		mostrar_datos_usuario($error);
	}
}
?>