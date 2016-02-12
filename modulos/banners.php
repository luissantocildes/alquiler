<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('banners.php');
require_once ('categorias.php');
require_once ('const_anuncios.php');
require_once ('panel.php');
require_once ('funciones.php');
require_once ('menus.php');
require_once ('anuncios.php');

// Protecci�n contra ejecuci�n sin usuario registrado. Salta autom�ticamente en cuanto se incluye el fichero
// Verifica que la sesi�n est� activa y que el usuario sea correcto.
global $nombreSesion;
$task = strtolower(Modulo::getParam($_GET, 'task', ''));
if (!(Clogin::existe_sesion() && Clogin::existe_usuario($_SESSION[$nombreSesion]['id'])) && !usuario_actual_es_admin()) {
	global $url;
	header("Location: http://$url");
	exit(0);
}

// funci�n principal del m�dulo
function funcion_modulo() {
	global $modulos;

	$task = strtolower($modulos->getParam($_POST, 'task', ''));
	if ($task == '')
		$task = strtolower($modulos->getParam($_GET, 'task', ''));

	switch ($task) {
		case 'cambiopublicacion':
			cambiar_publicacion();
			break;
		case 'editarbanner':
			editar_banner();
			break;
		case 'borrarbanner':
			borrar_banner();
			break;
		case 'modificar':
			modificar_banner();
			break;
		case 'grabar':
			grabar_nuevo_banner();
			break;
		case 'nuevobanner':
			formulario_nuevo_banner();
			break;
		case 'confirmarborrar':
			borrar_banner_confirmado();
			break;
		case 'indice':
		default:
			lista_banners();
			break;
	}
}

/**********************************
 * Realiza el cambio de la publicacion
 **********************************/
function cambiar_publicacion() {
	global $modulos, $nombreSesion;
	
	$bannersMostrados = $modulos->getParam($_POST, 'id', Array());
	$bannersPublicados = $modulos->getParam($_POST, 'publicado', Array());
	
	// Verifica si se publicado o despublicado algún banner de la página indicada
	if (is_array($bannersMostrados) && is_array($bannersPublicados) && count($bannersMostrados)) {
		// se recorre $bannerssMostrados. Si el elemento aparece en bannersPublicados, entonces
		// hay que publicar el banner, sino hay que despublicarlo
		foreach ($bannersMostrados as $banner) {
			// Modifica el estado de publicacion del banner
			if (in_array($banner, $bannersPublicados))
				publicar_banner($banner, C_PUBLICADO);
			else publicar_banner($banner, C_NO_PUBLICADO);
			
		}
	}
	lista_banners();
}



function editar_banner() {
	global $modulos;
	
	$id = $modulos->getParam($_GET, 'id', -1);
	$datosBanner = leer_banner($id);
	if (is_array($datosBanner)) {
		if ($datosBanner['fecha_fin'] == '0000-00-00')
			$datosBanner['fecha_fin'] = '';

		$aux = leer_imagenes_anuncio($datosBanner['id'], 'b');
		if ($aux) {
			$datosBanner['fichero'] = $aux[0]['fichero'];
		}
	
		formulario_nuevo_banner($datosBanner);
	} else
		lista_banners();
}

function modificar_banner() {
	global $modulos, $dirImagenes, $dirMinis;
	
	$datosBanner = leer_banner($modulos->getParam($_POST, 'id', -1));
	
	// Lee los datos del banner
	$datosBanner['titulo'] = $modulos->getParam($_POST, 'titulo', '');
	$datosBanner['tipo'] = $modulos->getParam($_POST, 'tipo', -1);
	$datosBanner['url'] = $modulos->getParam($_POST, 'url', '');
	$datosBanner['fecha_fin'] = $modulos->getParam($_POST, 'fechaFinPub', '');
	$datosBanner['categoria'] = $modulos->getParam($_POST, 'categoria', -1);
	$datosBanner['id'] = $modulos->getParam($_POST, 'id', -1);
	
	$aux = verifica_fecha($datosBanner['fecha_fin']);
	if ($aux) {
		$hoy = mktime(0,0,0);
		$fechaFinUnix = convierte_fecha_a_unix($datosBanner['fecha_fin']);
		if ($fechaFinUnix > $hoy)
			$fechaFin = convierte_fecha($datosBanner['fecha_fin']);
		else $fechaFin = '0000-00-00';
		$datosBanner['fecha_fin'] = $fechaFin;
	} else $datosBanner['fecha_fin'] = 0;
	
	if ($datosBanner['tipo'] == C_BANNER_SUPERIOR) {
		$imagenMaxX = 460;
		$imagenMaxY = 80;
	} else {
		$imagenMaxX = 120;
		$imagenMaxY = 120;
	}
	
	// Determina si los ficheros recibidos son imagenes correctas
	$listaImagenes = procesar_imagenes_anuncio($_FILES, $dirImagenes, $dirMinis, 1, $imagenMaxX, $imagenMaxY);
	if ($listaImagenes[0]['error'] == 0)
		$idBanner = grabar_banner($datosBanner, true, $listaImagenes);
	else $idBanner = grabar_banner($datosBanner, true, false);
	//lista_banners();
	
}

