<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('anuncios.php');
require_once ('funciones.php');
require_once ('denuncias.php');
require_once ('mail.php');

/************************
 * M�dulo Denuncias
 * Muestra las denuncias y las procesa
 ************************/
 
global $nombreSesion;
$task = strtolower(Modulo::getParam($_POST, 'task', ''));
if (!(Clogin::existe_sesion() && Clogin::existe_usuario($_SESSION[$nombreSesion]['id'])) && $task != 'enviodenuncia') {
	global $url;
	header("Location: http://$url");
	exit(0);
}
 
function funcion_modulo() {
    global $modulos;
	
	$task = strtolower($modulos->getParam($_POST, 'task', ''));
	if ($task == '')
		$task = strtolower($modulos->getParam($_GET, 'task', ''));
	
	switch ($task) {
		case 'enviodenuncia':
			procesa_denuncia();
			break;
		default:
			break;
	}
}

/********************************************************
 * Procesa una denuncia enviada, enviando un mensaje a los administradores y al denunciante
 * La denuncia queda pendiente hasta que se acepte o se deniegue
 ********************************************************/
function procesa_denuncia() {
	global $modulos, $emailAdmin, $dirApp, $url;
	
	$datosDenuncia['idAnuncio'] = $modulos->getParam($_POST, 'idAnuncio', -1);
	$datosDenuncia['idUsuario'] = $modulos->getParam($_POST, 'idUsuario', -1);
	$datosDenuncia['nombre'] = htmlentities($modulos->getParam($_POST, 'campoNombre', ''), ENT_QUOTES, 'UTF-8');
	$datosDenuncia['apellidos'] = htmlentities($modulos->getParam($_POST, 'campoApellido', ''), ENT_QUOTES, 'UTF-8');
	$datosDenuncia['email'] = htmlentities($modulos->getParam($_POST, 'campoEmail', ''), ENT_QUOTES, 'UTF-8');
	$asunto = $modulos->getParam($_POST, 'campoAsunto', '');
	$datosDenuncia['asunto'] = htmlentities(cortar_palabras($asunto), ENT_QUOTES, 'UTF-8');
	$contenido = $modulos->getParam($_POST, 'campoContenido', '');
	$datosDenuncia['contenido'] = htmlentities(cortar_palabras($contenido), ENT_QUOTES, 'UTF-8');
	$datosDenuncia['ip'] = $_SERVER['REMOTE_ADDR'];
	
	// Primero verifica que el anuncio a denunciar exista
	$datosAnuncio = leer_anuncio($datosDenuncia['idAnuncio']);
	if (is_array($datosAnuncio)) { // si el anuncio existe
		// Guarda la denuncia
		//$idDenuncia = inserta_denuncia ($datosDenuncia);
		$idDenuncia = nueva_denuncia();
		if ($idDenuncia !== FALSE) { // Ha insertado la denuncia correctamente
			// envía el email al denunciante
			$mail = new Email();
			$mail->setTo($datosDenuncia['email']);
			$mail->setFrom($emailAdmin);
			$mail->setSubject('Su denuncia en NombreEmpresa.com');
			$cambios = Array('usuario' => $datosDenuncia['nombre'],
							'anuncio' => $datosAnuncio['titulo'],
							'id' => $idDenuncia,
							'asunto' => $asunto,
							'contenido' => $contenido
							);
			@$mail->loadTemplate($dirApp.'/plantillaEmail/emailDenuncia.txt', $cambios, 'text');
			@$mail->loadTemplate($dirApp.'/plantillaEmail/emailDenuncia.html', $cambios, 'html');
			$mail->send();
			//$modulos->mostrar ($mail);
			//die();
			
			// Envía un aviso a los administradores
			// Se buscan todos los administradores y les envía un email
			$administradores = $modulos->resultado_sql('SQL_USUARIOS_ADMIN', Array());
			if (is_array($administradores)) {
				$mail->setFrom($emailAdmin);
				$mail->setSubject('Nueva denuncia en NombreEmpresa.com');
				$destinatarios = Array();
				foreach ($administradores as $usuario) {
					$destinatarios[] = $usuario['email'];
				}
				$mail->setTo(implode(',', $destinatarios));
				$cambios = Array('apellidos' => $datosDenuncia['apellidos'],
								'nombre' => $datosDenuncia['nombre'],
								'email' => $datosDenuncia['email'],
								'id' => $idDenuncia,
								'asunto' => $asunto,
								'contenido' => $contenido,
								'enlace_anuncio' => 'http://'.$url.'?opt=indice&task=anuncio&id='.$datosAnuncio['id']
								);
				@$mail->loadTemplate($dirApp.'/plantillaEmail/emailDenunciaAdmin.txt', $cambios, 'text');
				@$mail->loadTemplate($dirApp.'/plantillaEmail/emailDenunciaAdmin.html', $cambios, 'html');
				$mail->send();
			}
			
			?>
				Su denuncia al anuncio &quot;<?php echo $datosAnuncio['titulo']; ?>&quot; ha sido enviada.<br>
				Un administrador de NombreEmpresa.com le responder&aacute; prontamente.
			<?php
		} else {
			?>
				<div class="error">
					Ha ocurrido un error al insertar la denuncia. Vuelva a intentarlo m&aacute;s tarde.
				</div>
			<?php
		}		
	} else { // anuncio inexistente o error
		?>
			<div class="error">
				El anuncio a denunciar no existe o ha ocurrido un error con la base de datos. Por favor int&eacute;ntelo m&aacute;s tarde.
			</div>
		<?php
	}
}


?>