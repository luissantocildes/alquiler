<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once('panel.php');
require_once('categorias.php');
require_once('anuncios.php');

// Protecci�n contra ejecuci�n sin usuario registrado. Salta autom�ticamente en cuanto se incluye el fichero
// Verifica que la sesi�n est� activa y que el usuario sea correcto.
global $nombreSesion;
$task = strtolower(Modulo::getParam($_GET, 'task', ''));
if (!(Clogin::existe_sesion() && Clogin::existe_usuario($_SESSION[$nombreSesion]['id']))) {
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
		case 'modificarcategoria':
			modificacion_categoria();
			break;
		case 'nuevasubcategoria':
			creacion_subcategoria();
			break;
		case 'nuevacategoria':
			creacion_categoria();
			break;
		case 'borrarcategoria':
			borrado_categoria();
			break;
		default: echo $task;
			listado_categorias();
			break;
	}
}

/********************************
 * Genera el men� del m�dulo
 ********************************/
function menu_modulo() {
	global $nombreSesion;
	
	menu_panel();
}

/**********************************
 * Crea una subcategoria
 **********************************/
function creacion_subcategoria() {
	global $modulos;

	$padre = $modulos->getParam($_POST, 'padre', 0);
	$nombre = $modulos->getParam($_POST, 'nombreCategoria', '');
	$descripcion = $modulos->getParam($_POST, 'descripcionCategorias', '');
	if (is_numeric($padre)) {
		// Determina si el padre es de primer nivel
		$datosCategoria = datos_categoria($padre);
		if (is_array($datosCategoria[0])) {
			if ($datosCategoria[0]['padre'] == 0) {
				$resultado = $modulos->inserta_sql ('SQL_NUEVA_SUBCATEGORIA', array($nombre, $descripcion, $padre));
			} else {
				?>
					<span class="textoError">Solo puede crear una subcategor&iacute;a de una categor&iacute;a principal.</span>
				<?php
			}
		}
	}
	listado_categorias();
}

/**********************************
 * Modifica una subcategoria
 **********************************/
function modificacion_categoria() {
	global $modulos, $dirImagenes;;

	$nombre = $modulos->getParam($_POST, 'nombreCategoria', '');
	$descripcion = $modulos->getParam($_POST, 'descripcionCategorias', '');
	$idCategoria = $modulos->getParam($_POST, 'idCategoria', -1);
	$borrarImagen = $modulos->getParam($_POST, 'borrarImagen', '');
	
	// copia la imagen, si la hay
	if (is_numeric($idCategoria) && $nombre) {
		$datosCategoria = datos_categoria($idCategoria);

		if (is_uploaded_file($_FILES['nuevaImagen']['tmp_name']) && $datosCategoria[0]['padre'] == 0) {
			$imagen = $_FILES['nuevaImagen'];
			// solo la copia si es una imagen válida
			$datosImagen = getimagesize($imagen['tmp_name']);
			if ($datosImagen[2] && (IMAGETYPE_JPEG | IMAGETYPE_PNG | IMAGETYPE_GIF )) {
				$fichero = $imagen['tmp_name'];
				$hoy = mktime(0, 0, 0);
				$nombreTemporal = tempnam($dirImagenes, 'anuncio_'.$hoy.'_');
				if ($nombreTemporal) { // Si el fichero único se ha creado, entonces copia la imagen (o la redimensiona) y genera la imagen pequeña
					// Genera el nombre correcto del fichero
					$nombreImagen = end(explode(DIRECTORY_SEPARATOR, $nombreTemporal));
					switch ($datosImagen[2]) {
						case IMAGETYPE_JPEG:
							$extension = 'jpg';
							break;
						case IMAGETYPE_PNG:
							$extension = 'png';
							break;
						case IMAGETYPE_GIF:
							$extension = 'gif';
							break;
						default:
							if ($aux == 'swf') {
								$extension = 'swf'; 
						}
					}
					if (isset($extension)) {
						$aux = explode('.', $nombreImagen);
						if (count($aux) > 1)
							array_pop($aux);
						$nombreImagen = implode('.', $aux) . '.' . $extension;			
						rename($fichero, $dirImagenes.'/categorias/'.$nombreImagen);
						chmod ($dirImagenes.'/categorias/'.$nombreImagen, 0666);
						unlink($nombreTemporal);
					}
				}
			}
		}
	
		if (is_array($datosCategoria)) {
			$resultado = $modulos->inserta_sql('SQL_MODIFICAR_CATEGORIA', Array($nombre, $descripcion, $idCategoria));
			if ($borrarImagen) {
				unlink($dirImagenes.'categorias/'.$datosCategoria[0]['imagen']);
				$resultado = $modulos->inserta_sql('SQL_MODIFICAR_CATEGORIA_BORRAR_IMAGEN', $idCategoria);
			} else {
				if (isset($nombreImagen) && $datosCategoria[0]['padre'] == 0) {
					if ($datosCategoria[0]['imagen']) {
						unlink($dirImagenes.'categorias/'.$datosCategoria[0]['imagen']);
					}
					$resultado = $modulos->inserta_sql('SQL_MODIFICAR_CATEGORIA_IMAGEN', Array($nombreImagen, $idCategoria));
				}
			}
		}
	}
	listado_categorias();
}

