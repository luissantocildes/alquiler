<?php

define ('DEBUG', 1);

$dbname = 'alquileres';
$dbuser = 'alquilo';
$dbpasswd = '12345678';
$dbhost = 'localhost';
$dbtype = 'mysqli';

$dsn = "$dbtype://$dbuser:$dbpasswd@$dbhost/$dbname";

$titulo = '::: NombreEmpresa :::';

$tablePrefix = 'alq';

//$dirApp = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR;
$aux = explode(DIRECTORY_SEPARATOR, __FILE__);
array_pop($aux);
$dirApp=implode(DIRECTORY_SEPARATOR, $aux).DIRECTORY_SEPARATOR;
// Definición de los directorios de la aplicación
if ($_SERVER['HTTP_HOST'] == 'localhost') {
	$url = $_SERVER['HTTP_HOST']."/NombreEmpresa/";
//	$dirApp .= 'NombreEmpresa2'.DIRECTORY_SEPARATOR;
} else {
	$url = $_SERVER['HTTP_HOST'].'/';
//	$dirApp .= DIRECTORY_SEPARATOR;
}
$dirUpload = $dirApp.'docs'.DIRECTORY_SEPARATOR;

// Directorio de los módulos
$dirModulos = 'modulos';

// Directorios de las imágenes
$dirImagenes = $dirApp.'ficheros'.DIRECTORY_SEPARATOR;
$dirMinis = $dirImagenes.'minis'.DIRECTORY_SEPARATOR;

// Timeout por defecto, 1200 segundos (media hora)
$defaultTimeout = 1200;

// Cantidad de ofertas por l�nea en la p�gina de ofertas
$ofertasPorLinea = 3;
$ofertasPorPagina = $ofertasPorLinea * 4;

// Elementos por p�gina en los listados de la administraci�n
$elemPorLinea = 4;
$lineasPorPagina = 3;
$elemPorPagina = $elemPorLinea * $lineasPorPagina;

//$lineasListado = 20;
$lineasListado = 20;

// Total de anuncioa a mostrar en el lateral
$anunciosLaterales = 3;

// Cantidad mínima de días necesaria para avisar que un anuncio está por caducar
$diasParaCaducar = 5;
// Anuncios y mensajes a mostrar en la página principal del panel
$limiteAnunciosPanel = 3;

// Número de anuncios en la página principal
$limiteAnunciosDestacados = 8;
$limiteUltimosAnuncios = 8;
$limiteDestacadosCategoria = 3;
$limiteAnunciosCategoria = 10;
$limiteBannersLaterales = 6;
$limiteFotosAnuncio = 5;

// Tamaño máximo de la imagen a mostrar en los anuncios
$imagenMaxX = 542;
$imagenMaxY = 405;

// Determina el idioma (desde la sesión)
if (isset ($_SESSION['lang']))
	$lang = $_SESSION['lang'];
else $lang = 'es';

// Plantilla a cargar
$plantilla = 'NombreEmpresa';

// Nombre de la sesion
$nombreSesion = 'datosUsuario';

// Email Administrador
$emailAdmin = 'info@NombreEmpresa.com';

ini_set ('include_path', $dirApp.'include'.DIRECTORY_SEPARATOR.PATH_SEPARATOR.$dirApp.'pear'.DIRECTORY_SEPARATOR.PATH_SEPARATOR.ini_get('include_path'));
setlocale(LC_ALL, 'es_ES');
error_reporting(0);
?>
