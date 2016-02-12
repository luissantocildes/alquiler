/********************************
 * Verifica que el formulario de anuncios gratuitos sea correcto
 ********************************/
function verificar_banner(nombreFormulario, modificado) {
	var form = document.getElementById(nombreFormulario);
	
	if (form != undefined) {
		if (form.titulo.value == '') {
			alert ('El banner debe de tener un título');
			form.titulo.focus();
		} else if (form.tipo.selectedIndex && form.categoria.selectedIndex == 0) {
			alert ('Escoja una categoría, por favor.');
			form.categoria.focus();
		} else if (!modificado || form.url.value == '') {
			alert ('Escriba una url para el banner');
			form.url.focus();
		} else if (form.imagen1.value == '' && form.task.value == 'grabar') {
			alert ('Escoja una imagen para el banner');
			form.imagen1.focus();
		} else {
			form.submit();
		}
	} else {
		alert ("Error, el formulario de banners no existe");
	}
}

/*************************
 * Cambia la publicacion de los anuncios
 *************************/
function cambiar_estado_anuncios() {
	var formulario = document.getElementById('anuncios');
	
	formulario.task.value = 'cambioPublicacion';
	formulario.submit();
}

/**************************
 * Envia el formulario de borrar anuncio
 **************************/
function borrar_anuncio(nombreFormulario) {
	var formulario = document.getElementById(nombreFormulario);
	
	formulario.submit();
}

/*****************************
 * Cambia el estado de un campo
 *****************************/
function habilitar_campo(nombreCampo, estado) {
	var campo = document.getElementById(nombreCampo);
	
	if (estado)
		campo.disabled = true;
	else campo.disabled = false;
}

/*************************
 * Cambia la publicacion de los anuncios
 *************************/
function cambiar_estado_banner() {
	var formulario = document.getElementById('anuncios');
	
	formulario.task.value = 'cambioPublicacion';
	formulario.submit();
}