/********************************
 * Genera el men� del m�dulo
 ********************************/
function menu_modulo() {
	global $nombreSesion;
	
	if (isset ($_SESSION[$nombreSesion]))
		menu_panel();
	else
		menus::menu_standar();
}

/***********************************
 * Muestra el formulario para los banners
 ***********************************/
function formulario_nuevo_banner ($datosBanner = Array(), $borrar = false) {
	global $modulos;
	
	$modificar = false;
	if (count($datosBanner) == 0) {
		$datosBanner['id'] = $datosBanner['tipo'] = $datosBanner['categoria'] = $datosBanner['imagen'] = 
		$datosBanner['tipo_imagen'] = -1;
		$datosBanner['publicado'] = C_PUBLICADO;
		$datosBanner['titulo'] = $datosBanner['texto'] = $datosBanner['url'] = $datosBanner['fichero'] = '';
		$datosBanner['fecha_fin'] = '';
		$datosBanner['tipo'] = 0;
		$task = 'grabar';
		$textoBoton = 'Enviar Banner';
		$tituloFormulario = 'Nuevo Banner';
	} else if ($borrar) {
		$task = 'confirmarborrar';
		$textoBoton = 'Borrar Banner';
		$tituloFormulario = 'Borrar Banner';
		$datosBanner['fecha_fin'] = convierte_fecha_esp($datosBanner['fecha_fin']);
		$imagenBanner = leer_imagenes_anuncio($datosBanner['id'], 'b');
	} else {
		$task = 'modificar';
		$textoBoton = 'Editar Banner';
		$tituloFormulario = 'Editar Banner';
		$modificar = true;
		$datosBanner['fecha_fin'] = convierte_fecha_esp($datosBanner['fecha_fin']);
		$imagenBanner = leer_imagenes_anuncio($datosBanner['id'], 'b');
	}

	$listaCategorias = lista_categorias();
	$listaProvincias = lista_provincias();
	
	?>
		<script type="text/javascript" src="js/funciones_banners.js"></script>
		<style type="text/css">@import url(js/calendar/calendar-green.css);</style>
		<script type="text/javascript" src="js/calendar/calendar.js"></script>
		<script type="text/javascript" src="js/calendar/lang/calendar-es.js"></script>
		<script type="text/javascript" src="js/calendar/calendar-setup.js"></script>
		<script>
			<?php
			if ($borrar || $modificar) echo "modificado = true;";
			else echo "modificado = false;";
			?>
		</script>
		<form name="formBanner" id="formBanner" method="post" action="" class="formulario" enctype="multipart/form-data">
			<h3><?php echo $tituloFormulario; ?></h3>
			<fieldset>
				<input id="opt" name="opt" type="hidden" value="banners">
				<input id="task" name="task" type="hidden" value="<?php echo $task; ?>">
				<input id="id" name="id" type="hidden" value="<?php echo $datosBanner['id']; ?>">
				<label for="titulo">Titulo:</label><input type="text" id="titulo" name="titulo" value="<?php echo $datosBanner['titulo']; ?>" <?php echo $borrar ? 'readonly' : ''; ?>><br>
				<label for='tipo'>Tipo:</label><select id="tipo" name="tipo" onchange="habilitar_campo('categoria', this.selectedIndex-1);" <?php if ($borrar) echo 'disabled'; ?>>
					<option value="0" <?php if ($datosBanner['tipo'] == 0) echo 'selected'; ?>>Superior</option>
					<option value="1" <?php if ($datosBanner['tipo'] == 1) echo 'selected'; ?>>Lateral</option>
				</select>
				<label for="categoria">Categor&iacute;a:</label>
				<select id="categoria" name="categoria" <?php if ($datosBanner['tipo'] == 0) echo 'disabled'; ?> <?php echo $borrar ? 'readonly' : ''; ?>>
					<option value="-1">Seleccione una categor&iacute;a</option>
					<?php
						$padre = 0;
						foreach ($listaCategorias as $categoria) {
							if ($categoria['padre'] != $padre) {
								$padre = $categoria['padre'];
								?>
									</optgroup>
								<?php
							}
							if ($categoria['id'] == $datosBanner['categoria'])
								$selected = 'selected';
							else $selected = '';
							if ($categoria['padre']) {
								?>
									<option value="<?php echo $categoria['id']; ?>" <?php echo $selected; ?>><?php echo $categoria['nombre']; ?></option>
								<?php
							} else {
								?>
									<optgroup label='<?php echo $categoria['nombre']; ?>'>
									<option value="<?php echo $categoria['id']; ?>" <?php echo $selected; ?>><?php echo $categoria['nombre']; ?>(generico)</option>
								<?php
								$padre = $categoria['id'];
							}
						}
					?>
				</select><br>
				<label for="url">Url:</label>
				<input type="text" id="url" name="url" onclick="this.select();" onchange="modificado = true;" value="<?php
						if ($datosBanner['url']) {
							echo $datosBanner['url'];
						} else { ?>Escriba la direcci&oacute;n a la que apuntar&aacute; el banner<?php
						}
				?>" <?php echo $borrar ? 'readonly' : ''; ?>>
				<label for="imagen1">Imagen del banner:</label>
				<?php if ($datosBanner['imagen'] != -1) { ?>
					<img style="display:inline;" src="ficheros/<?php echo $imagenBanner[0]['fichero']; ?>">
					<br><br>
				<?php
				} ?>
				<input type="file" name="imagen1" id="imagen1" size="50" <?php echo $borrar ? 'disabled' : ''; ?>>
				<label for="fechaFinPub">Fecha de fin de la publicaci&oacute;n:</label><input id="fecha_fin" name="fechaFinPub" type="text" value="<?php echo $datosBanner['fecha_fin']; ?>"  <?php echo $borrar ? 'readonly' : ''; ?>>
			</fieldset>
			<p class="cierre"><button type="button" onclick="verificar_banner('formBanner', modificado);"><?php echo $textoBoton; ?></button> <button type="button" onclick="volver_anterior('?opt=banners');">Cancelar</button></p>
		</form>
		<script type="text/javascript">
			Calendar.setup(
				{
					inputField  : "fecha_fin",         // ID of the input field
					ifFormat    : "%d/%m/%Y",    // the date format
					button      : "trigger"       // ID of the button
				}
			);
		</script>
		<script type="text/javascript">
			var aux = document.getElementById('titulo');
			aux.focus();
		</script>
	<?php

}

