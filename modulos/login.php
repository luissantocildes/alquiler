<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('const_usuarios.php');
require_once ('usuarios.php');
require_once ('funciones.php');
require_once ('mail.php');
require_once ('validacion.php');

// funci�n principal del m�dulo
function funcion_modulo() {
	global $modulos;

	$task = strtolower($modulos->getParam($_POST, 'task', ''));
	if ($task == '')
		$task = strtolower($modulos->getParam($_GET, 'task', ''));
	
	switch ($task) {
		case 'emailrecordar':
			return envio_email_recordar();
			break;
		case 'peticionrecordar':
			return formulario_recordar();
			break;
		case 'logout':
			return logout();
			break;
		case 'registro':
			return registro();
			break;
		case 'procesar_registro':
			return procesar_registro();
			break;
		case 'validacion':
			return validacion();
			break;
		default:
			break;
	}
}

/********************************
 * Envía un email con el enlace para poder cambiar la contraseña
 ********************************/
function envio_email_recordar() {
	global $modulos, $emailAdmin, $dirApp;

	$usuario = $modulos->getParam($_POST, 'usuario', '');
	$email = $modulos->getParam($_POST, 'email', '');

	// Verifica si el usuario y el email son correctos
	$datosUsuario = leer_usuario_login($usuario);
	// Ok, el usuario existe
	$error = OK;
	if (is_array($datosUsuario)) {
		if (strtolower($datosUsuario['email']) != strtolower($email)) // La dirección es diferente
			$error = -1;
	} else $error = -1; // El usuario no existe
	
	if ($error) { // Se muestra el mensaje de error
		?>
			<div class="error">
				El usuario o la direcci&oacute;n de email son incorrectos.<br>
				Por favor, verifique que los haya escrito correctamente y vuelva a intentarlo.
			</div>
		<?php
		formulario_recordar();
	} else {
		// Si todo está bien, entonces se envía el email con la nueva contraseña
		$nuevoPasswd = cadena_aleatoria(8);
		cambio_password_usuario($datosUsuario['id'], $nuevoPasswd);
		
		// envía el email
		$mail = new Email();
		$mail->setTo($datosUsuario['email']);
		$mail->setFrom($emailAdmin);
		$mail->setSubject('Cambio de password - NombreEmpresa.com');
		$cambios = Array('usuario' => $datosUsuario['nombre'],
						'password' => $nuevoPasswd);
		@$mail->loadTemplate($dirApp.'/plantillaEmail/emailCambioPass.txt', $cambios, 'text');
		@$mail->loadTemplate($dirApp.'/plantillaEmail/emailCambioPass.html', $cambios, 'html');
		$mail->send();

		?>
			<div>
				Se ha enviado un email con la nueva contrase&ntilde;a a <?php echo $email; ?>.<br>
				Con este password ya podr&aacute; acceder a su cuenta.<br>
			</div>
		<?php
	}
}

/********************************
 * Muestra el formulario para petición de cambio de contraseña
 ********************************/
function formulario_recordar() {
	?>
		<script language="JavaScript" type="text/javascript">
			function verificar_recordar(nombreFormulario) {
				var formulario = document.getElementById(nombreFormulario);
				if (formulario.usuario.value == '') {
					alert('Escriba el login del usuario.');
					formulario.usuario.focus();
				} else if (formulario.email.value == '') {
					alert('Escriba el email con el que se dió de alta.');
					formulario.email.focus();
				} else
					formulario.submit();
			}
		</script>
		<form method="POST" action="" name="formRecordar" id="formRecordar" class="formulario">
			<input type="hidden" name="opt" value="login">
			<input type="hidden" name="task" value="emailRecordar">
			<div style="margin: 4px;">
				<p>Por favor, escriba su nombre de usuario y la direcci&oacute;n de e-mail que proporcion&oacute; cuando
				se di&oacute; de alta en NombreEmpresa.com.</p>
				<p>Se le enviar&aacute; un email a esta direcci&oacute;n con la nueva contrase&ntilde;a, con la que podr&aacute; acceder a su cuenta.</p>
				<label for="usuario">Usuario:</label>
				<input type="text" name="usuario" id="usuario" value="">
				<label for="email">Direcci&oacute;n de e-mail:</label>
				<input type="text" name="email" id="email" value="">
				<p class="cierre"><button type="button" onclick="verificar_recordar('formRecordar');" style="width: auto;">Recordar contrase&ntilde;a</button> <button type="BUTTON" onclick="document.location='index.php';">Volver al &iacute;ndice</button></p>
			</div>
		</form>
	<?php
}

