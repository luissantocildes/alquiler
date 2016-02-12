<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

require_once ('funciones.php');

class menus {
	// Devuelve un array con los menus hijos de un padre determinado
	function lee_menus ($padre = 0) {
		global $modulos;
		
		$menus = Array();
		
		if (is_numeric($padre)) {
			$menus = $modulos->resultado_sql ('SQL_CATEGORIA_PADRE', $padre);
		} 
		
		return $menus;
	}
	
	/**************************
	 * Devuelve una cadena con el c�digo html del men� lateral
	 * Se verifica si existe la funci�n menu_modulo; funci�n definida por el m�dulo para presentar un men� propio.
	 * Si la funci�n existe entonces se llama a esta en lugar de generar al men� est�ndar
	 **************************/	
	function menu_lateral () {
		
		if (function_exists('menu_modulo'))
			menu_modulo();
		else {
			menus::menu_standar();
		}
	}
	
	function genera_menu_lateral ($padre = 0) {
		global $modulos;
		
		$resultado = Array();
		$menus = menus::lee_menus($padre);
		if (!empty($menus)) {
			foreach ($menus as $aux) {
				$entrada = $aux;
				$resultado[] = $entrada;
				$aux2 = menus::genera_menu_lateral ($entrada['id']);
				if (!empty($aux2)) {
					$resultado = array_merge($resultado, $aux2);
				}
				//	$entrada['hijos'] = $aux2;
			}
		}
		return $resultado;
	}
	
	function menu_standar() {
		global $modulos, $nombreSesion;
	
		$listaProvincias = lista_provincias();
		$idProvincia = $_SESSION['provincia'];

		?>
			<p class="seccion" style="font-size: 15px; padding-left: 10px; width: 198px;"><b>Seleccione la provincia:</b></p>
			<form name="formProvincia" id="formProvincia" method="post" action="index.php">
				<input type="hidden" name="opt" value="indice">
				<p>
					<select name="provincia" onchange="formProvincia.submit();">
						<option value="-1">Todas las provincias</option>
						<?php
							foreach($listaProvincias as $provincia) {
								?>
								<option value="<?php echo $provincia['id']; ?>" <?php if ($provincia['id'] == $idProvincia) echo 'selected'; ?>>
									<?php echo $provincia['nombre']; ?>
								</option>
								<?php
							}
						?>
					</select>
				</p>
			</form>
			<p class="seccion">Categor&iacute;as</p>
		<?php
			$menus = menus::genera_menu_lateral();
			menus::dibujar_menu_categorias($menus);
		?>
			<p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p><p>&nbsp;</p>
		<?php
	}

