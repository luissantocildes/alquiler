<?php

// Protección contra ejecución incorrecta
defined('PATH_BASE') or die();

require ('bbdd.php');
require ('login.php');

define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
define( "_MOS_ALLOWRAW", 0x0004 );

define('OK', 0);

class Modulo {
	var $opt;
	var $opciones = Array ('indice'=>'indice.php',
							'login'=>'login.php',
							'anuncios'=>'anuncios.php',
							'usuarios'=>'usuarios.php',
							'panel'=>'panel.php',
							'mensajes'=>'mensajes.php',
							'categorias'=>'categorias.php',
							'banners'=>'banners.php',
							'denuncias'=>'denuncias.php');

	var $inicio = 'indice';
	var $db;
	var $recargar = '';
	
	var $dirPath = '';

	var $parametros;	// Vble. auxiliar para almacenar los parámetros pasados

	var $nombrePlantilla = '';
	var $rutaPlantilla = '';

	var $cambios = Array();
	
	var $errorSql = '';
	
	var $categoria = -1;
	var $provincia = -1;

	/******************
	 * Constructor
	 * Coge el par�metro opt, verifica la sesion, e inicializa la conexi�n con la base de datos
	 ******************/
	function Modulo ($dirPath = '') {
		global $dirModulos, $lang;

		// Conecta con la base de datos
		if ($this->db = conexion_db()) {
			$this->db->loadModule('Extended'); 
			// Determina el módulo a cargar
			if (isset ($_GET['opt']))
				$this->opt = $this->getParam($_GET, 'opt', 'indice');
			else $this->opt = $this->getParam($_POST, 'opt', 'indice');
			// Inicializamos la sesion
			$this->inicializa_sesion();

			$this->dirPath = $dirPath;
			
			// Procesa par�metros para el m�dulo
			$task = Modulo::getParam($_POST, 'task', '');
			if ($task == '')
				$task = Modulo::getParam($_GET, 'task', $this->inicio);
				
			// incluye el módulo, que se inicializa
			if (isset($this->opciones[$this->opt])) {
				include ($this->dirPath . '/' . $dirModulos . '/' . $this->opciones[$this->opt]);
			} else {
				include ($this->dirPath . '/' . $dirModulos . '/' . $this->opciones[$this->inicio]);
			}
		} else {
			die ('Error al conectar a la base de datos<br>');
		}
	} // Modulo

	function mostrar ($cosa) {
		echo "<pre>";
		print_r ($cosa);
		echo "</pre>";
	}

	/**************************
	 * Funci�n principal
	 **************************/
	function principal () {

		if ($this->db) {
			// ejecuta la función principal del módulo
			funcion_modulo();
		}
	} // principal
	
	/************************
	 * final()
	 *	Ejecuta los procesos necesarios para cerrar los modulos. Funciona a modo de destructor
	 ************************/
	function terminado () {
		if ($this->db)
			desconexion_db ($this->db);
	} // final

	/************************
	 * getParam (Array, String, , )
	 * Coge un valor de un array y lo devuelve, si no est� definido devuelve el valor $def
	 ************************/
	function getParam( &$arr, $name, $def=null, $mask=0 ) {
		static $noHtmlFilter 	= null;
		static $safeHtmlFilter 	= null;
	
		$return = null;
		if (isset( $arr[$name] )) {
			$return = $arr[$name];
			
			if (is_string( $return )) {
				// trim data
				if (!($mask&_MOS_NOTRIM)) {
					$return = trim( $return );
				}
			}
	
			return $return;
		} else {
			return $def;
		}
	} // getParam
	
	/*****************
	 * ejecuta la peticion sql indicada por indice
	 *****************/
	function resultado_sql($indice, $parametros, $debug=false) {
		global $sql;

		$this->errorSql = '';
		if (isset($sql[$indice])) {
			$consulta = procesa_sql($sql[$indice]);
			if (!is_array($parametros))
				$parametros = Array($parametros);
			if ($debug) {
				echo 'Par&aacute;metros:';
				$this->mostrar($parametros);
			}
				
			$lineas = $this->db->extended->getAll($consulta, null, $parametros);
			if ($debug) {
				echo 'Consulta:<br>'.procesa_sql($sql[$indice]).'<br>';
				echo 'Resultado';
				$this->mostrar($lineas);
			}
			if (!PEAR::isError($lineas)) {
				return $lineas;
			} else {
				$this->errorSql = $lineas;
				return FALSE;
			}
		} else {
			$this->errorSql = true;
			return FALSE;
		}
	}
	
