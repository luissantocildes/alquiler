<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('const_usuarios.php');
require_once ('usuarios.php');

function menu_panel() {
	global $nombreSesion;
	
	$esAdmin = $_SESSION[$nombreSesion]['tipo'] & C_ADMIN;
	$esEditor = $_SESSION[$nombreSesion]['tipo'] & C_EDITOR;
	
	?>
		<p class="seccion">Panel de control</p>
		<ul>
			<li class="menu_lateral"><a href="?opt=indice">P&aacute;gina Principal</a></li>
			<li class="menu_lateral"><a href="?opt=panel">Panel de control</a></li>
			<li class="menu_lateral"><a href="?opt=anuncios">Anuncios</a></li>
			<li class="sub_menu_lateral"><a href="?opt=anuncios&task=nuevo">Nuevo Anuncio</a></li>
			<li class="sub_menu_lateral"><a href="?opt=anuncios">Listar Anuncios</a></li>
			<li class="menu_lateral"><a href="?opt=mensajes">Mensajes</a></li>
			<li class="sub_menu_lateral"><a href="?opt=mensajes">Listar Mensajes</a></li>
			<?php
				if ($esAdmin) { ?>
					<li class="menu_lateral"><a href="?opt=banners">Banners</a></li>
					<li class="sub_menu_lateral"><a href="?opt=banners&task=nuevoBanner">Nuevo Banner</a></li>
					<li class="menu_lateral"><a href="?opt=usuarios">Usuarios</a></li>
					<li class="sub_menu_lateral"><a href="?opt=usuarios&task=nuevo">Nuevo Usuario</a></li>
					<li class="menu_lateral"><a href="?opt=categorias">Categor&iacute;as</a></li>
				<?php
				}
			?>
			<li class="menu_lateral"><a href="?opt=panel&task=cuenta">Mi cuenta</a></li>
			<li class="menu_lateral"><a href="?opt=login&task=logout">Salir</a></li>
		</ul>
	<?php
}

?>