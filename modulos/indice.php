<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('anuncios.php');
require_once ('funciones.php');
require_once ("categorias.php");

/************************
 * M�dulo Indice
 * Muestra los anuncios de la p�gina principal y de las categor�as
 ************************/
 
// Determina la provincia
global $modulos;

$provincia = Modulo::getParam($_POST, 'provincia', -2);
if ($provincia == -2)
	$provincia = Modulo::getParam($_GET, 'provincia', -2);

if (isset ($_SESSION['provincia'])) {
	if ($provincia == -2)
		$provincia = $_SESSION['provincia'];

	if ($_SESSION['provincia'] != $provincia)
		$_SESSION['provincia'] = $provincia;
} else {
	if ($provincia == -2)
		$provincia = -1;
	$_SESSION['provincia'] = $provincia;
}
 
function funcion_modulo() {
    global $modulos;
	
	$task = strtolower($modulos->getParam($_GET, 'task', ''));
	if ($task == '')
		$task = strtolower($modulos->getParam($_POST, 'task', ''));

	switch ($task) {
		case 'buscar':
			buscar_anuncios_palabras();
			break;
		case 'anuncio':
			mostrar_anuncio();
			break;
		case 'categoria':
		default:
			listado_inicial();
			break;
	}
}

/******************************
 * Busca anuncios por palabras
 ******************************/
function buscar_anuncios_palabras() {
	global $modulos, $nombreSesion;

	$palabras = $modulos->getParam($_GET, 'busquedaPalabras', '');

	?>
		<script language="JavaScript" type="text/javascript">
			var aux=document.getElementById('busquedaPalabras');

			aux.value = '<?php echo $palabras; ?>';
		</script>
	<?php
	if (isset($_SESSION['busqueda']) && $_SESSION['busqueda']['cadena'] == $palabras) {
		$anuncios = $_SESSION['busqueda']['resultados'];
		$arrayPalabras = $_SESSION['busqueda']['terminos'];
	} else {
		$arrayPalabras = explode(' ', $palabras);
		// Elimina las palabras repetidas
		$copiaPalabras = Array();
		while (count($arrayPalabras)) {
			$palabra = strtoupper(array_pop($arrayPalabras));
			if (!in_array($palabra, $copiaPalabras))
				$copiaPalabras[] = $palabra;
		}
		$arrayPalabras = &$copiaPalabras;
		$_SESSION['busqueda']['cadena'] = $palabras;
		$_SESSION['busqueda']['terminos'] = $arrayPalabras;
	}
		
	// Busca todos los anuncios que contengan las palabras buscadas y los guarda en un array
	$anuncios = Array();
	if (count($arrayPalabras)) {
		foreach ($arrayPalabras as $palabra) {
			$aux = "%$palabra%";
			$resultado = $modulos->resultado_sql('SQL_ANUNCIOS_UNA_PALABRA', Array($aux, $aux));
			foreach($resultado as $idAnuncio) {
				if (!in_array($idAnuncio['id'], $anuncios))
					$anuncios[] = $idAnuncio['id'];
			}
		}
		$_SESSION['busqueda']['resultados'] = $anuncios;

	} //else
		//listado_inicial();

		// Ahora se muestran los resultados
	if (count($anuncios)) {
		?><p>Encontrados <?php echo count($anuncios); ?> resultados.</p><?php
	} else {
		?><p>No se han encontrado resultados.</p><?php
	}
	$datosAnuncios = Array();
	foreach ($anuncios as $idAnuncio) {
		$datos = leer_anuncio($idAnuncio);
		$aux = leer_imagenes_anuncio($idAnuncio);
		if (isset($aux[0]['fichero'])) {
			$datos['fichero'] = $aux[0]['fichero'];
		} else $datos['fichero'] = '';
		$datosAnuncios[] = $datos;
	}
	dibujar_anuncios($datosAnuncios);

}
	
/******************************
 * Muestra el �ndice principal o el de una categor�a
 ******************************/
