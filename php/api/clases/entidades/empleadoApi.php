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
		or !array_key_exists('perfil', $ArrayDeParametros)
		or !array_key_exists('fecha_creacion', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys (
				"nombre", 
				"apellido", 
				"clave",
				"mail",
				"turno", 
				"perfil" y 
				"fecha_creacion"
				)</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null 
			or $ArrayDeParametros['apellido']==null 
			or $ArrayDeParametros['clave']==null
			or $ArrayDeParametros['mail']==null 
			or $ArrayDeParametros['turno']==null
			or $ArrayDeParametros['perfil']==null
			or $ArrayDeParametros['fecha_creacion']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos (
					"nombre", 
					"apellido", 
					"clave",
					"mail",
					"turno", 
					"perfil" y 
					"fecha_creacion"
					)</p>';
			}else {
				$miempleado = new empleado();
				
				$miempleado->nombre=$ArrayDeParametros['nombre'];
				$miempleado->apellido=$ArrayDeParametros['apellido'];
				$miempleado->mail=$ArrayDeParametros['mail'];
				$miempleado->turno=$ArrayDeParametros['turno'];
				$miempleado->perfil=$ArrayDeParametros['perfil'];
				$miempleado->fecha_creacion=$ArrayDeParametros['fecha_creacion'];

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
			
			$cantidadDeBorrados = $empleado->Borrarempleado();

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
		or !array_key_exists('id', $ArrayDeParametros) 
		or !array_key_exists('nombre', $ArrayDeParametros) 
		or !array_key_exists('apellido', $ArrayDeParametros) 
		or !array_key_exists('clave', $ArrayDeParametros) 
		or !array_key_exists('mail', $ArrayDeParametros) 
		or !array_key_exists('turno', $ArrayDeParametros)
		or !array_key_exists('perfil', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys (
				"id",
				"nombre", 
				"apellido", 
				"clave",
				"mail",
				"turno" y  
				"perfil"
				)</p>';
		} else {
			if ($ArrayDeParametros['id']==null
			or $ArrayDeParametros['nombre']==null  
			or $ArrayDeParametros['apellido']==null 
			or $ArrayDeParametros['clave']==null
			or $ArrayDeParametros['mail']==null 
			or $ArrayDeParametros['turno']==null
			or $ArrayDeParametros['perfil']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos (
					"id",
					"nombre", 
					"apellido", 
					"clave",
					"mail",
					"turno" y 
					"perfil"
					)</p>';
			}else {
				$miempleado = empleado::TraerUnempleadoPorId($ArrayDeParametros['id']);

				$miempleado->nombre=$ArrayDeParametros['nombre'];
				$miempleado->apellido=$ArrayDeParametros['apellido'];
				$miempleado->mail=$ArrayDeParametros['mail'];
				$miempleado->turno=$ArrayDeParametros['turno'];
				$miempleado->perfil=$ArrayDeParametros['perfil'];

				$miempleado->setClave($ArrayDeParametros['clave']);

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
}
?>