	function dibujar_menu_categorias ($menus) {
		global $modulos;
		
		$nuevoMenu = true;
		if (is_array($menus) && count($menus)) {
			for ($c = 0; $c < count($menus); $c++) {
				$entrada = $menus[$c];
				$nombreHijo = 'capa_'.$entrada['id'];
				if ($nuevoMenu) {
						?>
							<div class="contenedorEntradaMenu" onmouseover="cambiar('<?php echo $nombreHijo; ?>');" onmouseout="cambiar('<?php echo $nombreHijo; ?>');">
						<?php
					$nuevoMenu = false;
				}
				if ($entrada['padre'] == 0) {
					?>
					<div class="entrada_menu">
					<?php 
						if ($entrada['imagen']) {
							$imagen = 'ficheros/categorias/'.$entrada['imagen'];
							$anchoImagen = 'width="20"';
							$margen = '0px 0px 0px -7px';
						} else {
							$imagen = $modulos->rutaPlantilla . '/images/cat.gif';
							$anchoImagen = '';
							$margen = '0px 4px 0px 0px';
						}
					?>
						<div style="clear: both; width: 100%;">
							<img src="<?php echo $modulos->rutaPlantilla; ?>images/menu_bi.png" style="float:left; height:29px;">
							<div class="fuera">
								<div class="centro">
									<div class="dentro">
										<img src="<?php echo $imagen; ?>" style="margin: <?php echo $margen; ?>; vertical-align:middle;"<?php echo $anchoImagen; ?>>
										<a href="?opt=indice&task=categoria&id=<?php echo $entrada['id']; ?>">
											<?php echo $entrada['nombre']; ?>
										</a>
									</div>
								</div>
							</div>
							<img class="imagenDerechaBoton" src="<?php echo $modulos->rutaPlantilla; ?>images/menu_bd.png">
						</div>
					</div>
					<?php
					if ($c < count($menus)-1 && $menus[$c+1]['padre'] == 0) {
						$nuevoMenu = true;
						?>
							</div>
						<?php
					}
				} else {
					$nombre = 'capa_'.$entrada['padre'];
					$d = $c;
					while ($d < count($menus) && $menus[$d]['padre'] != 0 && $menus[$d]['id'] != $modulos->categoria)
						$d++;
					//if ($menus[$c]['padre'] == $modulos->categoria || $menus[$c]['id'] == $modulos->categoria) {
					if (($d < count($menus) && $menus[$d]['padre'] != 0) || $menus[$c]['padre'] == $modulos->categoria) {
						?>
							<div class="entrada_submenu" style="overflow: hidden;">
						<?php
					} else {
						?>
							<div class="entrada_submenu" id="<?php echo $nombre; ?>" style="overflow: hidden; height: 0px;">
						<?php
					}
								$entrada = $menus[$c];
								while ($c < count($menus) && $entrada['padre'] != 0) {
									?>
									<div style="width: 190px;"><img src="<?php echo $modulos->rutaPlantilla; ?>/images/sub.gif" align="left" style="margin: 3px;">
										<a href="?opt=indice&task=categoria&id=<?php echo $entrada['id']; ?>">
											<?php echo $entrada['nombre']; ?>
										</a>
									</div>
									<?php
									$c++;
									$entrada = $menus[$c];
								}
								$c--;
								$nuevoMenu = true;
							?>
						</div>
					</div>
					<?php
				}
			}
		}
	}

	function dibujar_menu_categorias_($menus, $nombre = '', $padre = 0) {
		global $modulos, $dirImagenes;

		if (!empty($menus)) {
			if ($nombre == '' || $modulos->categoria == $padre || recursive_array_search($modulos->categoria, $menus) !== false) {
				?>
					<div class="menu_lateral" id="<?php echo $nombre; ?>" style="overflow: hidden;">
				<?php
			} else {
				?>
					<div class="menu_lateral" id="<?php echo $nombre; ?>" style="overflow: hidden; height: 0px;">
				<?php
			}
			foreach ($menus as $entrada) {
				$nombreHijo = $nombre.'_'.$entrada['id'];
				$hijos = isset($entrada['hijos']) ? $entrada['hijos'] : Array();

				if ($entrada['id'] == $modulos->categoria || recursive_array_search($modulos->categoria, $hijos) !== false) {
					?>
						<div>
					<?php
				} else {
					if ($entrada['padre'] != 0) {
					?>
						<div>
					<?php
					} else {
					?>
						<div onmouseover="cambiar('<?php echo $nombreHijo; ?>');" onmouseout="cambiar('<?php echo $nombreHijo; ?>');">
					<?php
					}
				}
					if ($entrada['imagen'])
						$imagen = 'ficheros/categorias/'.$entrada['imagen'];
					else $imagen = $modulos->rutaPlantilla . ($entrada['padre'] ? '/images/sub.gif':'/images/cat.gif');
				?>
					<div style="clear: both; width: 100%;"><img src="<?php echo $imagen; ?>" align="left" style="margin: 2px;">&nbsp;
						<a href="?opt=indice&task=categoria&id=<?php echo $entrada['id']; ?>">
							<?php echo $entrada['nombre']; ?>
						</a>
					</div>
					<?php
						if ($hijos) {
							menus::dibujar_menu_categorias($hijos, $nombreHijo, $entrada['id']);
						}
						if ($entrada['padre'] == 0)
							echo '<div style="height:5px;"></div>';
					?>
					</div>
				<?php
			}
			?>
				</div>
			<?php
		}
		
	}

}

?>