function listado_inicial() {
	global $modulos, $nombreSesion;
	
	// Se determina la provincia por la que filtrar
	$provincia = $_SESSION['provincia'];

	// Se lee el id de la categor�a a mostrar. Si es 0 o no existe se mostrar� el �ndice principal
	// (anuncion destacados + �ltimos anuncios), sino se muestra el �ndice de la categor�a.
	$categoria = $modulos->getParam($_GET, 'id', 0);
	if ($provincia != -1 && !$categoria)
		lista_provincia($provincia);
	else if ($categoria)
		lista_categoria($categoria, $provincia);
	else
		pagina_principal($provincia);
}

/********************************
 * Muestra el listado de la provincia
 ********************************/
function lista_provincia ($provincia) {
	global $modulos, $limiteDestacadosCategoria, $limiteAnunciosCategoria;

	$datosProvincia = $modulos->resultado_sql('SQL_PROVINCIA', $provincia);
	$datosProvincia = $datosProvincia[0];
		
	$pagina = $modulos->getParam($_GET, 'pag', 0);
	if (is_numeric($pagina) && $pagina >= 0)
		$inicio = $limiteAnunciosCategoria * $pagina;
	else {
		$inicio = 0;
		$pagina = 0;
	}

	$anuncios = leer_anuncios(-1, $limiteAnunciosCategoria, $inicio, false, $provincia);
	//$destacados = leer_anuncios($idCategoria, $limiteDestacadosCategoria, 0, true, $provincia);
	//$anuncios = $modulos->resultado_sql('SQL_ANUNCIOS_CATEGORIA_HIJOS', Array($idCategoria, $idCategoria));
	//$destacados = $modulos->resultado_sql('SQL_ANUNCIOS_DESTACADOS_CATEGORIA_HIJOS', Array($idCategoria, $idCategoria));

	$totalAnuncios = $modulos->resultado_sql('SQL_TOTAL_ANUNCIOS_PROVINCIA', $provincia);
	// Cuenta el total de anuncios
	if (is_array($totalAnuncios) && count($totalAnuncios) == 1)
		$totalAnuncios = $totalAnuncios[0]['total'];
	
	// Ok, ahora se genera el listado
	?>
		<p class='seccionAnuncio' style="padding-left: 20px; text-align: left;">
		<?php
			echo $datosProvincia['nombre'];
		?>
		</p>
	<?php

	// Muestra los anuncios destacados de la categoria
	if (is_array($destacados) && count($destacados)) {
		dibujar_destacados($destacados);
	}

	// Muestra los anuncios normales de la categoria
	if (is_array($anuncios) && count($anuncios)) {
		dibujar_anuncios($anuncios);
	}

	if ($totalAnuncios > $limiteAnunciosCategoria) {
		?>
			<div class="centrado">
		<?php
		echo paginador($pagina, ceil ($totalAnuncios / $limiteAnunciosCategoria), '?opt=indice&pag=@&id='.$idCategoria.'&provincia='.$provincia);
		?>
			</div>
		<?php
	}
}

/********************************
 * Muestra el listado de la categor�a
 ********************************/
