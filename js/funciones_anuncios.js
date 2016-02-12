/********************************
 * Verifica que el formulario de anuncios gratuitos sea correcto
 ********************************/
function verificar_anuncio_gratuito(nombreFormulario, modificado, nombreCapa) {
	var form = document.getElementById(nombreFormulario);
	
	if (form != undefined) {
		if (form.titulo.value == '') {
			alert ('El anuncio debe de tener un título');
			form.titulo.focus();
		} else if (form.provincia.selectedIndex == 0) {
			alert ('Escoja una provincia, por favor.');
			form.provincia.focus();
		} else if (form.categoria.selectedIndex == 0) {
			alert ('Escoja una categoría, por favor.');
			form.categoria.focus();
		} else if (form.localidad.value == '') {
			alert ('Escriba una población');
			form.localidad.focus();
		} else if (!modificado && form.texto.value == '') {
			alert ('Escriba un contenido para el anuncio');
			form.texto.focus();
		} else if (form.condiciones.checked == false) {
			alert ('Debe aceptar las condiciones de uso');
			form.condiciones.focus();
		} else {
			var capa = document.getElementById(nombreCapa);
			capa.style.visibility = 'visible';
			form.submit();
		}
	} else {
		alert ("Error, el formulario de anuncios no existe");
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