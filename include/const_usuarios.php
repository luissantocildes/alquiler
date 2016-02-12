<?php

// Protecci�n contra ejecuci�n incorrecta
defined('PATH_BASE') or die();

// tipos de usuario
define ('C_ADMIN', 1);
define ('C_EDITOR', 2);
define ('C_USUARIO', 4);

// Errores de usuario
define ('E_OLD_PASSWD', 0x0001);
define ('E_NO_PASSWD', 0x0002);
define ('E_PASSWD_DIFF', 0x0003);
define ('E_PASSWD_OLD_NEW_DIFF', 0x0004);
define ('E_NO_NAME', 0x0010);
define ('E_NO_LASTNAME', 0x0100);
define ('E_NEW_NAME', 0x0020);
define ('E_NO_EMAIL', 0x0040);
define ('E_NO_LOGIN', 0x0080);

// Estados de los usuarios
define ('C_CORRECTO', 0);
define ('C_BLOQUEADO', 1);
define ('C_PENDIENTE', 2);
define ('C_PRIMERA_VEZ', 3);
?>