function lista_categoria ($idCategoria, $provincia) {
	global $modulos, $limiteDestacadosCategoria, $limiteAnunciosCategoria;
	
	// Se comprueba si la categor�a existe
	if (existe_categoria($idCategoria)) {
		// Ok la categor�a existe, se leen los primeros anuncios, los destacados y el total de anuncios
		// Datos de la categor�a actual
		$padre = false;
		$categoria = datos_categoria($idCategoria);
		if (is_array($categoria) && count($categoria)) {
			$categoria = $categoria[0];
			// Verifica si la categoria tiene padre
			if ($categoria['padre']) {
				$padre = datos_categoria($categoria['padre']);
				if (is_array($padre) && count($padre))
					$padre = $padre['0'];
			}
		}
		
		$pagina = $modulos->getParam($_GET, 'pag', 0);
		if (is_numeric($pagina) && $pagina >= 0)
			$inicio = $limiteAnunciosCategoria * $pagina;
		else {
			$inicio = 0;
			$pagina = 0;
		}

		$anuncios = leer_anuncios($idCategoria, $limiteAnunciosCategoria, $inicio, false, $provincia);
		$destacados = leer_anuncios($idCategoria, $limiteDestacadosCategoria, 0, true, $provincia);
		//$anuncios = $modulos->resultado_sql('SQL_ANUNCIOS_CATEGORIA_HIJOS', Array($idCategoria, $idCategoria));
		//$destacados = $modulos->resultado_sql('SQL_ANUNCIOS_DESTACADOS_CATEGORIA_HIJOS', Array($idCategoria, $idCategoria));
		
		if ($provincia == -1)
			$totalAnuncios = $modulos->resultado_sql('SQL_TOTAL_ANUNCIOS_CATEGORIA_HIJOS', Array($idCategoria, $idCategoria));
		else $totalAnuncios = $modulos->resultado_sql('SQL_TOTAL_ANUNCIOS_CATEGORIA_PROVINCIA_HIJOS', Array($idCategoria, $idCategoria, $provincia));
		// Cuenta el total de anuncios
		if (is_array($totalAnuncios) && count($totalAnuncios) == 1)
			$totalAnuncios = $totalAnuncios[0]['total'];

		// Ok, ahora se genera el listado
		?>
			<p class='seccionAnuncio' style="padding-left: 20px; text-align: left;">
			<?php 
				if ($padre)
					echo $padre['nombre']." > ";
				echo $categoria['nombre'];
			?>
			</p>
		<?php

		// Muestra los anuncios destacados de la categoria
		if (is_array($destacados) && count($destacados)) {
			dibujar_destacados($destacados);
		}
		
		// Muestra los anuncios normales de la categoria
		if (is_array($anuncios) && count($anuncios)) {
			dibujar_anuncios($anuncios);
		}
		
		if ($totalAnuncios > $limiteAnunciosCategoria) {
			?>
				<div class="centrado">
			<?php
			echo paginador($pagina, ceil ($totalAnuncios / $limiteAnunciosCategoria), '?opt=indice&pag=@&id='.$idCategoria.'&provincia='.$provincia);
			?>
				</div>
			<?php
		}
		
	} else { // Si la categor�a no existe, se muestra la p�gina principal
		pagina_principal();
	}
}

/********************************
 * Pinta los anuncios destacados
 ********************************/
function dibujar_destacados($listaAnuncios) {
	global $modulos;
	
	if (is_array($listaAnuncios) && count ($listaAnuncios)) {
		foreach ($listaAnuncios as $anuncio) {
			$precio = '';
			if ($anuncio['precio_dia'])
				$precio .= ' - Precio: '.$anuncio['precio_dia'].' &euro;/dia';
			else if ($anuncio['precio_semana'])
				$precio .= ' - Precio: '.$anuncio['precio_semana'].' &euro;/semana';
			else if ($anuncio['precio_quincena'])
				$precio .= ' - Precio: '.$anuncio['precio_quincena'].' &euro;/quincena';
			else if ($anuncio['precio_mes'])
				$precio .= ' - Precio: '.$anuncio['precio_mes'].' &euro;/mes';

		?>
			<table class="anuncioDestacado" style="width: 96%;">
				<tr>
					<td style="width: 125px; padding: 10px; background-color: #FFFF99; text-align: center;" cellpadding=0 cellspacing=0>
						<?php
							if ($anuncio['fichero']) {
							?>
								<a href="?opt=indice&task=anuncio&id=<?php echo $anuncio['id']; ?>">
									<img src="ficheros/minis/<?php echo $anuncio['fichero']; ?>" style="border: 0px none;">
								</a>
							<?php
							} else {
							?>
								<a href="?opt=indice&task=anuncio&id=<?php echo $anuncio['id']; ?>">
									<img src="<?php echo $modulos->rutaPlantilla; ?>images/no-imagen.gif" style="border: 0px none;">
								</a>
							<?php
							}
						?>
					</td>
					<td style="padding-left: 10px;" valign="middle">
						<a href="?opt=indice&task=anuncio&id=<?php echo $anuncio['id']; ?>" style="text-decoration: none; color: #000066;">
							<span class="tituloAnuncio"><?php echo $anuncio['titulo']; ?></span>
							<span class="precioTitulo"><?php echo $precio; ?></span><br>
							Categor&iacute;a: <?php echo $anuncio['nombre_categoria']; ?><br>
							Provincia: <?php echo $anuncio['nombre_provincia']; ?><br>
							Localidad: <?php echo $anuncio['localidad']; ?>
						</a>
					</td>
				</tr>
			</table>
		<?php
		}
	}
}

