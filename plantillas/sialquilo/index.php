<?php
	require_once ('menus.php');
	require_once ('banners.php');

	global $titulo, $modulos, $url;

	$aux = $modulos->getParam($_GET, 'task', '');
	if ($aux == 'categoria') {
		$modulos->categoria = $modulos->getParam($_GET, 'id', -1);
		if (!is_numeric($modulos->categoria))
			$modulos->categoria = -1;
	}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link href='<?php echo $modulos->rutaPlantilla; ?>/sialquilo.css' rel='stylesheet' type='text/css'>
<title><?php echo $titulo; ?></title>
<script language="javascript" src="http://<?php echo $url; ?>js/funciones.js"></script>
</head>

<body onload="MM_preloadImages('<?php echo $modulos->rutaPlantilla; ?>images/menu/Inicio_f4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis_f6.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/Inicio.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis_f4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4_f4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f6.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6_f4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos_f6.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos_f4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c8_f4.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c8.jpg','<?php echo $modulos->rutaPlantilla; ?>images/menu/Buscar_f2.jpg');">
	<table width="980" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
		<tr>
			<td width="10">&nbsp;</td>
			<td width="260"><a href="http://<?php echo $url; ?>"><img border="0" src="<?php echo $modulos->rutaPlantilla; ?>images/sialquilo.jpg" alt="Portal de alquiler" /></a></td>
			<td width="10">&nbsp;</td>
			<td width="700" align="center" valign="middle">
				<table width="700" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td width="460">
							<?php
								$imagenBanner = banner_superior_azar();
							?>
							<a href="http://<?php echo urldecode($imagenBanner[1]); ?>" target="nueva"><img src="ficheros/<?php echo $imagenBanner[0]; ?>" alt="" name="Banner" width="460" height="80" id="Banner" style="background-color: #0000FF" />
							</a>
						</td>
						<td width="3">&nbsp;</td>
						<td width="228"><?php echo Clogin::cuadro_login(); ?></td>
					</tr>
				</table>
			</td>
			<td width="10">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="5" align="center">
				<hr />
				<span class="subtituloPagina">Anuncios de alquiler. Alquila lo que no uses, puedes sacarle mucho partido.</span>
				<hr />
			</td>
		</tr>
		<tr>
			<td width="10">&nbsp;</td>
			<td width="260" valign="top">
				<table border="0" cellpadding="0" cellspacing="0" width="260">
					<tr>
						<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/spacer.gif" alt="" name="undefined_2" width="1" height="1" border="0" id="undefined_2" /></td>
						<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/spacer.gif" alt="" name="undefined_2" width="248" height="1" border="0" id="undefined_2" /></td>
						<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/spacer.gif" alt="" name="undefined_2" width="11" height="1" border="0" id="undefined_2" /></td>
						<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/spacer.gif" alt="" name="undefined_2" width="1" height="1" border="0" id="undefined_2" /></td>
					</tr>
					<tr>
						<td><img name="SI" src="<?php echo $modulos->rutaPlantilla; ?>images/cat-SI.jpg" width="1" height="8" border="0" id="SI" alt="" /></td>
						<td><img name="SC" src="<?php echo $modulos->rutaPlantilla; ?>images/cat-SC.jpg" width="248" height="8" border="0" id="SC" alt="" /></td>
						<td><img name="SD" src="<?php echo $modulos->rutaPlantilla; ?>images/cat-SD.jpg" width="11" height="8" border="0" id="SD" alt="" /></td>
					</tr>
					<tr>
						<td bgcolor="#54B236"></td>
						<td valign="top" bgcolor="#BDDD7C" class='menu_lateral'>
							<div class="contenedorMenu"><?php menus::menu_lateral(); ?></div>
						</td>
						<td background="<?php echo $modulos->rutaPlantilla; ?>images/cat-CD.jpg"></td>
					</tr>
					<tr>
						<td><img name="BI" src="<?php echo $modulos->rutaPlantilla; ?>images/cat-BI.jpg" width="1" height="8" border="0" id="BI" alt="" /></td>
						<td><img name="BC" src="<?php echo $modulos->rutaPlantilla; ?>images/cat-BC.jpg" width="248" height="8" border="0" id="BC" alt="" /></td>
						<td><img name="BD" src="<?php echo $modulos->rutaPlantilla; ?>images/cat-BD.jpg" width="11" height="8" border="0" id="BD" alt="" /></td>
					</tr>
				</table>
			</td>
			<td width="10">&nbsp;</td>
			<td width="100%" valign="top">
				<table width="700" border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td colspan="3">
							<form name="formBusqueda" id="formBusqueda" method="get" action="">
								<input type="hidden" name="opt" value="indice">
								<input type="hidden" name="task" value="buscar">
								<table class="menuSuperior" border="0" cellpadding="0" cellspacing="0" width="696">
							  <!-- fwtable fwsrc="menu.png" fwpage="Página 1" fwbase="menu.jpg" fwstyle="Dreamweaver" fwdocid = "181478041" fwnested="0" -->
									<tr>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="3" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="49" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="10" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="130" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="11" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="78" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="11" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="165" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="160" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="67" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="12" height="1" border="0" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="1" height="1" border="0" alt="" /></td>
									</tr>
									<tr>
										<td colspan="11"><img name="menu_r1_c1" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r1_c1.jpg" width="696" height="5" border="0" id="menu_r1_c1" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="1" height="5" border="0" alt="" /></td>
									</tr>
									<tr>
										<td rowspan="2"><img name="menu_r2_c1" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c1.jpg" width="3" height="31" border="0" id="menu_r2_c1" alt="" /></td>
										<td><img name="Inicio" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/Inicio.jpg" width="49" height="26" border="0" id="Inicio" usemap="#m_Inicio" alt="Inicio" /></td>
										<td><img name="anunciogratis" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis.jpg" width="10" height="26" border="0" id="anunciogratis" usemap="#m_anunciogratis" alt="publicar anuncio" /></td>
										<td><img name="menu_r2_c4" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4.jpg" width="130" height="26" border="0" id="menu_r2_c4" usemap="#m_menu_r2_c4" alt="publicar anuncio" /></td>
										<td><img name="comoalquilar" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg" width="11" height="26" border="0" id="comoalquilar" usemap="#m_comoalquilar" alt="Como alquilar" /></td>
										<td><img name="menu_r2_c6" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6.jpg" width="78" height="26" border="0" id="menu_r2_c6" usemap="#m_menu_r2_c6" alt="Como alquilar" /></td>
										<td><img name="contratos" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos.jpg" width="11" height="26" border="0" id="contratos" usemap="#m_contratos" alt="Descargar contratos modelos" /></td>
										<td><img name="menu_r2_c8" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c8.jpg" width="165" height="26" border="0" id="menu_r2_c8" usemap="#m_menu_r2_c8" alt="Descargar contratos modelos" /></td>
										<td rowspan="2">
											<input type="text" id="busquedaPalabras" name="busquedaPalabras" value="" style="margin-left: 25px; margin-bottom: 5px; width:130px; height:20px;" onkeypress="saltar2(event, 'busquedaPalabras');" />
										</td>
										<td><a <a href="#" onclick="busqueda_palabras('busquedaPalabras');" onmouseout="MM_swapImgRestore();" onmouseover="MM_swapImage('Buscar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/Buscar_f2.jpg',1);"><img name="Buscar" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/Buscar.jpg" width="67" height="26" border="0" id="Buscar" alt="Buscar" /></a></td>
										<td rowspan="2"><img name="menu_r2_c11" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c11.jpg" width="12" height="31" border="0" id="menu_r2_c11" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="1" height="26" border="0" alt="" /></td>
									</tr>
									<tr>
										<td colspan="7"><img name="menu_r3_c2" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r3_c2.jpg" width="454" height="5" border="0" id="menu_r3_c2" alt="" /></td>
										<td><img name="menu_r3_c10" src="<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r3_c10.jpg" width="67" height="5" border="0" id="menu_r3_c10" alt="" /></td>
										<td><img src="<?php echo $modulos->rutaPlantilla; ?>images/menu/spacer.gif" width="1" height="5" border="0" alt="" /></td>
									</tr>
								</table>
								<map name="m_Inicio" id="m_Inicio">
									<area shape="poly" coords="0,26,10,0,59,0,49,26,0,26" href="http://www.sialquilo.com" title="Inicio" alt="Inicio" onmouseout="MM_swapImage('Inicio','','<?php echo $modulos->rutaPlantilla; ?>images/menu/Inicio.jpg','anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis.jpg',1);"  onmouseover="MM_swapImage('Inicio','','<?php echo $modulos->rutaPlantilla; ?>images/menu/Inicio_f4.jpg','anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis_f6.jpg',1);"  />
								</map>
								<map name="m_anunciogratis" id="m_anunciogratis">
									<area shape="poly" coords="10,0,151,0,140,26,0,26,10,0" href="http://www.sialquilo.com/?opt=anuncios&task=nuevo" title="publicar anuncio" alt="publicar anuncio" onmouseout="MM_swapImage('anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis.jpg','menu_r2_c4','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4.jpg','comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg',1);"  onmouseover="MM_swapImage('anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis_f4.jpg','menu_r2_c4','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4_f4.jpg','comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f6.jpg',1);"  />
									<area shape="poly" coords="-49,26,-39,0,10,0,0,26,-49,26" href="www.sialquilo.com" title="Inicio" alt="Inicio" onmouseout="MM_swapImage('Inicio','','<?php echo $modulos->rutaPlantilla; ?>images/menu/Inicio.jpg','anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis.jpg',1);"  onmouseover="MM_swapImage('Inicio','','<?php echo $modulos->rutaPlantilla; ?>images/menu/Inicio_f4.jpg','anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis_f6.jpg',1);"  />
								</map>
								<map name="m_menu_r2_c4" id="m_menu_r2_c4">
									<area shape="poly" coords="0,0,141,0,130,26,-10,26,0,0" href="http://www.sialquilo.com/?opt=anuncios&task=nuevo" title="publicar anuncio" alt="publicar anuncio" onmouseout="MM_swapImage('anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis.jpg','menu_r2_c4','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4.jpg','comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg',1);"  onmouseover="MM_swapImage('anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis_f4.jpg','menu_r2_c4','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4_f4.jpg','comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f6.jpg',1);"  />
								</map>
								<map name="m_comoalquilar" id="m_comoalquilar">
									<area shape="poly" coords="0,26,11,0,100,0,89,26,0,26" href="http://sialquilo.com/comoalquilar.php" title="Como alquilar" alt="Como alquilar" onmouseout="MM_swapImage('comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg','menu_r2_c6','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6.jpg','contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos.jpg',1);"  onmouseover="MM_swapImage('comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f4.jpg','menu_r2_c6','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6_f4.jpg','contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos_f6.jpg',1);"  />
									<area shape="poly" coords="-130,0,11,0,0,26,-140,26,-130,0" href="http://www.sialquilo.com/?opt=anuncios&task=nuevo" title="publicar anuncio" alt="publicar anuncio" onmouseout="MM_swapImage('anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis.jpg','menu_r2_c4','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4.jpg','comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg',1);"  onmouseover="MM_swapImage('anunciogratis','','<?php echo $modulos->rutaPlantilla; ?>images/menu/anunciogratis_f4.jpg','menu_r2_c4','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c4_f4.jpg','comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f6.jpg',1);"  />
								</map>
								<map name="m_menu_r2_c6" id="m_menu_r2_c6">
									<area shape="poly" coords="-11,26,0,0,89,0,78,26,-11,26" href="http://sialquilo.com/comoalquilar.php" title="Como alquilar" alt="Como alquilar" onmouseout="MM_swapImage('comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg','menu_r2_c6','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6.jpg','contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos.jpg',1);"  onmouseover="MM_swapImage('comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f4.jpg','menu_r2_c6','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6_f4.jpg','contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos_f6.jpg',1);"  />
								</map>
								<map name="m_contratos" id="m_contratos">
									<area shape="poly" coords="0,26,11,0,176,0,166,26,0,26" href="http://sialquilo.com/plantillas/sialquilo/images/menu/menu_r1_c6.jpg" title="Descargar contratos modelos" alt="Descargar contratos modelos" onmouseout="MM_swapImage('contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos.jpg','menu_r2_c8','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c8.jpg',1);"  onmouseover="MM_swapImage('contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos_f4.jpg','menu_r2_c8','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c8_f4.jpg',1);"  />
									<area shape="poly" coords="-89,26,-78,0,11,0,0,26,-89,26" href="http://sialquilo.com/comoalquilar.php" title="Como alquilar" alt="Como alquilar" onmouseout="MM_swapImage('comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar.jpg','menu_r2_c6','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6.jpg','contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos.jpg',1);"  onmouseover="MM_swapImage('comoalquilar','','<?php echo $modulos->rutaPlantilla; ?>images/menu/comoalquilar_f4.jpg','menu_r2_c6','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c6_f4.jpg','contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos_f6.jpg',1);"  />
								</map>
								<map name="m_menu_r2_c8" id="m_menu_r2_c8">
									<area shape="poly" coords="-11,26,0,0,165,0,155,26,-11,26" href="http://sialquilo.com/plantillas/sialquilo/images/menu/menu_r1_c6.jpg" title="Descargar contratos modelos" alt="Descargar contratos modelos" onmouseout="MM_swapImage('contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos.jpg','menu_r2_c8','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c8.jpg',1);"  onmouseover="MM_swapImage('contratos','','<?php echo $modulos->rutaPlantilla; ?>images/menu/contratos_f4.jpg','menu_r2_c8','','<?php echo $modulos->rutaPlantilla; ?>images/menu/menu_r2_c8_f4.jpg',1);"  />
								</map>
							</form>
							<br />
						</td>
					</tr>
					<tr>
						<td width="570" valign="top">
							<table width="570" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF" style="border-collapse:collapse;">
								<tr>
									<td><img name="anunciosSI" src="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-SI.jpg" width="1" height="8" border="0" id="anunciosSI" alt="" /></td>
									<td background="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-SC.jpg"></td>
									<td><img name="anunciosSD" src="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-SD.jpg" width="9" height="8" border="0" id="anunciosSD" alt="" /></td>
								</tr>
								<tr>
									<td background="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-CI.jpg"></td>
									<td width="559" valign="top">
										<div style="width: 556px; border-width: 0px; position:relative; left: 3px;">
											<?php
												// Llama a la función principal del módulo
												$modulos->principal();
											?>
										</div>
										<p>&nbsp;</p>
									</td>
									<td background="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-CD.jpg"></td>
								</tr>
								<tr>
									<td><img name="anunciosBI" src="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-BI.jpg" width="1" height="9" border="0" id="anunciosBI" alt="" /></td>
									<td><img name="anunciosBC" src="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-BC.jpg" width="559" height="9" border="0" id="anunciosBC" alt="" /></td>
									<td><img name="anunciosBD" src="<?php echo $modulos->rutaPlantilla; ?>images/anuncios/anuncios-BD.jpg" width="9" height="9" border="0" id="anunciosBD" alt="" /></td>
								</tr>
							</table>
						</td>
						<td width="10">&nbsp;</td>
						<td width="120" valign="top">
							<?php
								$imagenBanner = banner_laterales($modulos->categoria);
							?>
							<table width="120" border="0" cellspacing="0" cellpadding="0">
								<?php
									if (is_array($imagenBanner)) {
										foreach ($imagenBanner as $banner) {
											?>
												<tr>
													<td style="padding-bottom: 4px;">
													<a href="http://<?php echo urldecode($banner['enlace']); ?>" target="_new">
													<img src="ficheros/<?php echo $banner[0]['fichero']; ?>" width="120" height="120" />
													</a>
													</td>
												</tr>
											<?php
										}
									}
								?>
							</table>
						</td>
					</tr>
				</table>
			</td>
			<td width="10">&nbsp;</td>
		</tr>
		<tr style="">
			<td style="padding-bottom: 5px; padding-top: 5px; font-weight: bold; font-size: 12pt; border-top: solid 1px gray;border-bottom: solid 1px gray;" colspan="2">&copy; 2009 SiAlquilo.com</td>
			<td colspan="3" style="padding-bottom: 5px; padding-top: 5px; font-weight: bold; font-size: 11pt;text-align: right; border-top: solid 1px gray;border-bottom: solid 1px gray;">Quienes Somos - Contacto - Pol&iacute;tica de privacidad</td>
		</tr>
	</table>
</body>
</html>
