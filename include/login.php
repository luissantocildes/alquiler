<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require ('mensajes.php');
require ('const_usuarios.php');
require ('usuarios.php');

define ('ERROR_LOGIN', -1);

class Clogin {
	// Determina el c�digo a ejecutar en funci�n de que el usuario est� logueado o no.
	function cuadro_login() {
		if (Clogin::existe_sesion()) { // Ok, si aparece user en $_SESSION, entonces el usuario est� logueado
			return Clogin::formulario_logout();
		} else { // No aparece, el usuario no est� logueado
			// �Se ha intentado loguear?
			$datosUsuario = Clogin::verifica_login();
			if ($datosUsuario === FALSE) { // No, se muestra el formulario
				return Clogin::formulario_login();
			} else { // Si, �son correctos los datos?
				if (count($datosUsuario) == 0) { // No, el usuario o la contrase�a son err�neos
					return Clogin::formulario_login(ERROR_LOGIN);
				} else { // Si, los datos son correctos
					if ($datosUsuario['estado'] == C_CORRECTO) { // Comprueba si el usuario está bloqueado
						$_SESSION['datosUsuario'] = $datosUsuario;
						$_SESSION['ultima'] = time();
						$_SESSION['timeout'] = 3600;
						return Clogin::formulario_logout();
					} else if ($datosUsuario['estado'] == C_PRIMERA_VEZ) { // Se accede por primera vez
						$_SESSION['datosUsuario'] = $datosUsuario;
						$_SESSION['ultima'] = time();
						$_SESSION['timeout'] = 3600;
						modificar_campo_usuario('estado', $datosUsuario['id'], C_CORRECTO);
						$aux = '<script language="javascript">document.location="index.php";</script>';
						return $aux;
					} else { // El usuario está bloqueado
						?>
						<div id="cuadroLogin">
							<span class="error">Lo sentimos mucho, el usuario con el que está intentando acceder está bloqueado.<br>
							Si considera que esto es un error, pongase en contacto con un administrador.</span>
						</div>
						<?php
					}
				}
			}
		}
	}
	
	/*****************************
	 * Verifica que la sesi�n est� activa
	 * devuelve true en el caso de que lo est�
	 *****************************/
	function existe_sesion() {
		return isset ($_SESSION['datosUsuario']['id']);
	}
	
	/*****************************
	 * Verifica si el usuario indicado por el id existe en la base de datos
	 * Si existe se devuelven los datos del usuario, sino se devuelve FALSE
	 *****************************/
	function existe_usuario ($id) {
		global $modulos;
		
		if (is_numeric($id)) {
			$datosUsuario = Modulo::resultado_sql ('SQL_USUARIO_ID', $id);
			if (PEAR::isError ($datosUsuario)) {
				echo "error";
			} else
				return $datosUsuario;
		} else
			return false;
	}
	 

	/*****************************
	 * Verifica si se ha intentado hacer login
	 * Solo comprueba si los datos pasados del usuario y del login son correctos. Si lo son
	 * devuelve los datos del usuario, sino devuelve un array vac�o. Si no hay datos de login
	 * devuelve FALSE
	 *****************************/
	function verifica_login() {
		global $modulos;
		// Si est�n definidos usuario y cuadroPass en $_POST se intenta comprobar el login,
		// sino se devuelve false
		if (isset ($_POST['usuario']) && isset ($_POST['cuadroPass'])) {
			$datosUsuario = $modulos->resultado_sql ('SQL_USUARIO', $_POST['usuario']);
			if (count($datosUsuario) && strtolower($datosUsuario[0]['password']) == strtolower($_POST['cuadroPass'])) {
				return $datosUsuario[0];
			} else {
				return Array();
			}
		} else
			return FALSE;
	}
	
	/********************
	 * Devuelve el c�digo del cuadro de login con los botones correspondientes
	 ********************/
	function formulario_login() {
		global $modulos;
		?>
			<div id="cuadroLogin">
				<form name="loginForm" id="loginForm" method="post">
					<input type="image" src="<?php echo $modulos->rutaPlantilla; ?>images/entrar.png" class="botonOk" id="boton" name="boton" value="" onclick="formulario.submit();">
					<input type="text" class="loginClass" id="usuario" name="usuario" value="" onkeypress="cambio(event);">
					<input type="password" class="passwdClass" id="cuadroPass" name="cuadroPass" value="" onkeypress="saltar(event);">
					<div><a href="?opt=login&task=registro">Registrarse</a> | <a href="?opt=login&task=peticionRecordar">&iquest;Recordar la contrase&ntilde;a?</a></div>
				</form>
			</div>
		<?php
		$cadena = '';
		
		return $cadena;
	}
	
	/*********************
	 * Muestra el formulario de salida junto con algunos enlaces m�s.
	 *********************/
	function formulario_logout() {
		global $modulos;
	
		$totalMensajesNuevos = total_mensajes_usuario($_SESSION['datosUsuario']['id'], false);
		
		if ($totalMensajesNuevos['total'] == 0)
			$cadena = 'No tiene mensajes nuevos.';
		else {
			$cadena = 'Tiene ' . $totalMensajesNuevos ;
			if ($totalMensajesNuevos['total'] > 1)
				$cadena .= ' mensajes nuevos';
			else $cadena .= ' mensaje nuevo';
		}
		?>
			<div id="cuadroLogin">
				<p><?php echo $cadena; ?></p>
				<div class='recuadro'>
					<a href="?opt=panel">Panel de control</a><br>
					<?php /* // A�ade una opci�n si el usuario es el administrador
						if ($_SESSION['datosUsuario']['tipo'] == C_ADMIN) { ?>
							<a href="?opt=admin">Administraci&oacute;n</a><br>
						<?php
						} */
					?>
					<a href="?opt=login&task=logout">Salir</a>
				</div>
			</div>
		<?php
	}
}

?>