/********************************
 * Pinta los anuncios destacados de la portada
 ********************************/
function dibujar_destacados_portada($listaAnuncios) {
	global $modulos;
	
	if (is_array($listaAnuncios) && count ($listaAnuncios)) {
		foreach ($listaAnuncios as $anuncio) {
		?>
			<div class="anuncioDestacadoPortada">
				<a href="?opt=indice&task=anuncio&id=<?php echo $anuncio['id']; ?>">
					<?php
						if ($anuncio['fichero']) {
						?>
							<img src="ficheros/minis/<?php echo $anuncio['fichero']; ?>">
						<?php
						} else {
						?>
							<img src="<?php echo $modulos->rutaPlantilla; ?>images/no-imagen.gif">
						<?php
						}
					?>
					<p class="tituloAnuncio"><?php echo $anuncio['titulo']; ?></p>
				</a>
			</div>
		<?php
		}
	}
}

/********************************
 * Pinta los anuncios normales
 ********************************/
function dibujar_anuncios($listaAnuncios) {
	global $modulos;
	
	if (is_array($listaAnuncios)) {
		foreach ($listaAnuncios as $anuncio) {
			$precio = '';
			if ($anuncio['precio_dia'])
				$precio .= ' - Precio: '.$anuncio['precio_dia'].' &euro;/dia';
			else if ($anuncio['precio_semana'])
				$precio .= ' - Precio: '.$anuncio['precio_semana'].' &euro;/semana';
			else if ($anuncio['precio_quincena'])
				$precio .= ' - Precio: '.$anuncio['precio_quincena'].' &euro;/quincena';
			else if ($anuncio['precio_mes'])
				$precio .= ' - Precio: '.$anuncio['precio_mes'].' &euro;/mes';

		?>
			<a href="?opt=indice&task=anuncio&id=<?php echo $anuncio['id']; ?>" style="text-decoration: none;">
				<div class="cuadroAnuncio">
					<div style="float: left;">
					<?php
						if ($anuncio['fichero']) {
						?>
							<img src="ficheros/minis/<?php echo $anuncio['fichero']; ?>">
						<?php
						} else {
						?>
							<img src="<?php echo $modulos->rutaPlantilla; ?>images/no-imagen.gif">
						<?php
						}
					?>
					</div>
					<div>
						<p>
							<span class="tituloAnuncio"><?php echo $anuncio['titulo']; ?></span>
							<span class="precioTitulo"><?php echo $precio; ?></span><br>
							Categor&iacute;a: <?php echo $anuncio['nombre_categoria']; ?><br>
							Provincia: <?php echo $anuncio['nombre_provincia']; ?><br>
							Localidad: <?php echo $anuncio['localidad']; ?>
						</p>
						<p class="textoAnuncio"><?php echo substr($anuncio['texto'], 0, 256).'...'; ?></p>
					</div>
					<p class="cierre"></p>
				</div>
			</a>
		<?php
		}
	}
}

/********************************
 * Genera el listado de la p�gina principal
 ********************************/
