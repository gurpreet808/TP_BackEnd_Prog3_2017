<?php
require_once "./clases/entidades/empleado.php";
require_once "./clases/AutentificadorJWT.php";
require_once ('./clases/entidades/IApiUsable.php');
//var_dump(scandir("./clases")); //para ver donde estoy parado
//session_start();

class empleadoApi extends Empleado implements IApiUsable{

	public function CheckBBDD($request, $response, $next) {
		$newResponse = $response;

		try {
			Empleado::TraerTodosLosEmpleados();
			$newResponse = $next($request, $response);
			
		} catch (Exception $e) {
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$consulta =$objetoAccesoDato->RetornarConsulta("CREATE TABLE `empleados` (
				`id` INT NOT NULL AUTO_INCREMENT, 
				`nombre` VARCHAR(45) NOT NULL, 
				`apellido` VARCHAR(45) NOT NULL, 
				`clave` LONGTEXT NOT NULL,
				`mail` VARCHAR(45) NOT NULL UNIQUE,
				`turno` VARCHAR(45) NOT NULL,
				`perfil` VARCHAR(45) NOT NULL,
				`fecha_creacion` VARCHAR(45) NOT NULL,
				`foto` VARCHAR(45), 
				PRIMARY KEY (`id`)
			)");
			
			$newResponse->getBody()->write($consulta->execute());
		}
		return $newResponse;
	}
	
	public function LlenarBBDD(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO empleados (nombre,apellido,clave,mail,turno,perfil,fecha_creacion)
	 		values
			 ('Administrador','Administrator','admin','admin@admin.com','mañana','admin','15/11/16'), 
			 ('Empleado','User','user','user@user.com','tarde','user','24/12/16')");
	
		return $consulta->execute();
	}

 	public function TraerUno($request, $response, $args) {
     	$mail = $args['mail'];
		$elEmpleado = Empleado::TraerUnEmpleado($mail);
		
		$newResponse = $response;
		
		if (!$elEmpleado) {
			return $newResponse->getBody()->write('<p>ERROR!! No se encontró ese empleado.</p>');			
		}	

    	return $newResponse->withJson($elEmpleado, 200);
	}
	
    public function TraerTodos($request, $response, $args) {
      	$todosLosEmpleados = Empleado::TraerTodosLosEmpleados();
     	$response = $response->withJson($todosLosEmpleados, 200);  
		
		 return $response;
	}
	
    public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;

