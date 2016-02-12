<?php

// Protección contra ejecución incorrecta
defined('PATH_BASE') or die();

// tipos de anuncios
define ('C_GRATUITO', 0);
define ('C_DESTACADO', 1);
define ('C_BANNER', 2);

// Errores de anuncios
define ('E_TIPO_ANUNCIO_INCORRECTO', 0x0001);
?>