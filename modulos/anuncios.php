<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('anuncios.php');
require_once ('categorias.php');
require_once ('const_anuncios.php');
require_once ('panel.php');
require_once ('funciones.php');
require_once ('usuarios.php');
require_once ('menus.php');

// Protecci�n contra ejecuci�n sin usuario registrado. Salta autom�ticamente en cuanto se incluye el fichero
// Verifica que la sesi�n est� activa y que el usuario sea correcto.
global $nombreSesion;
$task = strtolower(Modulo::getParam($_GET, 'task', ''));
if (!(Clogin::existe_sesion() && Clogin::existe_usuario($_SESSION[$nombreSesion]['id'])) && $task != 'nuevo') {
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
		case 'confirmarborrar':
			borrar_anuncio();
			break;
		case 'borraranuncio':
			confirmacion_borrar_anuncio();
			break;
		case 'editaranuncio':
			editar_anuncio();
			break;
		case 'grabar':
			grabar_anuncio();
			break;
		case 'nuevo':
			formulario_anuncio();
			break;
		case 'modificar':
			modificar_anuncio();
			break;
		case 'indice':
		default:
			lista_anuncios();
			break;
	}
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
 * Editar un artículo
 ***********************************/
function editar_anuncio() {
	global $modulos;
	
	$idAnuncio = $modulos->getParam($_GET, 'id', -1);
	if (usuario_actual_es_admin() || pertenece_anuncio_usuario($_SESSION['datosUsuario']['id'], $idAnuncio)) {
		$datosAnuncio = leer_anuncio($idAnuncio);
		if (is_array($datosAnuncio)) {
			$datosAnuncio['texto'] = $datosAnuncio['texto'];
			formulario_anuncio($datosAnuncio, 'editar');
		}
	} else
		lista_anuncios();
}

/***********************************
 * Muestra el formulario para el nuevo anuncio
 ***********************************/