		if ($ArrayDeParametros == null
		or !array_key_exists('nombre', $ArrayDeParametros) 
		or !array_key_exists('apellido', $ArrayDeParametros) 
		or !array_key_exists('clave', $ArrayDeParametros) 
		or !array_key_exists('mail', $ArrayDeParametros) 
		or !array_key_exists('turno', $ArrayDeParametros)
		or !array_key_exists('perfil', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys (
				"nombre", 
				"apellido", 
				"clave",
				"mail",
				"turno" y 
				"perfil"
				)</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null 
			or $ArrayDeParametros['apellido']==null 
			or $ArrayDeParametros['clave']==null
			or $ArrayDeParametros['mail']==null 
			or $ArrayDeParametros['turno']==null
			or $ArrayDeParametros['perfil']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos (
					"nombre", 
					"apellido", 
					"clave",
					"mail",
					"turno" y 
					"perfil"
					)</p>';
			}else {

				if ($ArrayDeParametros['turno'] != "mañana" 
				&& $ArrayDeParametros['turno'] != "tarde" 
				&& $ArrayDeParametros['turno'] != "noche") {
					return $newResponse->getBody()->write('<p>ERROR!! Sólo puede ingresar "mañana", "tarde" o "noche" en el turno.</p>');
				}

				if ($ArrayDeParametros['perfil'] != "usuario" 
				&& $ArrayDeParametros['perfil'] != "administrador") {
					return $newResponse->getBody()->write('<p>ERROR!! Sólo puede ingresar "usuario" o "administrador" en el perfil.</p>');
				}

				if (!empty(Empleado::TraerUnEmpleado($ArrayDeParametros['mail']))) {
					return $newResponse->getBody()->write('<p>ERROR!! Ese mail ya está registrado.</p>');
				}

				$miempleado = new empleado();
				
				$miempleado->nombre=$ArrayDeParametros['nombre'];
				$miempleado->apellido=$ArrayDeParametros['apellido'];
				$miempleado->mail=$ArrayDeParametros['mail'];
				$miempleado->turno=$ArrayDeParametros['turno'];
				$miempleado->perfil=$ArrayDeParametros['perfil'];
				$miempleado->fecha_creacion=date("Y-m-d H:i:s");

				$miempleado->setClave($ArrayDeParametros['clave']);
				
				$newResponse = $newResponse->withAddedHeader('alertType', "success");

				$rta = $miempleado->GuardarEmpleado();
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

	public function LogIn($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;

		if ($ArrayDeParametros == null
		or !array_key_exists('mail', $ArrayDeParametros) 
		or !array_key_exists('clave', $ArrayDeParametros)
		or !array_key_exists('clave_coincidencia', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys (
				"mail",
				"clave" y 
				"clave_coincidencia"
				)</p>';
		} else {
			if ($ArrayDeParametros['mail']==null 
			or $ArrayDeParametros['clave']==null
			or $ArrayDeParametros['clave_coincidencia']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos (
					"mail",
					"clave" y 
					"clave_coincidencia"
					)</p>';
			}else {

				$mail=$ArrayDeParametros['mail'];
				$clave=$ArrayDeParametros['clave'];
				$clave_coincidencia=$ArrayDeParametros['clave_coincidencia'];

				if ($clave != $clave_coincidencia) {
					$rta = '<p>ERROR!! Las claves no coinciden</p>';
				} else {					
					
					//Dar token

					$token = "";

					switch (empleado::VerificarClave($mail, $clave)) {
						case "VALIDO":
							$unEmpleado = empleado::TraerUnEmpleado($mail);
							
							//Datos para el token
							$datosEmpleado = array(
								'id' => $unEmpleado->id,
								'nombre' => $unEmpleado->nombre,
								'mail' => $unEmpleado->mail,
								'perfil' => $unEmpleado->perfil
							);
	
							$token = autentificadorJWT::crearJWT($datosEmpleado);
							$newResponse = $newResponse->withAddedHeader('token', $token);
							
							$newResponse = $newResponse->withAddedHeader('datos', json_encode($datosEmpleado));
	
							$newResponse = $newResponse->withAddedHeader('alertType', "success");
							$rta = "<strong>¡Bien!</strong> empleado (e-mail) y clave válidos";
							
							break;
						
						case "NO_VALIDO":
							$newResponse = $newResponse->withAddedHeader('alertType', "danger");
							$newResponse = $newResponse->withAddedHeader('token', $token);
							$rta = "<strong>ERROR!</strong> empleado y clave inválidos";
							break;
	
						case 'NO_MAIL':
							$newResponse = $newResponse->withAddedHeader('alertType', "warning");
							$newResponse = $newResponse->withAddedHeader('token', $token);
							$rta = "No se encuentra el mail que ingresó";
							break;
						
						default:
							# code...
							break;
					}
				}				
			}	
		}

		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

    public function BorrarUno($request, $response, $args) {
		$newResponse = $response;
		
		$ArrayDeParametros = $request->getParsedBody();
		$id = $ArrayDeParametros['id'];

		if(empty(Empleado::TraerUnEmpleadoPorId($id))){
			$newResponse = $newResponse->withAddedHeader('alertType', "danger");
			$rta = "No se encontró ese empleado";
		} else {
			$empleado = new empleado();
			$empleado->id = $id;
			
			$cantidadDeBorrados = $empleado->BorrarEmpleado();

			if($cantidadDeBorrados>0) {
				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = "Elementos borrados: ".$cantidadDeBorrados;
			} else {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = "No se puedo borrar empleado";	
			}
		}

		$newResponse->getBody()->write($rta);

		return $newResponse;
    }
     
    public function ModificarUno($request, $response, $args) {
		$newResponse = $response;
		
		$ArrayDeParametros = $request->getParsedBody();

		if ($ArrayDeParametros == null
		or !array_key_exists('id', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese debe ingresar al menos la key "id"</p>';
		} else {
			if ($ArrayDeParametros['id']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Complete el campo de la key "id"</p>';
			}else {

				$miempleado = empleado::TraerUnempleadoPorId($ArrayDeParametros['id']);

				$array_nombre = self::comprobar_key("nombre", $ArrayDeParametros);
				if ($array_nombre["esValido"]) {
					$miempleado->nombre=$ArrayDeParametros['nombre'];
				} elseif (array_key_exists('msg', $array_nombre)) {
					return $newResponse->getBody()->write($array_nombre["msg"]);
				}

				$array_apellido = self::comprobar_key("apellido", $ArrayDeParametros);
				if ($array_apellido["esValido"]) {
					$miempleado->apellido=$ArrayDeParametros['apellido'];
				} elseif (array_key_exists('msg', $array_apellido)) {
					return $newResponse->getBody()->write($array_apellido["msg"]);
				}

				
				$array_mail = self::comprobar_key("mail", $ArrayDeParametros);
				if ($array_mail["esValido"]) {
					if (!empty(Empleado::TraerUnEmpleado($ArrayDeParametros['mail'])) && (Empleado::TraerUnEmpleado($ArrayDeParametros['mail']))->id != $miempleado->id) {
						return $newResponse->getBody()->write('<p>ERROR!! Ese mail ya está registrado.</p>');
					}

					$miempleado->mail=$ArrayDeParametros['mail'];

				} elseif (array_key_exists('msg', $array_mail)) {
					return $newResponse->getBody()->write($array_mail["msg"]);
				}

				$array_turno = self::comprobar_key("turno", $ArrayDeParametros);
				if ($array_turno["esValido"]) {
					if ($ArrayDeParametros['turno'] != "mañana" 
					&& $ArrayDeParametros['turno'] != "tarde" 
					&& $ArrayDeParametros['turno'] != "noche") {
						return $newResponse->getBody()->write('<p>ERROR!! Sólo puede ingresar "mañana", "tarde" o "noche" en el turno.</p>');
					}

					$miempleado->turno=$ArrayDeParametros['turno'];
				} elseif (array_key_exists('msg', $array_turno)) {
					return $newResponse->getBody()->write($array_turno["msg"]);
				}

				$array_perfil = self::comprobar_key("perfil", $ArrayDeParametros);
				if ($array_perfil["esValido"]) {
					if ($ArrayDeParametros['perfil'] != "usuario" 
					&& $ArrayDeParametros['perfil'] != "administrador"
					&& $ArrayDeParametros['perfil'] != "suspendido") {
						return $newResponse->getBody()->write('<p>ERROR!! Sólo puede ingresar "usuario" o "administrador" en el perfil.</p>');
					}

					$miempleado->perfil=$ArrayDeParametros['perfil'];
				} elseif (array_key_exists('msg', $array_perfil)) {
					return $newResponse->getBody()->write($array_perfil["msg"]);
				}

				$array_clave = self::comprobar_key("clave", $ArrayDeParametros);
				if ($array_clave["esValido"]) {
					$miempleado->setClave($ArrayDeParametros['clave']);
				} elseif (array_key_exists('msg', $array_clave)) {
					return $newResponse->getBody()->write($array_clave["msg"]);
				}

				/*
				$miempleado->nombre=$ArrayDeParametros['nombre'];
				$miempleado->apellido=$ArrayDeParametros['apellido'];
				$miempleado->mail=$ArrayDeParametros['mail'];
				$miempleado->turno=$ArrayDeParametros['turno'];
				$miempleado->perfil=$ArrayDeParametros['perfil'];

				$miempleado->setClave($ArrayDeParametros['clave']);
				*/

				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				if ($miempleado->ModificarEmpleado()>0) {
					$rta = "Empleado modificado";
					$newResponse = $newResponse->withAddedHeader('alertType', "success");
				} else {
					$rta = "No se modificó el empleado";
				}				
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;	
    }

	public function LogOut($request, $response, $args) {

		$newResponse = $response;		
		$newResponse = $newResponse->withAddedHeader('Authorization', "Bye bye..");

		return $newResponse->getBody()->write("Deslogueo Correcto");
	}
	
	public static function comprobar_key($tag, $unArray){
        $rta_array = array();
        $rta_array["esValido"] = false;
        
        if (array_key_exists($tag, $unArray)) {
			if ($unArray[$tag]==null) {
                $rta_array["msg"] = '<p>ERROR!! Complete el campo de la key "'.$tag.'" </p>';
			} else {
                $rta_array["esValido"] = true;
            }
        }

        return $rta_array;
    }
}
?>