function pagina_principal($provincia) {
	global $modulos, $limiteAnunciosDestacados, $limiteUltimosAnuncios;
	
	$pagina = $modulos->getParam($_GET, 'pag', 0);
	if (is_numeric($pagina) && $pagina >= 0)
		$inicio = $limiteUltimosAnuncios * $pagina;
	else {
		$inicio = 0;
		$pagina = 0;
	}

	$anuncios = leer_anuncios(-1, $limiteUltimosAnuncios, $inicio, false, $provincia);
	$destacados = leer_anuncios(-1, $limiteAnunciosDestacados, 0, true, $provincia);
	$totalAnuncios = total_anuncios(false, false);

	?>
              <table width="559" border="0" cellspacing="0" cellpadding="0">
                <tr>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                </tr>
                <tr>
                  <td width="7">&nbsp;</td>
                  <td colspan="7">
					<!--img src="<?php echo $modulos->rutaPlantilla; ?>images/anuncios-destacados.gif" alt="Anuncio Destacados" width="545" height="22" /-->
					<p class="seccionAnuncio">Anuncios Destacados</p>
				</td>
                  <td width="7">&nbsp;</td>
                </tr>
                <tr>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                  <td width="131">&nbsp;</td>
                  <td width="7">&nbsp;</td>
                </tr>
				<tr>
					<td colspan="9">
						<?php
							dibujar_destacados_portada($destacados);
						?>
					</td>
				</tr>
                <tr>
                  <td colspan="9">&nbsp;</td>
                </tr>
                <tr>
                  <td width="7">&nbsp;</td>
                  <td colspan="7">
					<!--img src="<?php echo $modulos->rutaPlantilla; ?>images/ultimos-anuncios.gif" alt="Ultimos Anuncios" width="545" height="22" /-->
					<p class="seccionAnuncio">&Uacute;ltimos Anuncios</p>
				  </td>
                  <td width="7">&nbsp;</td>
                </tr>
                <tr>
					<td colspan="9">
					<?php
						dibujar_anuncios($anuncios);
					?>
					</td>
                </tr>
                <tr>
					<td colspan="9">
					<?php
						if ($totalAnuncios > $limiteUltimosAnuncios)
							echo paginador($pagina, ceil ($totalAnuncios / $limiteUltimosAnundios), '?opt=indice&pag=@');
		
					?>
					</td>
                </tr>
                <tr>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
              </table>
	<?php
}

/***************************************
 * Muestra el contenido de un anuncio
 ***************************************/