/**********************************
 * Borra una categoria
 **********************************/
function borrado_categoria() {
	global $modulos, $dirImagenes;

	$idCategoria = $modulos->getParam($_POST, 'idCategoria', -1);
	// Verifica que la categoria no contenga anuncios
	if (is_numeric($idCategoria)) {
		$totalAnuncios = total_anuncios_categoria($idCategoria);
		$totalSubCategorias = total_subcategorias($idCategoria);
		if ($totalAnuncios || $totalSubCategorias) {
			?>
				<div class='error'>No se puede borrar la categor&iacute;a porque hay anuncios y/o subcategor&iacute;as bajo esa categor&iacute;a.</div>
			<?php
		} else {
			$datosCategoria = datos_categoria ($idCategoria);
			if ($datosCategoria[0]['imagen'])
				unlink($dirImagenes.'categorias/'.$datosCategoria[0]['imagen']);
			$resultado = $modulos->inserta_sql('SQL_BORRAR_CATEGORIA', $idCategoria);
		}
	}
	listado_categorias();
}

/**********************************
 * Crea una categoria
 **********************************/
function creacion_categoria() {
	global $modulos, $dirImagenes;

	$nombre = $modulos->getParam($_POST, 'nombreCategoria', '');
	
	// copia la imagen, si la hay
	if (is_uploaded_file($_FILES['nuevaImagen']['tmp_name'])) {
		$imagen = $_FILES['nuevaImagen'];
		// solo la copia si es una imagen válida
		$datosImagen = getimagesize($imagen['tmp_name']);
		if ($datosImagen[2] && (IMAGETYPE_JPEG | IMAGETYPE_PNG | IMAGETYPE_GIF )) {
			$fichero = $imagen['tmp_name'];
			$hoy = mktime(0, 0, 0);
			$nombreTemporal = tempnam($dirImagenes, 'anuncio_'.$hoy.'_');
			if ($nombreTemporal) { // Si el fichero único se ha creado, entonces copia la imagen (o la redimensiona) y genera la imagen pequeña
				// Genera el nombre correcto del fichero
				$nombreImagen = end(explode(DIRECTORY_SEPARATOR, $nombreTemporal));
				switch ($datosImagen[2]) {
					case IMAGETYPE_JPEG:
						$extension = 'jpg';
						break;
					case IMAGETYPE_PNG:
						$extension = 'png';
						break;
					case IMAGETYPE_GIF:
						$extension = 'gif';
						break;
					default:
						if ($aux == 'swf') {
							$extension = 'swf'; 
					}
				}
				if (isset($extension)) {
					$aux = explode('.', $nombreImagen);
					if (count($aux) > 1)
						array_pop($aux);
					$nombreImagen = implode('.', $aux) . '.' . $extension;			
					rename($fichero, $dirImagenes.'/categorias/'.$nombreImagen);
					chmod ($dirImagenes.'/categorias/'.$nombreImagen, 0666);
					unlink($nombreTemporal);
				}
			}
		}
	}
	
	$descripcion = $modulos->getParam($_POST, 'descripcionCategoria', '');
	// Crea la categoria
	if (isset($nombreImagen))
		$resultado = $modulos->inserta_sql('SQL_NUEVA_CATEGORIA_IMAGEN', Array($nombre, $descripcion, $nombreImagen));
	else
		$resultado = $modulos->inserta_sql('SQL_NUEVA_CATEGORIA', Array($nombre, $descripcion));
	
	listado_categorias();
}

/**********************************
 * Muestra el índice, con la pantalla principal de categorías
 **********************************/
