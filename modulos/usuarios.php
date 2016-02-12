<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('mensajes.php');
require_once ('anuncios.php');
//require_once ('const_usuarios.php');
require_once ('panel.php');
require_once ('usuarios.php');
require_once ('funciones.php');

// Protecci�n contra ejecuci�n sin usuario registrado. Salta autom�ticamente en cuanto se incluye el fichero
// Verifica que la sesi�n est� activa y que el usuario sea correcto.
global $nombreSesion;
if (!(Clogin::existe_sesion() && Clogin::existe_usuario($_SESSION[$nombreSesion]['id'])) && !usuario_actual_es_admin()) {
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
		case 'modificacion':
			modificar_usuario();
			break;
		case 'editarusuario':
			editar_usuario();
			break;
		case 'nuevo':
			nuevo_usuario();
			break;
		default:
			indice_usuarios();
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
function indice_usuarios() {
	global $modulos, $lineasListado;

	$idUsuario = $_SESSION['datosUsuario']['id'];

	// Muestra la lista de usuarios
	$totalUsuarios = total_usuarios();
	$totalPaginas = $totalUsuarios / $lineasListado;
	$pagina = $modulos->getParam($_GET, 'pag', 0);
	if ($pagina > 0)
		$inicio = $pagina * $lineasListado;
	else $inicio = 0;
	$listaUsuarios = lista_usuarios($lineasListado, $inicio);

	if ($totalUsuarios && is_array($listaUsuarios) && count($listaUsuarios)) {
		?><form>
			Mostrando <?php echo count($listaUsuarios); ?> de <?php echo $totalUsuarios; ?> usuarios.
			<table class='listado'>
				<TR class='cabecera'>
					<Th></Th>
					<th>Id</th>
					<th>Login</th>
					<th>Tipo</th>
					<th>Apellidos y Nombre</th>
					<th>E-mail</th>
					<th>Estado</th>
				</TR>
				<?php
					$linea = 1;
					foreach ($listaUsuarios as $usuario) {
						$clase = ($linea++ & 1) ? 'linea1' : 'linea2';
						switch ($usuario['estado']) {
							case C_CORRECTO:
								$estado = 'Normal';
								break;
							case C_BLOQUEADO:
								$estado = 'Bloqueado';
								break;
							case C_PENDIENTE:
								$estado = 'Pendiente';
								break;
						}
					?>
						<tr class="<?php echo $clase; ?>">
							<TD><input type="hidden" name="id[]" value="<?php echo $usuario['id']; ?>">
								<a href="?opt=usuarios&task=borrarUsuario&id=<?php echo $usuario['id']; ?>"><img src="<?php echo $modulos->rutaPlantilla; ?>/images/listados/borrar.png" border="0"></a>
							</td>
							<TD><a href="?opt=usuarios&task=editarUsuario&id=<?php echo $usuario['id']; ?>"><?php echo $usuario['id']; ?></a></TD>
							<TD><a href="?opt=usuarios&task=editarUsuario&id=<?php echo $usuario['id']; ?>"><?php echo $usuario['login']; ?></a></TD>
							<TD class="centrado"><?php echo $usuario['tipo']; ?></TD>
							<TD class="centrado"><?php echo $usuario['apellidos'].', '.$usuario['nombre']; ?></TD>
							<TD><?php echo $usuario['email']; ?></TD>
							<TD class="centrado"><?php echo $estado; ?></TD>
						</tr>
					<?php
					}
				?>
				<tr>
					<TD colspan="7" class="centrado"><?php
						echo paginador ($pagina, $totalPaginas, '?opt=usuarios&pag=@');
					?></TD>
				</tr>
			</table>
		</form>
		<?php
	}
}

/*********************************
 * Edita un usuario
 *********************************/
function editar_usuario() {
	global $modulos;
	
	$idUsuario = $modulos->getParam($_GET, 'id', -1);
	$datosUsuario = leer_usuario($idUsuario);

	if (is_array($datosUsuario)) {
		formulario_usuario ($datosUsuario);
	}
}

/*********************************
 * Muestra un formulario para crear un usuario
 *********************************/
function nuevo_usuario() {
	formulario_usuario ();
}

/*********************************
 * Modifica los datos del usuario o crea un usuario nuevo
 *********************************/
function modificar_usuario() {
	global $modulos;
	
	// Lee los datos del formulario
	$idUsuario = $modulos->getParam($_POST, 'id', -1);
	$login = $modulos->getParam($_POST, 'f_login', '');
	$tipo = $modulos->getParam($_POST, 'f_tipo', -1);
	$passwd1 = $modulos->getParam($_POST, 'f_passwd1', '');
	$passwd2 = $modulos->getParam($_POST, 'f_passwd2', '');
	$nombre = $modulos->getParam($_POST, 'f_nombre', '');
	$apellidos = $modulos->getParam($_POST, 'f_apellidos', '');
	$email = $modulos->getParam($_POST, 'f_email', '');
	$bloqueado = $modulos->getParam($_POST, 'f_bloqueado', 0);
	
	// Lee los datos del usuario a modificar
	$error = OK;
	if (is_numeric($idUsuario)) { // Si $idUsuario == -1 entonces crea un usuario nuevo
		if ($idUsuario == -1) {
			$datosUsuario = Array('id' => $idUsuario,
									'login' => $login,
									'tipo' => $tipo,
									'password' => $passwd1,
									'nombre' => $nombre,
									'apellidos' => $apellidos,
									'email' => $email,
									'bloqueado' => $bloqueado);
			if ($passwd1 != $passwd2)
				$error |= E_PASSWD_DIFF;
			if ($passwd1 == '' || $passwd2 == '')
				$error |= E_NO_PASSWD;
			if ($login == '')
				$error |= E_NO_NAME;
			if (leer_usuario_login($login) != false)
			if ($email == '')
				$error |= E_NO_EMAIL;
			if (!$error) {
				crear_nuevo_usuario ($datosUsuario);
				// Vuelve al listado
				indice_usuarios();
			} else formulario_usuario($datosUsuario, $error);
		} else {
			$datosUsuario = leer_usuario($idUsuario);
			if (is_array($datosUsuario)) {
				//Cambia los datos del usuario uno a uno
				if ($login && $login != $datosUsuario['login'])
					modificar_campo_usuario('login', $idUsuario, $login);
				if ($tipo > -1 && $datosUsuario['tipo'] != $tipo)
					modificar_campo_usuario('tipo', $idUsuario, $tipo);
				if ($passwd1 == $passwd2 && strlen($passwd1.$passwd2) && $passwd != $datosUsuario['password'])
					modificar_campo_usuario('password', $idUsuario, $passwd1);
				if ($nombre && $datosUsuario['nombre'] != $nombre)
					modificar_campo_usuario('nombre', $idUsuario, $nombre);
				if ($apellidos && $datosUsuario['apellidos'] != $apellidos)
					modificar_campo_usuario('apellidos', $idUsuario, $apellidos);
				if ($email && $datosUsuario['email'] != $email)
					modificar_campo_usuario('email', $idUsuario, $email);
				if (($bloqueado == C_CORRECTO || $bloqueado == C_BLOQUEADO) && $datosUsuario['estado'] != $bloqueado && $datosUsuario['estado'] != C_PENDIENTE)
					modificar_campo_usuario('estado', $idUsuario, $bloqueado);
				echo $bloqueado;
			}
			// Vuelve al listado
			indice_usuarios();
		}
	}
	
}

?>