/********************************
 * valida la creación de un usuario
 ********************************/
function validacion() {
	global $modulos;
	
	$id = $modulos->getParam($_GET, 'id', '');
	if ($id) {
		$datosValidacion = existe_cadena_validacion($id);
		if (is_array($datosValidacion) && count($datosValidacion)) {
			elimina_datos_validacion($id);
			modificar_campo_usuario('estado', $datosValidacion['id_usuario'], C_PRIMERA_VEZ);
			?>
				Ya ha completado el proceso de registro en NombreEmpresa. Ahora ya puede dar de alta sus
				anuncios desde el panel de control que encontrará al entrar con su usuario y contrase&ntilde;a.
			<?php
		} else { 
			?>
				Ops, ha ocurrido un error al leer los datos de validaci&oacute;n. Por favor, int&eacute;ntelo
				m&aacute;s tarde.
			<?php
		}
	}
}

// Cierra la sesi�n
function logout() {
	global $url;

	session_destroy();
//	session_start();
	?>
		<script language="javascript">document.location='http://<?php echo $url; ?>';</script>
	<?php
}

/*******************************
 * Graba los datos del usuario
 *******************************/
function procesar_registro() {
	global $modulos, $emailAdmin, $url, $dirApp;

	$datosNuevoUsuario = Array(
		'login' => $modulos->getParam($_POST, 'f_login', ''),
		'passwd1' => $modulos->getParam($_POST, 'f_passwd1', ''),
		'passwd2' => $modulos->getParam($_POST, 'f_passwd2', ''),
		'nombre' => $modulos->getParam($_POST, 'f_nombre', ''),
		'apellidos' => $modulos->getParam($_POST, 'f_apellidos', ''),
		'email' => $modulos->getParam($_POST, 'f_email', ''));
	
	// Verifica los datos del formulario
	$error = OK;
	$datosUsuario = leer_usuario_login($datosNuevoUsuario['login']);
	if (is_array($datosUsuario)) {
		$error |= E_NO_LOGIN;
	}
	if ($datosNuevoUsuario['passwd1'] == '')
		$error |= E_NO_PASSWD;
	if ($datosNuevoUsuario['passwd1'] != $datosNuevoUsuario['passwd2'])
		$error |= E_PASSWD_DIFF;
	if (!es_email($datosNuevoUsuario['email']))
		$error |= E_NO_EMAIL;
		
	if ($error) {
		?>
			<div class="error">Hay un error en alguno de los datos. Por favor verif&iacute;quelos y vuelve a intentarlo</div>
		<?php
		registro($datosNuevoUsuario, $error);
	} else {
		$resultado = $modulos->inserta_sql ('SQL_NUEVO_USUARIO', Array($_POST['f_login'], $_POST['f_passwd1'], $_POST['f_nombre'], $_POST['f_apellidos'], $_POST['f_email'], C_PENDIENTE));
		
		if (!PEAR::isError($resultado) && $resultado == 1) {
			echo "Bienvenido usuario {$_POST['f_login']}. ";
			?>
			<div style="padding: 5px;">
				Dentro de unos momentos recibir&aacute; un mensaje en su direcci&oacute;n de email para terminar el proceso de registro.<br><br>
				Muchas gracias por darse de alta en NombreEmpresa.
			</div>
			<?php
			
			// Genera el ID para el verificar el alta.
			$cadenaID = cadena_aleatoria(20);
			$datosUsuario = leer_usuario_login($datosNuevoUsuario['login']);
			$resultado = $modulos->inserta_sql ('SQL_NUEVA_VERIFICACION', Array($datosUsuario['id'], $cadenaID));
			
			// envía el email
			$mail = new Email();
			$mail->setTo($datosNuevoUsuario['email']);
			$mail->setFrom($emailAdmin);
			$mail->setSubject('Alta en NombreEmpresa.com');
			$cambios = Array('usuario' => $datosNuevoUsuario['nombre'],
							'enlace' => 'http://'.$url.'?opt=login&task=validacion&id='.$cadenaID);
			@$mail->loadTemplate($dirApp.'/plantillaEmail/emailAlta.txt', $cambios, 'text');
			@$mail->loadTemplate($dirApp.'/plantillaEmail/emailAlta.html', $cambios, 'html');
			@$mail->send();
			//$modulos->mostrar($mail);
		} else {
			?>
				<div class="error">Error al crear el nuevo usuario. Por favor int&eacute;ntelo de nuevo y verifique que los datos sean correctos.</div>
			<?php
		}
	}
		
}