function listado_categorias() {
	global $modulos;

	$listaCategorias = lista_categorias();
	if (is_array($listaCategorias)) {
		?>
		<style>
			#listaCategorias {
				float: left;
				margin: 2px 2px 2px 2px;
				padding: 2px 2px 2px 2px;
			}

			.listaCategorias {
				height: 350px;
			}

			#zonaCategoriasTrabajo {
				margin: 2px 2px 2px 2px;
				padding: 2px 2px 2px 2px;
			}
		</style>
		<fieldset>
			<form name="categorias" class="formulario" method="POST" enctype="multipart/form-data" id="categorias">
				<div id='capaCategorias'>
					Categor&iacute;as:<br>
					<select name="listaCategorias" id="listaCategorias" class="listaCategorias" multiple="true" onchange="cambia_datos()">
						<?php
							$padre=-1;
							foreach($listaCategorias as $categoria) {
								if ($categoria['padre'] != $padre) {
									$padre = $categoria['padre'];
								}
								if ($categoria['padre']) {
									?>
										<option class='optionSubcategoria' value="<?php echo $categoria['id']; ?>"><?php echo $categoria['nombre']; ?></option>
									<?php
								} else {
									?>
										<option class='optionCategoria' value="<?php echo $categoria['id']; ?>">* <?php echo $categoria['nombre']; ?></option>
									<?php
									$padre = $categoria['id'];
								}
							}
						?>
					</select>
				</div>
				<div id="zonaCategoriasTrabajo">
					<label for="nombreCategoria">Nombre:</label> <input type="text" id="nombreCategoria" name="nombreCategoria" value=""><br>
					<label for="descripcionCategorias">Descripci&oacute;n:</label>
					<textarea name="descripcionCategorias" id="descripcionCategorias"></textarea><br><br>
					<label>Imagen (Tama&ntilde;o m&aacute;ximo: 20x20 pixels):</label>
					<img src="pixel.gif" id="imagenCategoria" name="imagenCategoria"><br>
					<input type="checkbox" name="borrarImagen" style="width: 20px;">Borrar la imagen.
					<input type="file" name="nuevaImagen">

					<p class="cierre">
						<button type="button" value="" onclick="nueva_categoria();">Crear Categor&iacute;a</button>
						<button type="button" value="" onclick="nueva_subcategoria();">Crear subcategor&iacute;a</button><br>
						<button type="button" value="" onclick="cambiar_categoria();">Modificar Categor&iacute;a</button>
						<button type="button" value="" onclick="borrar_categoria();">Borrar Categor&iacute;a</button><br><br>
					</p>
					<input type="hidden" id='opt' name="opt" value="categorias">
					<input type="hidden" name="task" id="task" value="">
					<input type="hidden" name="padre" id="padre" value="">
					<input type="hidden" name="idCategoria" id="idCategoria" value="">
				</div>
				<!--p class='cierre'><input type="submit" value="Aceptar cambios"> <input type="button" value="Cancelar" onclick="document.location='?opt=panel';"></p-->
			</form>
		</fieldset>
		<script language="JavaScript" type="text/javascript">
			var lista = document.getElementById('listaCategorias');
			var nombre = document.getElementById('nombreCategoria');
			var imagenActual = document.getElementById('imagenCategoria');
			var descripcion = document.getElementById('descripcionCategorias');
			//var f_task = document.getElementById('task');
			var formulario = document.getElementById('categorias');
			var f_task = formulario.task;
			var f_padre = document.getElementById('padre');
			var id = document.getElementById('idCategoria');
			var descripciones = Array(
				<?php
					foreach ($listaCategorias as $categoria) {
						?> Array(<?php echo $categoria['id'] .', "'. nl2br($categoria['descripcion']).'", "';
							if ($categoria['imagen']) 
								echo 'ficheros/categorias/'.$categoria['imagen'];
							echo '"'; ?>),
						<?php
					}
				?>
				Array()
			);
			
			function nueva_categoria() {
				if (nombre.value != '') {
					f_task.value='nuevaCategoria';
					formulario.submit();
				} else {
					alert ('Escriba un nombre y una descripción');
				}
			}

			function borrar_categoria() {
				if (lista.selectedIndex == -1)
					alert ('Escoja una categoría, por favor');
				else {
					f_task.value='borrarCategoria';
					formulario.submit();
				}
			}

			function cambiar_categoria() {
				if (lista.selectedIndex == -1)
					alert ('Escoja una categoría, por favor');
				else if (nombre.value != '') {
					f_task.value='modificarCategoria';
					id.value = lista.options[lista.selectedIndex].value;
					formulario.submit();
				} else
					alert ('Escriba un nuevo nombre para la categoría');
			}

			function nueva_subcategoria() {
				if (lista.selectedIndex == -1)
					alert ('Escoja una categoría padre, por favor');
				else if (nombre.value != '') {
					// Busca los datos del padre
					var escogido = -1;
					for (var c = 0; c < lista.options.length && c != lista.selectedIndex; c++) ;
					f_padre.value = lista.options[c].value;
				
					f_task.value = 'nuevaSubCategoria';
					formulario.submit();
				}
			}

			function cambia_datos() {
				var cadenaNombre = new String(lista.options[lista.selectedIndex].text);
				if (cadenaNombre.charAt(0) == '*')
					nombre.value = cadenaNombre.substring(2);
				else
					nombre.value = cadenaNombre;

				//nombre.value = lista.options[lista.selectedIndex].text;
				id.value = lista.options[lista.selectedIndex].value;
				
				// Busca la descripcion
				c = 0;
				while (c < descripciones.length && descripciones[c][0] != id.value)
					c++;
				if (c < descripciones.length) {
					descripcion.value = descripciones[c][1];
					if (descripciones[c][2] != '') {
						imagenActual.src = descripciones[c][2];
					} else {
						imagenActual.src = 'pixel.gif';
					}
				}
			}
		</script>
		<?php
	}
	
}

?>