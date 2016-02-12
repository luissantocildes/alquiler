<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once('const_usuarios.php');

/*******************************************
 * Comprueba si el usuario actual es administrador
 * Par�metros: ninguno
 * Devuelve true si el usuario es administrador, false en caso contrario
 *******************************************/
function usuario_actual_es_admin($comprobarEditor = false) {
	global $nombreSesion;

	if ($comprobarEditor)
		return $_SESSION[$nombreSesion]['tipo'] & (C_ADMIN | C_EDITOR);
	else
		return $_SESSION[$nombreSesion]['tipo'] & C_ADMIN;
}

/*******************************************
 * Devuelve el total de usuarios
 * Par�metros: ninguno
 * Devuelve un entero con el total de usuarios; false si ha habido un error
 *******************************************/
function total_usuarios() {
	global $modulos;
	
	$resultados = $modulos->resultado_sql('SQL_TOTAL_USUARIOS', Array());
	if ($modulos->errorSql)
		return false;
	else return $resultados[0]['total'];
}

/*******************************************
 * Devuelve la lista de usuarios
 * Par�metros:
 *	$inicio = elemento por el que comenzar el listado
 *	$total = total de usuarios a buscar
 * Devuelve un array con los usuarios. False en caso de error.
 ************************************************/
function lista_usuarios($total = 0, $primero = null) {
	global $modulos;

	if ($total)
		$modulos->db->setLimit($total, $primero);
	$listaUsuarios = $modulos->resultado_sql('SQL_USUARIOS', Array());
	$modulos->db->setLimit(0);
	if ($modulos->errorSql)
		return false;
	else return $listaUsuarios;
}

/*******************************************
 * Lee los datos del usuario
 * Par�metros:
 *	$idUsuario = id del usuario a buscar
 * Devuelve un array con los datos del usuario. False en caso de error.
 ************************************************/
function leer_usuario($idUsuario) {
	global $modulos;

	if (is_numeric($idUsuario)) {
		$datosUsuario = $modulos->resultado_sql('SQL_USUARIO_ID', $idUsuario);
		if ($modulos->errorSql || empty($datosUsuario))
			return false;
		else return $datosUsuario[0];
	} else
		return false;
}

/*******************************************
 * Lee los datos del usuario por el login
 * Par�metros:
 *	$login = login del usuario a buscar
 * Devuelve un array con los datos del usuario. False en caso de error.
 ************************************************/
function leer_usuario_login($login) {
	global $modulos;

	if ($login) {
		$datosUsuario = $modulos->resultado_sql('SQL_USUARIO', $login);

		if ($modulos->errorSql || empty($datosUsuario))
			return false;
		else return $datosUsuario[0];
	} else
		return false;
}

/********************************
 * Formulario usuario
 * Par�metros:
 *	$datosUsuario: Array con los datos del usuario
 * No devuelve nada
 ********************************/