/*******************************
 * Muestra el formulario de registro
 *******************************/
function registro($datosUsuario = Array(), $error = OK) {
	global $url;
	
	if (!is_array($datosUsuario) || empty($datosUsuario)) {
		$datosUsuario = Array(
			'login' => '',
			'nombre' => '',
			'apellidos' => '',
			'email' => ''
		);
	}
	
	?>
		<script language="javascript">
			function verifica_formulario(nombreFormulario, prefijoCampos) {
				return true;
			}
			
			function envio_formulario() {
				var formulario = document.getElementById('formRegistro');
				if (verifica_formulario(formulario, 'f_')) {
					formulario.submit();
				}
					
			}
		</script>
		<form class="formulario" id="formRegistro" name="formRegistro" method="post">
			<input type="hidden" name="opt" value="login">
			<input type="hidden" name="task" value="procesar_registro">
			Por favor, complete el siguiente formulario para registrarse en NombreEmpresa...<br><br>
			<fieldset>
				<legend>Usuario</legend>
				<label for="f_login" <?php if ($error & E_NO_LOGIN) echo 'class="error"'; ?>>Usuario:</label><input type="text" name="f_login" value="<?php echo $datosUsuario['login']; ?>">
				<?php
					if ($error & E_NO_LOGIN && $datosUsuario['login'] != '') { ?>
						<div class='error'>Probablemente ya exista un usuario con ese login. Escoja otro nombre para el usuario.</div>
					<?php
					}
				?>
				<label for="f_passwd1" <?php if ($error & E_NO_PASSWD) echo 'class="error"'; ?>>Contrase&ntilde;a:</label><input type="password" name="f_passwd1" value="">
				<label for="f_passwd2" <?php if ($error & E_PASSWD_DIFF) echo 'class="error"'; ?>>Repita la contrase&ntilde;a:</label><input type="password" name="f_passwd2" value="">
			</fieldset>
			<fieldset>
				<legend>Datos personales</legend>
				<label for="f_nombre" <?php if ($error & E_NO_NAME) echo 'class="error"'; ?>>Nombre:</label><input type="text" name="f_nombre" value="<?php echo $datosUsuario['nombre']; ?>">
				<label for="f_apellidos" <?php if ($error & E_NO_LASTNAME) echo 'class="error"'; ?>>Apellidos:</label><input type="text" name="f_apellidos" value="<?php echo $datosUsuario['apellidos']; ?>">
				<label for="f_email" <?php if ($error & E_NO_EMAIL) echo 'class="error"'; ?>>E-mail:</label><input type="text" name="f_email" value="<?php echo $datosUsuario['email']; ?>">
			</fieldset>
			<p class="cierre"><button type="button" onclick="envio_formulario();">Registrarse</button> <button type="button" onclick="document.location='http://<?php echo $url; ?>'">Cancelar</button></p>
		</form>
	<?php
}
?>