/*******************************************
 * Muestra la lista de anuncios
 * Determina que lista mostrar en base al tipo de usuario
 *******************************************/
function lista_banners() {
	global $modulos, $lineasListado;

	$pagina = $modulos->getParam($_POST, 'pag', -1);
	if ($pagina == -1)
		$pagina = $modulos->getParam($_GET, 'pag', 0);
	if ($pagina > 0)
		$inicio = $pagina * $lineasListado;
	else $inicio = 0;
	
	$totalBanners = total_banners();
	if ($totalBanners) {
		$listaBanners = listado_banners($lineasListado, $inicio);
		$totalPaginas = $totalBanners / $lineasListado;
		?>
		<script language="Javascript" type="text/javascript" src="js/funciones_banners.js"></script>
		<form name="anuncios" id="anuncios" method="post">
			<input type="hidden" id="opt" name="opt" value="banners">
			<input type="hidden" id="task" name="task" value="">
			<input type="hidden" id="pag" name="pag" value="<?php echo $pagina; ?>">
			Mostrando <?php echo count($listaBanners); ?> de <?php echo $totalBanners; ?> banners.
			<table class='listado'>
				<TR class='cabecera'>
					<Th colspan="2"></Th>
					<th>T&iacute;tulo</th>
					<th>url</th>
					<th>Tipo</th>
					<th>Fecha de Creaci&oacute;n</th>
					<!--th>Veces visto</th-->
					<th>Publicado</th>
				</TR>
				<tr class="cabecera">
					<th colspan="6">&nbsp;</th>
					<th><a href="#" onclick="cambiar_estado_banner();">Guardar cambios</a></th>
				</tr>
				<?php
					$linea = 1;
					foreach ($listaBanners as $anuncio) {
						$clase = ($linea++ & 1) ? 'linea1' : 'linea2';
						$texto = substr($anuncio['url'], 0, 20);
						if (strlen($anuncio['url']) > 20)
							$texto .= '...';
						$tipo = $anuncio['tipo'] == 0 ? 'Superior' : 'Lateral';
					?>
						<tr class="<?php echo $clase; ?>">
							<TD><input type="hidden" name="id[]" value="<?php echo $anuncio['id']; ?>">
								<a href="?opt=banners&task=editarBanner&id=<?php echo $anuncio['id']; ?>"><img src="<?php echo $modulos->rutaPlantilla; ?>/images/listados/lapiz.png" border="0"></a>
							</td>
							<td>
								<a href="?opt=banners&task=borrarBanner&id=<?php echo $anuncio['id']; ?>"><img src="<?php echo $modulos->rutaPlantilla; ?>/images/listados/borrar.png" border="0"></a>
							</TD>
							<TD><?php echo $anuncio['titulo']; ?></TD>
							<TD><?php echo $texto; ?></TD>
							<td><?php echo $tipo; ?></td>
							<TD class="centrado"><?php echo $anuncio['fecha_alta']; ?></TD>
							<!--TD class="centrado"><?php echo $anuncio['visto']; ?></TD-->
							<TD class="centrado"><input type="checkbox" name="publicado[]" value="<?php echo $anuncio['id']; ?>" <?php if ($anuncio['publicado'] == 1) echo 'checked'; ?>></TD>
						</tr>
					<?php
					}
				?>
				<tr>
					<TD colspan="6" class="centrado"><?php
						echo paginador ($pagina, $totalPaginas, '?opt=banners&pag=@');
					?></TD>
				</tr>
			</table>
		</form>
		<?php
	} else {
		?>
			No hay banners.
		<?php
	}
}