function mostrar_anuncio() {
	global $modulos;
	
	$idAnuncio = $modulos->getParam($_GET, 'id', -1);
	if (is_numeric($idAnuncio) && $idAnuncio >= 0) {
		$datosAnuncio = leer_anuncio($idAnuncio);
		if (is_array($datosAnuncio)) {
			// Primero se incrementa el nº de visitas
			$resultado = $modulos->inserta_sql('SQL_ANUNCIO_INCREMENTAR_VISITA', $idAnuncio);
			
			// Luego se leen los datos del anuncio y se muestra		
			$titulo = $datosAnuncio['titulo'];
			
			$imagenesAnuncio = leer_imagenes_anuncio($idAnuncio);
			$fechaFin = convierte_fecha_a_unix($datosAnuncio['fechafinpub']);
			$hayPrecio = strlen(str_replace(Array(' ',"\t","\n","\r","\0","\x0B"), Array('','','','','',''), $datosAnuncio['precio_dia'] . $datosAnuncio['precio_semana'] . $datosAnuncio['precio_quincena'] . $datosAnuncio['precio_mes']));
			$precios = Array();
			if ($datosAnuncio['precio_dia'] != '')
				$precios[] = Array('Precio por d&iacute;a', $datosAnuncio['precio_dia']);
			if ($datosAnuncio['precio_semana'] != '')
				$precios[] = Array('Precio por semana', $datosAnuncio['precio_semana']);
			if ($datosAnuncio['precio_quincena'] != '')
				$precios[] = Array('Precio por quincena', $datosAnuncio['precio_quincena']);
			if ($datosAnuncio['precio_mes'] != '')
				$precios[] = Array('Precio por mes', $datosAnuncio['precio_mes']);
			$telefono = $datosAnuncio['telefono'];
			$categoria = $datosAnuncio['nombre_categoria'];
			$provincia = $datosAnuncio['nombre_provincia'];
			
			if ($datosAnuncio['publicado'] == '1' && (!$fechaFin || $fechaFin >= mktime(0, 0, 0))) {
				if (isset($_SESSION['datosUsuario']['id'])) {
					$idUsuario = $_SESSION['datosUsuario']['id'];
					$nombre = $_SESSION['datosUsuario']['nombre'];
					$apellidos = $_SESSION['datosUsuario']['apellidos'];
					$email = $_SESSION['datosUsuario']['email'];
				} else {
					$idUsario = $nombre = $apellidos = $email = '';
				}
				?>
					<script language="Javascript" type="text/javascript">
						function abre_denuncia(nombreCapa) {
							var capa = document.getElementById(nombreCapa);
							capa.style.visibility = 'visible';
						}
						function cerrar_capa(nombreCapa) {
							var capa = document.getElementById(nombreCapa);
							capa.style.visibility='hidden';
						}
					</script>
					<form id="formDenuncia" method="post" action="index.php" enctype="application/x-www-form-urlencoded" class="formulario">
						<div class='denuncia' id='capaDenuncia'>
							<input type="hidden" id="opt" name="opt" value="denuncias">
							<input type="hidden" id="task" name="task" value="envioDenuncia">
							<input type="hidden" id="idAnuncio" name="idAnuncio" value="<?php echo $datosAnuncio['id']; ?>">
							<input type="hidden" id="idUsuario" name="idUsuario" value="<?php echo $idUsuario; ?>">
							<div class="botonCierre">
								<input type="button" value="X" onclick="cerrar_capa('capaDenuncia');" style="width: 36px;">
							</div>
							<h4>Env&iacute;o de denuncias</h4>
							<div class="contenido">
								<label>Nombre:</label> <input type="text" id="campoNombre" name="campoNombre" value="<?php echo $nombre; ?>"><br>
								<label>Apellidos:</label> <input type="text" id="campoApellido" name="campoApellido" value="<?php echo $apellidos; ?>"><br>
								<label>E-mail:</label> <input type="text" id="campoEmail" name="campoEmail" value="<?php echo $email; ?>"><br>
								<label>Asunto:</label> <input type="text" id="campoAsunto" name="campoAsunto" value=""><br>
								<label>Denuncia:</label> <br><textarea id='campoContenido' name="campoContenido" style="height: 110px;" wrap="soft"></textarea>
							</div>
							<p class="cierre">
								<input type="button" value="Enviar mensaje" onclick="envio_mensaje('formDenuncia');" style="width:125px; margin:0px; padding: 0px;">
								<input type="button" value="Cancelar" onclick="cerrar_capa('capaDenuncia');" style="width:125px; margin:0px; padding: 0px;">
							</p>
						</div>
					</form>
				<?php
				
				if (isset ($_SESSION['datosUsuario']['id'])) {
					?>
					<script language="Javascript" type="text/javascript">
						function muestra_capa() {
							var capa = document.getElementById('capaMensaje');
							capa.style.visibility='visible';
						}
						function envio_mensaje(nombreFormulario) {
							var formulario = document.getElementById(nombreFormulario);
							var titulo = formulario.campoAsunto;
							var mensaje = formulario.campoContenido;
							if (titulo.value != '' && mensaje.value != '') {
								formulario.submit();
							} else {
								alert ('Escriba el contenido del mensaje');
							}
						}
					</script>
					<form id="formMensaje" method="post" action="index.php" enctype="application/x-www-form-urlencoded" class="formulario">
						<div class='mensaje' id='capaMensaje'>
							<input type="hidden" id="opt" name="opt" value="mensajes">
							<input type="hidden" id="task" name="task" value="envioMensaje">
							<input type="hidden" id="idAnuncio" name="idAnuncio" value="<?php echo $datosAnuncio['id']; ?>">
							<div class="botonCierre">
								<input type="button" value="X" onclick="cerrar_capa('capaMensaje');" style="width: 36px;">
							</div>
							<h4>Env&iacute;o de mensajes</h4>
							<div class="contenido">
								<label>Asunto:</label> <input type="text" id="campoAsunto" name="campoAsunto" value=""><br>
								<label>Contenido:</label> <br><textarea id='campoContenido' name="campoContenido" style="height: 110px;" wrap="soft"></textarea>
							</div>
							<p class="cierre"><input type="button" value="Enviar mensaje" onclick="envio_mensaje('formMensaje');" style="width:125px; margin:0px; padding: 0px;"> <input type="button" value="Cancelar" onclick="cerrar_capa('capaMensaje');" style="width:125px; margin:0px; padding: 0px;"></p>
						</div>
					</form>
					<?php
				} else {
					?>
					<script language="Javascript" type="text/javascript">
						function muestra_capa() {
							alert ('Debe hacer login con su usuario para enviar un mensaje a la persona que ha puesto el anuncio.');
						}
					</script>
					<?php
				}
				?>
				<div class='anuncio'>
					<h2><?php echo $titulo ?></h2>
					<div class='publicacion'>Publicado el <?php echo $datosAnuncio['fechaalta']; ?> por <?php echo $datosAnuncio['login']; ?><br>
					Provincia: <?php echo $datosAnuncio['nombre_provincia']; ?><br>
					Localidad: <?php echo $datosAnuncio['localidad']; ?><br>
					Categor&iacute;a:
					<?php
						echo $categoria;
						if ($telefono) {
						?>
							<br>Tel&eacute;fono de contacto:
						<?php
							echo $telefono;
						}
					?>
					</div>
					<br>
					<?php // Muestra las imagenes del anuncio
						if (count($imagenesAnuncio)) { ?>
							<table align="center" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" style="text-align: center;"><?php
										for ($c = 0; $c < count($imagenesAnuncio) && $datosAnuncio['imagen_principal'] != $imagenesAnuncio[$c]['id']; $c++) ;
										if ($c < count($imagenesAnuncio)) {
										?>
											<img src="ficheros/<?php echo $imagenesAnuncio[0]['fichero']; ?>">
										<?php
										}
									?>
									</td>
								</tr>
								<?php
									// Mira si el anuncio tiene m�s de una foto
									if (count($imagenesAnuncio) > 1) {
									?>
										<tr>
											<td>M&aacute;s im&aacute;genes:
												<table align="center" border="0">
													<tr>
													<?php
														foreach ($imagenesAnuncio as $imagen) {
															if ($imagen['id'] != $datosAnuncio['imagen_principal']) {
															?>
																<td class='listaMiniImagenes' style=""><a href='ficheros/<?php echo $imagen['fichero']; ?>' rel='ibox'><img src='ficheros/minis/<?php echo $imagen['fichero']; ?>' id='imagen' name='imagen' /></a>
																</td>
															<?php
															}
														}
													?>
													</tr>
												</table>
											</td>
										</tr>
									<?php
									}
								?>
							</table>
						<?php
						}
					?>
					<br>
					<div class='texto'>
						<?php
							echo $datosAnuncio['texto']; 
						?>
					</div>
					<div style="padding-top: 10px; clear: both;">
						<?php // Mira si hay puesto precios
							if ($hayPrecio) { ?>
								<div style="text-align: center; width: 100%;">Tabla de precios</div>
								<table class='tablaPrecios' align="center">
									<tr>
										<th class='celda1'>Periodo</th>
										<th>Precio</th>
									</tr>
									<?php
										foreach($precios as $precioPeriodo) {
											?>
											<tr>
												<td class='celda1'><?php echo $precioPeriodo[0]; ?></td>
												<td><?php echo $precioPeriodo[1]; ?> &euro;</td>
											</tr>
											<?php
										}
									?>
								</table>
							<?php
							}
						?>
					</div>
					<p class="cierre">
						<input type="button" onclick="window.history.back();" value="Volver al &iacute;ndice">
						<input type="button" onclick="muestra_capa('capaMensaje');" value="Enviar un mensaje al anunciante">
						<input type="button" onclick="abre_denuncia('capaDenuncia');" value="Denunciar">
					</p>

				</div>
				<script type="text/javascript" src="js/ibox.js"></script>
				<script type="text/javascript">iBox.setPath('js/');</script>

				<?php
			}
		} else {
			?>
				<script language="javascript">
					document.location="index.php";
				</script>
			<?php
		}
	}
}

?>