function formulario_anuncio($datosAnuncio = Array(), $tipo = 'nuevo', $id = -1, $esBanner=false) {
	global $modulos, $imagenMaxX, $imagenMaxY, $limiteFotosAnuncio;

	// Comprueba los datos del anuncio
	if (count($datosAnuncio) == 0) {
		$datosAnuncio['id'] = -1;
		$datosAnuncio['fechafinpub'] = $datosAnuncio['titulo'] =
		$datosAnuncio['texto'] = $datosAnuncio['fichero'] = '';
		$datosAnuncio['usuario'] = -1;
		$datosAnuncio['categoria'] = -1;
		$datosAnuncio['provincia'] = -1;
		$datosAnuncio['localidad'] = '';
		$datosAnuncio['telefono'] = '';
		$datosAnuncio['imagen_principal'] = -1;
		$datosAnuncio['precio_dia'] = $datosAnuncio['precio_semana'] = $datosAnuncio['precio_quincena'] =
			$datosAnuncio['precio_mes'] = '';
		$task = 'grabar';
		$textoBoton = 'Enviar Anuncio';
		$totalImagenes = 0;
	} else {
		if ($datosAnuncio['fechafinpub'] == '00/00/0000')
			$datosAnuncio['fechafinpub'] ='';
		$listaImagenes = leer_imagenes_anuncio($datosAnuncio['id']);
		$totalImagenes = count($listaImagenes);
		$task = 'modificar';
		$textoBoton = 'Editar Anuncio';
	}
	
	$tipo = strtolower($tipo);
	if ($tipo == 'nuevo')
		if ($esBanner)
			$tituloFormulario = 'Nuevo Banner';
		else
			$tituloFormulario = 'Nuevo Anuncio';
	else $tituloFormulario = 'Editar Anuncio';

	$listaCategorias = lista_categorias();
	$listaProvincias = lista_provincias();

	if (isset($_SESSION['datosUsuario']) && !empty($_SESSION['datosUsuario'])) {
		?>
			<script type="text/javascript" src="js/funciones_anuncios.js"></script>
			<style type="text/css">@import url(js/calendar/calendar-green.css);</style>
			<script type="text/javascript" src="js/calendar/calendar.js"></script>
			<script type="text/javascript" src="js/calendar/lang/calendar-es.js"></script>
			<script type="text/javascript" src="js/calendar/calendar-setup.js"></script>
			<script>
				modificado = false;
			</script>
			<div id="capaPausa" name="capaPausa">
				Espere un momento, por favor...
			</div>
			<form name="formAnuncio" id="formAnuncio" method="post" action="" class="formulario" enctype="multipart/form-data">
				<h3><?php echo $tituloFormulario; ?></h3>
				<fieldset>
					<div class="advertencia">
						<ul>
							<li>El título del anuncio tiene que describir el artículo con claridad.</li>
							<li>Inserta un solo anuncio por producto ofertado.</li>
							<li>No publiques el mismo anuncio en más de una región y/o categoría.</li>
							<li>Asegúrate de seleccionar la categoría correcta para el producto que ofreces.</li>
							<li>Todos los anuncios que no cumplan con las reglas de <a href="http://www.NombreEmpresa.com/">NombreEmpresa.com</a> no serán publicados.</li>
						</ul>
					</div>
					<input id="opt" name="opt" type="hidden" value="anuncios">
					<input id="task" name="task" type="hidden" value="<?php echo $task; ?>">
					<input id="tipo" name="tipo" type="hidden" value="<?php echo $esBanner ? C_BANNER : C_GRATUITO; ?>">
					<input id="id" name="id" type="hidden" value="<?php echo $datosAnuncio['id']; ?>">
					<label for="titulo">Titulo:</label><span class="campoObligatorio">*</span><input type="text" id="titulo" name="titulo" value="<?php echo $datosAnuncio['titulo']; ?>"><br>
					<label for="provincia">Provincia:</label>
					<span class="campoObligatorio">*</span><select id="provincia" name="provincia">
						<option value="-1">Seleccione una provincia</option>
						<?php
							foreach($listaProvincias as $idProvincia=>$nombreProvincia) {
								echo '<option value="'.$idProvincia.'"'.($datosAnuncio['provincia']==$idProvincia?' selected':'').'>'.$nombreProvincia['nombre'].'</option>';
							}
						?>
					</select>
					<label>Poblaci&oacute;n:</label>
					<span class="campoObligatorio">*</span><input type="text" name="localidad" id="localidad" maxlength="256" value="<?php echo $datosAnuncio['localidad']; ?>"><br>
					<label for="categoria">Categor&iacute;a:</label>
					<span class="campoObligatorio">*</span><select id="categoria" name="categoria">
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
								if ($categoria['id'] == $datosAnuncio['categoria'])
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
					<label for="texto">Anuncio:</label>
					<span class="campoObligatorio">*</span><textarea id="texto" name="texto" onclick="this.select();" onchange="modificado = true;" wrap="soft"><?php
							if ($datosAnuncio['texto']) {
								echo $datosAnuncio['texto'];
							} else { ?>Escriba el contenido del nuevo anuncio<?php
							}
					?></textarea><br>
					<label for="telefono">Tel&eacute;fono:</label><input type="text" name="telefono" id="telefono" maxlength="20" value="<?php echo $datosAnuncio['telefono']; ?>">
					<label for="precio_dia">Precio por d&iacute;a:</label><input type="text" name="precio_dia" id="precio_dia" maxlength="100" value="<?php echo $datosAnuncio['precio_dia']; ?>"> &euro;
					<label for="precio_semana">Precio por semana:</label><input type="text" name="precio_semana" id="precio_semana" maxlength="100" value="<?php echo $datosAnuncio['precio_semana']; ?>"> &euro;
					<label for="precio_quincena">Precio por quincena:</label><input type="text" name="precio_quincena" id="precio_quincena" maxlength="100" value="<?php echo $datosAnuncio['precio_quincena']; ?>"> &euro;
					<label for="precio_mes">Precio por mes:</label><input type="text" name="precio_mes" id="precio_mes" maxlength="100" value="<?php echo $datosAnuncio['precio_mes']; ?>"> &euro;
					<label>Im&aacute;genes:</label>
					<?php // Muestra las imágenes del anuncio
						for ($c = 0; $c < $limiteFotosAnuncio; $c++) {
							if ($c < $totalImagenes) { ?>
								<div class='listaMiniImagenes'>
									<input type="radio" name="principal" value="<?php echo $listaImagenes[$c]['id']; ?>" style="width: 10px; margin: 3px;" <?php if ($listaImagenes[$c]['id'] == $datosAnuncio['imagen_principal']) echo 'checked'; ?>>Imagen principal<br>
									<img style="display:inline;" src="ficheros/minis/<?php echo $listaImagenes[$c]['fichero']; ?>"><br>
									<input style="width: 10px; margin: 3px;" type="checkbox" name="borrar[]" value="<?php echo $listaImagenes[$c]['id']; ?>">Borrar la imagen
								</div>
							<?php
							}
						?>
							<input type="file" name="imagen<?php echo $c; ?>" size="45" class="ficheros">
						<?php
						}
					?>
					
					<div class="advertencia">
						Peso M&aacute;x.: <?php echo ini_get('upload_max_filesize'); ?><br>
						El servidor redimensionar&aacute; autom&aacute;ticamente cualquier imagen cuyo tama&ntilde;o supere
						las siguientes dimensiones: <?php echo "$imagenMaxX x $imagenMaxY" ; ?>
						p&iacute;xeles de ancho y alto. Las im&aacute;genes m&aacute;s peque&ntilde;as no se redimensionar&aacute;n.
					</div>
					<label for="fechaFinPub">Fecha de fin de la publicaci&oacute;n:</label><input id="fechaFinPub" name="fechaFinPub" type="text" value="<?php echo $datosAnuncio['fechafinpub']; ?>">
					<br><input type="checkbox" name="condiciones" id="condiciones" style="width: auto; margin: 0px; margin-top: 3px; margin-right: 3px;" <?php if ($task == 'modificar') echo 'checked'; ?>><label for="condiciones" style="display: inline;"><a href="condiciones.html" target="_new">Acepto las condiciones de uso y política de privacidad</a></label>
					<p class="cierre"><button type="button" onclick="verificar_anuncio_gratuito('formAnuncio', modificado, 'capaPausa');"><?php echo $textoBoton; ?></button> <button type="button" onclick="volver_anterior('?opt=anuncios');">Cancelar</button></p>
				</fieldset>
			</form>
			<script type="text/javascript">
				Calendar.setup(
					{
						inputField  : "fechaFinPub",         // ID of the input field
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
	} else {
		?>
		<div style="text-align: center; width: 100%;"><br>
			Es necesario entrar con su usuario antes de poder crear o editar un anuncio.<br>
			Haga 'login' con su usuario o <a href="?opt=login&task=registro">registre una cuenta en NombreEmpresa</a> y entre con ella.
		</div>
		<?php
	}	
}

/***************************************
 * Graba el anuncio en la base de datos
 ***************************************/
function grabar_anuncio() {
	global $modulos, $nombreSesion, $dirImagenes, $dirMinis;

	// Lee los datos del formulario
	$datosAnuncio = Array(
		'titulo' => $modulos->getParam($_POST, 'titulo', ''),
		'provincia' => $modulos->getParam($_POST, 'provincia', -1),
		'localidad' => $modulos->getParam($_POST, 'localidad', ''),
		'categoria' => $modulos->getParam($_POST, 'categoria', -1),
		'texto' => $modulos->getParam($_POST, 'texto', ''),
		'telefono' => $modulos->getParam($_POST, 'telefono', ''),
		'precio_dia' => $modulos->getParam($_POST, 'precio_dia', ''),
		'precio_semana' => $modulos->getParam($_POST, 'precio_semana', ''),
		'precio_quincena' => $modulos->getParam($_POST, 'precio_quincena', ''),
		'precio_mes' => $modulos->getParam($_POST, 'precio_mes', ''),
		'tipo' => strtolower($modulos->getParam($_POST, 'tipo', '')),
		'fechaFinPub' => $modulos->getParam($_POST, 'fechaFinPub', ''),
		'aceptaCondiciones' => $modulos->getParam($_POST, 'condiciones', '')
	);
	// verifica algunos errores antes de procesar el anuncio
	$error = OK;
	if ($datosAnuncio['titulo'] == '') // Titulo
		$error = E_ANUNCIO_NO_TITULO;
	if ($datosAnuncio['categoria'] == -1 || !is_numeric($datosAnuncio['categoria'])) // Categor�a
		$error |= E_ANUNCIO_NO_CATEGORIA;
	if ($datosAnuncio['texto'] == '') // Contenido del anuncio
		$error |= E_ANUNCIO_NO_TEXTO;
	if ($datosAnuncio['provincia'] == -1 || !is_numeric($datosAnuncio['provincia'])) // Provincia
		$error |= E_ANUNCIO_NO_PROVINCIA;
	if ($datosAnuncio['aceptaCondiciones'] == '')
		$error |= E_ANUNCIO_NO_CONDICIONES;
		
	// Valida las fechas. 
	/***
	 * Solo se aceptan fechas en formato dd/mm/aa y dd/mm/aaaa.
	 ***/
	// Valida que las fecha tengan un formato v�lido. Se considera un formato v�lido que la cadena est� vac�a
	$hoy = mktime(0, 0, 0);
	$fecha = verifica_fecha($datosAnuncio['fechaFinPub']);

	if ($fecha !== FALSE) {
		if (is_array($fecha)) {
			$datosAnuncio['fechaFinPub'] = convierte_fecha($datosAnuncio['fechaFinPub']);
			$fecha = convierte_fecha_a_unix($datosAnuncio['fechaFinPub']);
		} else $datosAnuncio['fechaFinPub'] = '';
		
		// valida que las fechas sean correctas (Hoy <= fechaInicio < fechaFin)
		if ($datosAnuncio['fechaFinPub']) {
			if ($fecha >= $hoy)
				//$error |= E_ANUNCIO_NO_FECHA;
				$datosAnuncio['fechaFinPub'] = '';
		}
	} else $datosAnuncio['fechaFinPub'] = '';
	// Procesa el anuncio
	if (!$error) {
		$listaImagenes = procesar_imagenes_anuncio($_FILES, $dirImagenes, $dirMinis, 5);
		$idAnuncio = guardar_nuevo_anuncio_gratuito($_SESSION[$nombreSesion]['id'], $datosAnuncio, true, $listaImagenes);
		lista_anuncios();
	} else {
		echo $error;
		formulario_anuncio();
	}
}

/*******************************************
 * Muestra la lista de anuncios
 * Determina que lista mostrar en base al tipo de usuario
 *******************************************/
function lista_anuncios() {
	// Muestra un listado de los anuncios
	listado_anuncios();
}

/********************************************
 * Muestra la lista de anuncios del usuario actual, si es administrador
 ********************************************/
function listado_anuncios() {
	global $modulos, $lineasListado;

	$pagina = $modulos->getParam($_POST, 'pag', -1);
	if ($pagina == -1)
		$pagina = $modulos->getParam($_GET, 'pag', 0);
	if ($pagina > 0)
		$inicio = $pagina * $lineasListado;
	else $inicio = 0;
	
	$idUsuario = $_SESSION['datosUsuario']['id'];
	if (usuario_actual_es_admin()) {
		$totalAnuncios = total_anuncios();
		$listaAnuncios = anuncios_usuario(-1, $lineasListado, $inicio);
	} else {
		$totalAnuncios = total_anuncios_usuario($idUsuario);
		$listaAnuncios = anuncios_usuario($idUsuario, $lineasListado, $inicio);
	}
	$totalPaginas = $totalAnuncios / $lineasListado;
	
	if ($totalAnuncios) {
		?>
		<script language="Javascript" type="text/javascript" src="js/funciones_anuncios.js"></script>
		<form name="anuncios" id="anuncios" method="post">
			<input type="hidden" id="opt" name="opt" value="anuncios">
			<input type="hidden" id="task" name="task" value="">
			<input type="hidden" id="pag" name="pag" value="<?php echo $pagina; ?>">
			Mostrando <?php echo count($listaAnuncios); ?> de <?php echo $totalAnuncios; ?> anuncios.
			<table class='listado'>
				<TR class='cabecera'>
					<Th colspan="2"></Th>
					<th>T&iacute;tulo</th>
					<th>Contenido</th>
					<th>Fecha de Creaci&oacute;n</th>
					<th>Veces visto</th>
					<th>Publicado</th>
					<?php
						if (usuario_actual_es_admin()) { ?>
							<th>Destacado</th>
							<th>Autor</th>
						<?php
						}
					?>
				</TR>
				<tr class="cabecera">
					<th colspan="6">&nbsp;</th>
					<th colspan="<?php echo usuario_actual_es_admin() ? '2' : '1'; ?>"><a href="#" onclick="cambiar_estado_anuncios();">Guardar cambios</a></th>
					<?php
						if (usuario_actual_es_admin()) { ?>
							<th>&nbsp;</th>
						<?php
						}
					?>
				</tr>
				<?php
					$linea = 1;
					foreach ($listaAnuncios as $anuncio) {
						$clase = ($linea++ & 1) ? 'linea1' : 'linea2';
						if (strlen($anuncio['texto']) > 15)
							$texto = substr($anuncio['texto'], 0, 12).'...';
						else $texto=$anuncio['texto'];
						if (strlen($anuncio['titulo']) > 15)
							$titulo = substr($anuncio['titulo'], 0, 12) . '...';
						else $titulo = $anuncio['titulo'];
					?>
						<tr class="<?php echo $clase; ?>">
							<TD><input type="hidden" name="id[]" value="<?php echo $anuncio['id']; ?>">
								<a href="?opt=anuncios&task=editarAnuncio&id=<?php echo $anuncio['id']; ?>"><img src="<?php echo $modulos->rutaPlantilla; ?>/images/listados/lapiz.png" border="0"></a>
							</td>
							<td>
								<a href="?opt=anuncios&task=borrarAnuncio&id=<?php echo $anuncio['id']; ?>"><img src="<?php echo $modulos->rutaPlantilla; ?>/images/listados/borrar.png" border="0"></a>
							</TD>
							<TD><?php echo $titulo; ?></TD>
							<TD><?php echo $texto; ?></TD>
							<TD class="centrado"><?php echo $anuncio['fechaalta']; ?></TD>
							<TD class="centrado"><?php echo $anuncio['visto']; ?></TD>
							<TD class="centrado"><input type="checkbox" name="publicado[]" value="<?php echo $anuncio['id']; ?>" <?php if ($anuncio['publicado'] == 1) echo 'checked'; ?>></TD>
							<?php
								if (usuario_actual_es_admin()) { ?>
									<TD class="centrado"><input type="checkbox" name="destacado[]" value="<?php echo $anuncio['id']; ?>" <?php if ($anuncio['tipo'] == C_DESTACADO) echo 'checked'; ?>></TD>
									<td><?php echo $anuncio['login']; ?></td>									
								<?php
								}
							?>
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
	} else { // El usuario no tiene ningún anuncio
		?>
			Todavía no ha publicado ningún anuncio.
		<?php
	}
}

/******************************************************
 * Verifica que el usuario pueda borrar el anuncio indicado. Si es así
 * entonces muestra la página de confirmación
 ******************************************************/
function confirmacion_borrar_anuncio() {
	global $modulos;
	
	$idAnuncio = $modulos->getParam($_GET, 'id', -1);
	// Primero se comprueba si el anuncio es del usuario, solo si
	// el usuario es un usuario normal
	if (usuario_actual_es_admin() || pertenece_anuncio_usuario($_SESSION['datosUsuario']['id'], $idAnuncio)) {
		if (!formulario_borrar_anuncio($idAnuncio))
			lista_anuncios();
	} else
		lista_anuncios();
}

/***********************************
 * Muestra el formulario con los datos y la confirmación de borrar el anuncio
 ***********************************/
function formulario_borrar_anuncio($idAnuncio) {
	global $modulos;

	// Carga los datos del anuncio
	$datosAnuncio = leer_anuncio($idAnuncio);
	if ($datosAnuncio === FALSE) {
		return false;
	} else {
		$listaCategorias = lista_categorias();
		$listaProvincias = lista_provincias();
		$imagenesAnuncio = leer_imagenes_anuncio($idAnuncio);
		$totalImagenes = count($imagenesAnuncio);

		if ($datosAnuncio['fechafinpub'] == '00/00/0000')
			$datosAnuncio['fechafinpub'] = '';
	

		?>
			<script type="text/javascript" src="js/funciones_anuncios.js"></script>
			<form name="formAnuncio" id="formAnuncio" method="post" action="" class="formulario" enctype="multipart/form-data">
				<h3>Anuncio a borrar</h3>
				<fieldset>
					<input id="opt" name="opt" type="hidden" value="anuncios">
					<input id="task" name="task" type="hidden" value="confirmarBorrar">
					<input id="id" name="id" type="hidden" value="<?php echo $datosAnuncio['id']; ?>">
					<label for="titulo">Titulo:</label><input type="text" id="titulo" name="titulo" value="<?php echo $datosAnuncio['titulo']; ?>" readonly>
					<label for="provincia">Provincia:</label><input type="text" id="provincia" name="provincia" value="<?php echo $listaProvincias[$datosAnuncio['provincia']]['nombre']; ?>" readonly />
					<label for="localidad">Poblaci&oacute;n:</label><input type="text" id="localidad" name="localidad" value="<?php echo $datosAnuncio['localidad']; ?>" readonly />
					<label for="categoria">Categor&iacute;a:</label>
					<select id="categoria" name="categoria" readonly>
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
								if ($categoria['id'] == $datosAnuncio['categoria'])
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
					<label for="texto">Anuncio:</label>
					<textarea id="texto" name="texto" readonly><?php
							if ($datosAnuncio['texto']) {
								echo $datosAnuncio['texto'];
							} else { ?>Escriba el contenido del nuevo anuncio<?php
							}
					?></textarea><br>
					<label for="telefono">Tel&eacute;fono:</label><input type="text" name="telefono" id="telefono" value="<?php echo $datosAnuncio['telefono']; ?>" readonly>
					<label for="precio_dia">Precio por d&iacute;a:</label><input type="text" name="precio_dia" id="precio_dia" value="<?php echo $datosAnuncio['precio_dia']; ?>" readonly> &euro;
					<label for="precio_semana">Precio por semana:</label><input type="text" name="precio_semana" id="precio_semana" value="<?php echo $datosAnuncio['precio_semana']; ?>" readonly> &euro;
					<label for="precio_quincena">Precio por quincena:</label><input type="text" name="precio_quincena" id="precio_quincena" value="<?php echo $datosAnuncio['precio_quincena']; ?>" readonly> &euro;
					<label for="precio_mes">Precio por mes:</label><input type="text" name="precio_mes" id="precio_mes" value="<?php echo $datosAnuncio['precio_mes']; ?>" readonly> &euro;
					<?php
						if ($totalImagenes) {
							?>
							<label for="imagen1">Imagen principal:</label>
							<img src="ficheros/minis/<?php echo $imagenesAnuncio[0]['fichero']; ?>">
							<?php
						}
						if ($totalImagenes > 1) {
							?>
							<label>Im&aacute;genes secundarias:</label>
							<div style="text-align: center; width: 100%;">
							<?php
							for ($c = 1 ; $c < $totalImagenes; $c++) {
								?>
								<div class='listaMiniImagenes'><img src='ficheros/minis/<?php echo $imagenesAnuncio[$c]['fichero']; ?>' id='imagen' name='imagen' /></div>
								<?php
							}
							?>
							</div>
							<?php
						}
					?>
					<label for="fechaFinPub">Fecha de fin de la publicaci&oacute;n:</label><input id="fechaFinPub" name="fechaFinPub" type="text" value="<?php echo $datosAnuncio['fechafinpub']; ?>" readonly>
					<p class="cierre"><button type="button" onclick="borrar_anuncio('formAnuncio');">Borrar anuncio</button> <button type="button" onclick="volver_anterior('?opt=anuncio');">Cancelar</button></p>
				</fieldset>
			</form>
		<?php
		return true;
	}
}

/*******************************************
 * Borra el anuncio indicado
 *******************************************/
function borrar_anuncio() {
	global $modulos;
	
	// Lectura de datos
	$idAnuncio = $modulos->getParam($_POST, 'id', -1);
	
	// Verificaciones de seguridad
	if (usuario_actual_es_admin() || pertenece_anuncio_usuario($_SESSION['datosUsuario']['id'], $idAnuncio)) {
		// lee el anuncio
		$datosAnuncio = leer_anuncio($idAnuncio);
		if ($datosAnuncio) { // Si existe, entonces se borra el anuncio y la imagen
			$resultado = $modulos->inserta_sql('SQL_BORRAR_ANUNCIO', $idAnuncio);
			borrar_imagenes_anuncio ($idAnuncio);
		} 
	}
	lista_anuncios();
}

/**********************************
 * Realiza el cambio de la publicacion
 **********************************/
function cambiar_publicacion() {
	global $modulos, $nombreSesion;
	
	$anunciosMostrados = $modulos->getParam($_POST, 'id', Array());
	$anunciosPublicados = $modulos->getParam($_POST, 'publicado', Array());
	$anunciosDestacados = $modulos->getParam($_POST, 'destacado', Array());
	
	// Verifica si se publicado o despublicado algún anuncio de la página indicada
	if (is_array($anunciosMostrados) && is_array($anunciosPublicados) && count($anunciosMostrados)) {
		// se recorre $anunciosMostrados. Si el elemento aparece en anunciosPublicados, entonces
		// hay que publicar el anuncio, sino hay que despublicarlo
		foreach ($anunciosMostrados as $anuncio) {
			// Modifica el estado de publicacion del anuncio
			if (usuario_actual_es_admin() || pertenece_anuncio_usuario($_SESSION[$nombreSesion]['id'], $anuncio))
				if (in_array($anuncio, $anunciosPublicados))
					publicar_anuncio($anuncio, C_PUBLICADO);
				else publicar_anuncio($anuncio, C_NO_PUBLICADO);
			
			// Modifica si el anuncio es destacado o normal
			if (usuario_actual_es_admin())
				if (in_array($anuncio, $anunciosDestacados) && tipo_anuncio($anuncio) != C_BANNER)
					cambiar_tipo_anuncio($anuncio, C_DESTACADO);
				else cambiar_tipo_anuncio($anuncio, C_GRATUITO);
		}
	}
	lista_anuncios();
}

/**************************************
 * Modifica el contenido de un anuncio
 **************************************/
function modificar_anuncio() {
	global $modulos, $nombreSesion, $dirImagenes, $dirMinis;

	// Verifica que el anuncio a modificar exista
	$idAnuncio = $modulos->getParam($_POST, 'id', -1);

	if (is_numeric($idAnuncio)) {
		if (usuario_actual_es_admin() || $_SESSION[$nombreSesion]['id'] == $datosAnuncio['usuario']) {
			$datosAnuncio = leer_anuncio($idAnuncio);
			$listaImagenes = leer_imagenes_anuncio($idAnuncio);
			
			// Verifica que datos han cambiado
			$titulo = $modulos->getParam($_POST, 'titulo', '');
			$provincia = $modulos->getParam($_POST, 'provincia', -1);
			$localidad = $modulos->getParam($_POST, 'localidad', '');
			$categoria = $modulos->getParam($_POST, 'categoria', -1);
			$texto = $modulos->getParam($_POST, 'texto', '');
			$telefono = $modulos->getParam($_POST, 'telefono', '');
			$precioDia = $modulos->getParam($_POST, 'precio_dia', '');
			$precioSemana = $modulos->getParam($_POST, 'precio_semana', '');
			$precioQuincena = $modulos->getParam($_POST, 'precio_quincena', '');
			$precioMes = $modulos->getParam($_POST, 'precio_mes', '');
			$fechaFinPub = $modulos->getParam($_POST, 'fechaFinPub', '');
			$datosAnuncio['fechaFin'] = $datosAnuncio['fechafinpub'];
			$imagenPrincipal = $modulos->getParam($_POST, 'principal', -1);
			$aBorrar = $modulos->getParam($_POST, 'borrar', Array());

			if ($titulo != $datosAnuncio['titulo'])
				$datosAnuncio['titulo'] = $titulo;
			if ($provincia != $datosAnuncio['provincia'])
				$datosAnuncio['provincia'] = $provincia;
			if ($localidad != $datosAnuncio['localidad'])
				$datosAnuncio['localidad'] = $localidad;
			if ($categoria != $datosAnuncio['categoria'])
				$datosAnuncio['categoria'] = $categoria;
			if ($texto != $datosAnuncio['texto'])
				$datosAnuncio['texto'] = $texto;
			if ($telefono != $datosAnuncio['telefono'])
				$datosAnuncio['telefono'] = $telefono;
			if ($precioDia != $datosAnuncio['precio_dia'])
				$datosAnuncio['precio_dia'] = $precioDia;
			if ($precioSemana != $datosAnuncio['precio_semana'])
				$datosAnuncio['precio_semana'] = $precioSemana;
			if ($precioQuincena != $datosAnuncio['precio_quincena'])
				$datosAnuncio['precio_quincena'] = $precioQuincena;
			if ($precioMes != $datosAnuncio['precio_mes'])
				$datosAnuncio['precio_mes'] = $precioMes;
			if ($fechaFinPub != $datosAnuncio['fechaFin']) {
				$aux = verifica_fecha($fechaFinPub);
				if (is_array($aux)) {
					$fechaUnix = convierte_fecha_a_unix($fechaFinPub);
					$hoy = mktime(0, 0, 0);
					if ($fechaUnix < $hoy)
						$datosAnuncio['fechaFin'] = '';
					else $datosAnuncio['fechaFin'] = convierte_fecha($fechaFinPub);
				} else $datosAnuncio['fechaFin'] = '';
			}
			$datosAnuncio['fechaFinPub'] = $datosAnuncio['fechaFin'];

			// Procesa las nuevas imagenes y las inserta en la base de datos
			$nuevasImagenes = procesar_imagenes_anuncio($_FILES, $dirImagenes, $dirMinis, 5);

			if (is_array($listaImagenes)) {
				$aux = Array();
				for ($c = 0; $c < count($listaImagenes); $c++) {
					// Mira que imágenes hay que borrar
					if (in_array($listaImagenes[$c]['id'], $aBorrar) || $nuevasImagenes[$c]['error'] == 0) {
						$aux[] = $listaImagenes[$c]['id'];
						$listaImagenes[$c]['id'] = -1;
					}
				}
					
				// Borra las imágenes del disco y de la base de datos
				foreach($aux as $idImagen)
					borrar_imagen($idImagen);

				// Guarda las imagenes nuevas en la base de datos
				for ($c = 0; $c < count($nuevasImagenes); $c++) {
					if ($nuevasImagenes[$c]['error'] == 0) {
						$resultado = $modulos->inserta_sql ('SQL_NUEVA_IMAGEN_ANUNCIO', Array($idAnuncio, $nuevasImagenes[$c]['name'], 'a'));
					}
				}
					
				// Mira si se ha cambiado la imagen principal
				if (is_numeric($imagenPrincipal)) {
					if (in_array($imagenPrincipal, $listaImagenes)) {
						$resultado = $modulos->inserta_sql('SQL_ANUNCIO_MODIFICA_IMAGEN_PRINCIPAL', Array($imagenPrincipal, $idAnuncio));
					} else { // Si no está definido, entonces se coge la primera imagen
						$listaImagenes = leer_imagenes_anuncio($idAnuncio);
						if (is_array($listaImagenes) && count($listaImagenes))
							$resultado = $modulos->inserta_sql('SQL_ANUNCIO_MODIFICA_IMAGEN_PRINCIPAL', Array($listaImagenes[0]['id'], $idAnuncio));
					}
				} else { // Si no es numerico se coge la primera imagen también
					$listaImagenes = leer_imagenes_anuncio($idAnuncio);
					$resultado = $modulos->inserta_sql('SQL_ANUNCIO_MODIFICA_IMAGEN_PRINCIPAL', Array($listaImagenes[0]['id'], $idAnuncio));
				}
			}

			$idAnuncio = guardar_nuevo_anuncio_gratuito($datosAnuncio['usuario'], $datosAnuncio, true, false);
			?>
				<script language='javascript'>
					document.location='index.php?opt=anuncios';
				</script>
			<?php
		}
	}
}
?>