function formulario_usuario($datosUsuario = Array(), $error = OK) {
	if (!is_array($datosUsuario) || count($datosUsuario) == 0) {
		$datosUsuario['id'] = -1;
		$datosUsuario['login'] = $datosUsuario['nombre'] = $datosUsuario['apellidos'] = $datosUsuario['email'] = '';
		$datosUsuario['tipo'] = C_USUARIO;
		$datosUsuario['estado'] = 0;
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
	<?php
		if ($error) {
			?>
			<div class='cuadroError'>
				Hay un error en los datos, verif�quelos y vuelva a intentarlo.
			</div>
			<?php
		}
	?>
		<form class="formulario" id="formRegistro" name="formRegistro" method="post">
			<input type="hidden" name="opt" value="usuarios">
			<input type="hidden" name="task" value="modificacion">
			<input type="hidden" name="id" value="<?php echo $datosUsuario['id']; ?>">
			<fieldset>
				<legend>Usuario</legend>
				<label>Usuario:</label> <input type="text" name="f_login" value="<?php echo $datosUsuario['login']; ?>"><br>
				<label>Tipo de Usuario:</label> <select name="f_tipo">
					<option value="1" <?php if ($datosUsuario['tipo'] & C_USUARIO) echo 'selected'; ?>>Usuario registrado</option>
					<option value="2" <?php if ($datosUsuario['tipo'] & C_EDITOR) echo 'selected'; ?>>Editor</option>
					<option value="1" <?php if ($datosUsuario['tipo'] & C_ADMIN) echo 'selected'; ?>>Administrador</option>
				</select><br>
				<label>Contrase&ntilde;a:</label> <input type="password" name="f_passwd1" value=""><br>
				<label>Repita la contrase&ntilde;a:</label> <input type="password" name="f_passwd2" value=""><br>
			</fieldset>
			<fieldset>
				<legend>Datos personales</legend>
				<label>Nombre:</label> <input type="text" name="f_nombre" value="<?php echo $datosUsuario['nombre']; ?>"><br>
				<label>Apellidos:</label> <input type="text" name="f_apellidos" value="<?php echo $datosUsuario['apellidos']; ?>"><br>
				<label>E-mail:</label> <input type="text" name="f_email" value="<?php echo $datosUsuario['email']; ?>">
			</fieldset>
			<fieldset>
				<legend>Bloqueo</legend>
				<label>Bloqueo del usuario:</label>
				<select name="f_bloqueado">
					<option value="0" <?php echo $datosUsuario['estado'] == C_CORRECTO ? 'selected':''; ?>>No</option>
					<option value="1" <?php echo $datosUsuario['estado'] == C_BLOQUEADO ? 'selected':''; ?>>Si</option>
				</select>
			</fieldset>
			<div><button type="button" onclick="envio_formulario();">Modificar Usuario</button> <button type="button" onclick="window.history.back();">Cancelar</button></div>
		</form>
	<?php
}

/**********************************
 * Cambia el password de un usuario
 * Parámetros:
 *	$idUsuario: id del usuario
 *	$password: nueva password para el usuario
 * Devuelve true si todo es correcto
 **********************************/
function cambio_password_usuario($idUsuario, $password) {
	return modificar_campo_usuario ('password', $idUsuario, $password);
}

/**********************************
 * Modifica los datos del usuario
 * Par�metros:
 *	$campo
 *	$idUsuario
 *	$valor
 * Devuelve true si todo ha ido bien
 **********************************/
function modificar_campo_usuario($campo, $idUsuario, $valor) {
	global $modulos;

	switch (strtolower($campo)) {
		case 'login':
		case 'tipo':
		case 'password':
		case 'nombre':
		case 'apellidos':
		case 'email':
		case 'estado':
			$resultado = $modulos->inserta_sql ('SQL_CAMBIA_USUARIO', Array($valor, $idUsuario), false, $campo);
			break;
	}
}

/***********************************
 * Crea un nuevo usuario
 * Par�mentro:
 *	$datosUsuario = array con los datos del usuario
 * Devuelve true si todo es correcto. False en caso contrario
 ************************************/
function crear_nuevo_usuario($datosUsuario) {
	global $modulos;
	
	if (is_array($datosUsuario)) {
		$resultado = $modulos->inserta_sql ('SQL_NUEVO_USUARIO_TIPO', Array($datosUsuario['login'], $datosUsuario['password'], 
											$datosUsuario['nombre'], $datosUsuario['apellidos'], $datosUsuario['email'], $datosUsuario['tipo'], $datosUsuario['estado']));
		
		if (!PEAR::isError($resultado) && $resultado == 1) { ?>
			Usuario creado correctamente.<br>
		<?php
		} else {
			?>
				Error al crear el nuevo usuario. Por favor int&eacute;ntelo de nuevo y verifique que los datos sean correctos.
			<?php
		}
	}
}

?>