/******************************
 * Graba el nuevo banner
 ******************************/
function grabar_nuevo_banner() {
	global $modulos, $dirImagenes, $dirMinis;
	
	// Lee los datos del banner
	$datosBanner['titulo'] = $modulos->getParam($_POST, 'titulo', '');
	$datosBanner['tipo'] = $modulos->getParam($_POST, 'tipo', -1);
	$datosBanner['url'] = $modulos->getParam($_POST, 'url', '');
	$datosBanner['fecha_fin'] = $modulos->getParam($_POST, 'fechaFinPub', '');
	$datosBanner['categoria'] = $modulos->getParam($_POST, 'categoria', -1);
	$datosBanner['imagen'] = -1;
	$datosBanner['tipo_imagen'] = -1;
	$datosBanner['id'] = -1;
	
	if ($datosBanner['tipo'] == C_BANNER_SUPERIOR) {
		$imagenMaxX = 460;
		$imagenMaxY = 80;
	} else {
		$imagenMaxX = 120;
		$imagenMaxY = 120;
	}
	
	// Determina si los ficheros recibidos son imagenes correctas
	$listaImagenes = procesar_imagenes_anuncio($_FILES, $dirImagenes, $dirMinis, 1, $imagenMaxX, $imagenMaxY);
	$idBanner = grabar_banner($datosBanner, true, $listaImagenes);
	lista_banners();
	
}

function borrar_banner() {
	global $modulos;
	
	$id = $modulos->getParam($_GET, 'id', -1);
	if (is_numeric($id)) {
		$banner = leer_banner($id);
		if (is_array($banner)) {
			formulario_nuevo_banner($banner, true);
		}
	}
}

function borrar_banner_confirmado() {
	global $modulos;
	
	$id = $modulos->getParam($_GET, 'id', -1);
	if (is_numeric($id)) {
		$banner = leer_banner($id);
		if (is_array($banner)) {
			borrar_imagenes_anuncio($id, 'b');
			$resultado = $modulos->inserta_sql('SQL_BANNER_BORRAR', $id);
		}
	}
	lista_banners();
}

?>