
function valor_tecla_pulsada(e)
{	var keynum
	var keychar
	var numcheck

	if(window.event) // IE
		{
		keynum = e.keyCode
		}
	else if(e.which) // Netscape/Firefox/Opera
		{
		keynum = e.which
		}
	keychar = String.fromCharCode(keynum)
	numcheck = /\d/
	//return !numcheck.test(keychar) 
	return keynum
}

function busqueda_palabras(nombreCampoBusqueda) {
	var campo = document.getElementById('formBusqueda');
	
	if (campo != '') {
		campo.submit();
	}
}

function saltar2(e, nombreCampo) {
	if (valor_tecla_pulsada(e) == 13) {
		busqueda_palabras(nombreCampo);
	}
}

function cambiar(nombreCampo) {
	var campo = document.getElementById(nombreCampo);

	if (campo != undefined) {
		if (campo.style.height == '0px') {
			campo.style.height = '100%';
		} else {
			campo.style.height = '0px';
		}
	}
}

function volver_anterior(dir) {
	document.location=dir;
}

function cambio(e) {
	if (valor_tecla_pulsada(e) == 13) {
		var texto = document.getElementById('cuadroPass');
		texto.focus();
	}
}

function saltar(e) {
	if (valor_tecla_pulsada(e) == 13) {
		var formulario = document.getElementById('loginForm');
		formulario.submit();
	}
}

/************** FUNCIONES PARA EL MENU ****************/
function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}
function MM_swapImage() { //v3.0
  var i,j=0,x,a=MM_swapImage.arguments; document.MM_sr=new Array; for(i=0;i<(a.length-2);i+=3)
   if ((x=MM_findObj(a[i]))!=null){document.MM_sr[j++]=x; if(!x.oSrc) x.oSrc=x.src; x.src=a[i+2];}
}
function MM_swapImgRestore() { //v3.0
  var i,x,a=document.MM_sr; for(i=0;a&&i<a.length&&(x=a[i])&&x.oSrc;i++) x.src=x.oSrc;
}

function MM_preloadImages() { //v3.0
	var d=document;
	if(d.images) {
		if(!d.MM_p)
			d.MM_p=new Array();
		var i,j=d.MM_p.length, a=MM_preloadImages.arguments;
		for(i=0; i<a.length; i++)
			if (a[i].indexOf("#")!=0){
				d.MM_p[j]=new Image;
				d.MM_p[j++].src=a[i];
			}
	}
}