/*************************
 * Verifica que el formulario de contraseñas sea correcto
 *************************/
function verifica_passwd(nombreFormulario) {
	var form = document.getElementById(nombreFormulario);
	
	if (form != undefined) {
		if (form.f_passwd_old.value == '') {
			alert ('Por favor, escriba la contraseña actual');
			form.f_passwd_old.focus();
		} else if (form.f_passwd1.value == '') {
			alert ('Por favor, escriba la nueva contraseña');
			form.f_passwd1.focus();
		} else if (form.f_passwd2.value == '') {
			alert ('Por favor, escriba la verificación de la contraseña nueva');
			form.f_passwd2.focus();
		} else if (form.f_passwd_old.value == form.f_passwd1.value) {
			alert ('La contraseña nueva debe de ser diferente de la antigua');
			form.f_passwd1.focus();
		} else if (form.f_passwd1.value != form.f_passwd2.value) {
			alert ('La contraseñueva y la verificación deben de ser iguales');
			form.f_passwd1.focus();
		} else {
			form.task.value = 'cambioPassword';
			form.submit();
		}
	} else
		alert ('El formulario no existe');
}

/*****************************
 * Verifica que los datos personales sean correctos. Si están bien los envía
 *****************************/
function verifica_datos_personales(nombreFormulario) {
	var form = document.getElementById(nombreFormulario);
	var emailReg = new RegExp('^[0-9a-zA-Z]+@[0-9a-zA-Z]+[\.]{1}[0-9a-zA-Z]+[\.]?[0-9a-zA-Z]+$');
	
	if (form != undefined) {
		if (form.f_nombre.value == '') {
			alert ('Escriba su nombre, por favor.');
			form.f_nombre.focus();
		} else if (form.f_apellidos.value == '') {
			alert ('Escriba sus apellidos, por favor.');
			form.f_apellidos.focus();
		} else if (!form.f_email.value.match(emailReg)) {
			alert ('Escriba una dirección de email válida, por favor.');
			form.f_email.focus();
		} else {
			form.task.value = 'cambioDatosPersonales';
			form.submit();
		}
	} else
		alert ('El formulario no existe');
}