	/******************
	 *
	 ******************/
	function inserta_sql ($indice, $parametros, $debug=false, $campo='') {
		global $sql, $sql_params;
		
		$this->errorSql='';
		if (isset($sql[$indice])) {
			$consulta = procesa_sql ($sql[$indice]);

			if ($campo)
				$consulta = str_replace('-@-', $campo, $consulta);
			if ($debug) {
				echo 'Par&aacute;metros:';
				$this->mostrar($parametros);
				echo 'Consulta:<br>'.procesa_sql($sql[$indice]).'<br>';
			}
				
			if (!is_array($parametros))
				$parametros = array($parametros);
#			$resultado = $this->db->extended->execParam($consulta, $parametros, $sql_params[$indice]);
			$stmt = $this->db->prepare($consulta, $sql_params[$indice], MDB2_PREPARE_MANIP);
	        if (PEAR::isError($stmt)) {
				if ($debug)
					$this->mostrar ($stmt);
				$this->errorSql = $stmt;
				return $stmt;
			}
			$result = $stmt->execute($parametros);
			if (PEAR::isError($result)) {
				if ($debug)
					$this->mostrar ($result);
				$this->errorSql = $result;
				$stmt->free();
			    return $result;
			}

			$stmt->free();
			return $result;
		} else {
			if ($debug)
				echo "La sentencia SQL solicitada ($indice) no existe";
			$this->errorSql = true;
			return FALSE;
		}
	}

	/**************************
	 * inicializa_sesion()
	 * @return: Devuelve true si la sesion es correcta. Si el usuario no est� logueado o la sesi�n ha
	 * caducado, entonces devuelve false
	 **************************/
	function inicializa_sesion() {
		global $nombreSesion;
		
		session_start();
		// Primero se verifica que el usuario est� logueado y que este exista en la base de datos
		if (isset($_SESSION[$nombreSesion])) {
			$usuario = $this->resultado_sql ('SQL_USUARIO', $_SESSION[$nombreSesion]['login']);
			if ($usuario != FALSE) { // Ok, consulta correcta, verifica que exista el usuario
				if (count($usuario)) { // el usuario existe, a ver que no se haya pasado de su timeout
					if (time() <= $_SESSION['ultima'] + $_SESSION['timeout']) { // Correcto, actualiza la hora de la ultima sesi�n
						$_SESSION['ultima'] = time() + $_SESSION['timeout'];
						return true;
                        exit(0);
					}
				}
			}
		}

		// En el caso de que no se cumpla ninguna de las condiciones del if se elimina la sesi�n
		//session_destroy();
		//session_start();
		return false;
	} // inicializa_sesion

	/******************************
	 * repite_accion ()
	 * Se obliga a que se ejecute la acci�n guardada en $this->parametros
	 ******************************/
	function repite_accion($get, $post) {
		if (count ($get))
			$_GET = unserialize(stripslashes($get));
		if (count ($post))
			$_POST = unserialize(stripslashes($post));

		$this->opt = $this->getParam($_GET, 'opt', 'indice');
		$this->principal();
	} // repite_accion

	/*******************************
	 * plantilla()
	 * Carga la plantilla indicada
	 *******************************/
	function plantilla ($nombrePlantilla) {
		global $url, $dirApp;
	
		if (is_string ($nombrePlantilla) && file_exists($this->dirPath."/plantillas/".$nombrePlantilla."/index.php")) {
			$this->rutaPlantilla = $this->dirPath."/plantillas/".$nombrePlantilla."/";
			$this->nombrePlantilla = $nombrePlantilla;
			include $this->rutaPlantilla . "index.php";
		} else
			die ("La plantilla especificada ('$nombrePlantilla') no existe.");
	}
}

?>
