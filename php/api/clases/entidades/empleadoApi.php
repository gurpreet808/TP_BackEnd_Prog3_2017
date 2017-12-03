<?php
require_once "./clases/entidades/empleado.php";
require_once "./clases/AutentificadorJWT.php";
require_once ('./clases/entidades/IApiUsable.php');
//var_dump(scandir("./clases")); //para ver donde estoy parado
session_start();

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
		$elempleado = Empleado::TraerUnEmpleado($mail);
		
		$newResponse = $response;
		
		if (!$elEmpleado) {
			return $newResponse->getBody()->write('<p>ERROR!! No se encontró ese empleado.</p>');			
		}	

    	return $newResponse->withJson($elEmpleado, 200);

		//$newResponse = $response->withJson($elempleado, 200);
		//$newResponse = $newResponse->withAddedHeader('Token', 'unTokenCreado');

    	//return $newResponse;
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

		if (!array_key_exists('nombre', $ArrayDeParametros) 
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
				$rta = $miempleado->Guardarempleado();
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

	/*
	public function MiPerfil($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$tokenAuth = $request->getHeader('Authorization');
		$tokenAuth = $tokenAuth[0];

		autentificadorJWT::dataDelToken($tokenAuth);

		$newResponse = $response;

		if (!array_key_exists('nombre', $ArrayDeParametros) or !array_key_exists('apellido', $ArrayDeParametros) or !array_key_exists('sexo', $ArrayDeParametros) or !array_key_exists('correo', $ArrayDeParametros) or !array_key_exists('clave', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys ("nombre", "apellido", "sexo", "correo" y "clave")</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null or $ArrayDeParametros['apellido']==null or $ArrayDeParametros['sexo']==null or $ArrayDeParametros['correo']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos ("nombre", "apellido", "sexo", "correo" y "clave")</p>';
			}else {
				$miempleado = empleado::TraerUnempleado();
				
				$miempleado->nombre=$ArrayDeParametros['nombre'];
				$miempleado->apellido=$ArrayDeParametros['apellido'];
				$miempleado->sexo=$ArrayDeParametros['sexo'];
				$miempleado->correo=$ArrayDeParametros['correo'];
				
				$miempleado->setClave($ArrayDeParametros['clave']);
				$miempleado->nivel=-4;
				
				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = $miempleado->Guardarempleado();
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
	}*/

    public function BorrarUno($request, $response, $args) {
		$tokenAuth = $request->getHeader('Authorization');
		$tokenAuth = $tokenAuth[0];
		$newResponse = $response;
		 
		if ($_SESSION["user"] == $tokenAuth && $_SESSION["lvl"]==0) {
			$ArrayDeParametros = $request->getParsedBody();
			$id=$ArrayDeParametros['id'];
			
			$empleado= new empleado();
			$empleado->id=$id;
			
			$cantidadDeBorrados=$empleado->Borrarempleado();

			if($cantidadDeBorrados>0){
				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = "Elementos borrados: ".$cantidadDeBorrados;
			}
			else {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = "No se borró nada";	
			}
		} else {
			$newResponse = $newResponse->withAddedHeader('alertType', "danger");
			$newResponse = $newResponse->withStatus(401);
			$rta = "No tiene permiso para borrar";	
		}	

		$newResponse->getBody()->write($rta);
		return $newResponse;
    }
     
    public function ModificarUno($request, $response, $args) {

		$tokenAuth = $request->getHeader('Authorization');
		$tokenAuth = $tokenAuth[0];
		$newResponse = $response;
		 
		if ($_SESSION["user"] == $tokenAuth && $_SESSION["lvl"]==0) {
			$ArrayDeParametros = $request->getParsedBody();
			$newResponse = $response;

			if (!array_key_exists('nombre', $ArrayDeParametros) or !array_key_exists('apellido', $ArrayDeParametros) or !array_key_exists('sexo', $ArrayDeParametros) or !array_key_exists('correo', $ArrayDeParametros) or !array_key_exists('nivel', $ArrayDeParametros)) {
				$newResponse = $newResponse->withAddedHeader('alertType', "warning");
				$rta = '<p>Ingrese todas las keys ("nombre", "apellido", "sexo", "correo" y "nivel")</p>';
			} else {
				if ($ArrayDeParametros['nombre']==null or $ArrayDeParametros['apellido']==null or $ArrayDeParametros['sexo']==null or $ArrayDeParametros['correo']==null) {
					$newResponse = $newResponse->withAddedHeader('alertType', "danger");
					$rta = '<p>ERROR!! Ingrese todos los datos ("nombre", "apellido", "sexo", "correo" y "nivel")</p>';
				}else {
					$idempleado = $request->getHeader('UserNum');
					$idempleado = $idempleado[0];
					
					$miempleado = empleado::TraerUnempleadoPorId($idempleado);
					
					$miempleado->nombre=$ArrayDeParametros['nombre'];
					$miempleado->apellido=$ArrayDeParametros['apellido'];
					$miempleado->sexo=$ArrayDeParametros['sexo'];
					$miempleado->correo=$ArrayDeParametros['correo'];
					$miempleado->nivel=$ArrayDeParametros['nivel'];
					
					$newResponse = $newResponse->withAddedHeader('alertType', "success");
					if ($miempleado->Modificarempleado()>0) {
						$rta = "empleado modificado";
					} else {
						$rta = "No se modificó el empleado";
					}
				}	
			}			
		} else {
			$newResponse = $newResponse->withAddedHeader('alertType', "danger");
			$newResponse = $newResponse->withStatus(401);
			$rta = "No tiene permiso para modificar";	
		}		

		$newResponse->getBody()->write($rta);

        return $newResponse;	
    }

	public function LogIn($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);
		$newResponse = $response;

		if (!array_key_exists('correo', $ArrayDeParametros) or !array_key_exists('clave', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys ("correo" y "clave")</p>';
		} else {
			if ($ArrayDeParametros['correo']==null or $ArrayDeParametros['clave']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos ("correo" y "clave")</p>';
			}else {
				$correo= $ArrayDeParametros['correo'];
				$clave= $ArrayDeParametros['clave'];


				switch (empleado::VerificarClave($correo,$clave)) {
					case true:
						$unempleado = empleado::TraerUnempleado($correo);
        				
						$_SESSION["user"] = $correo;
        				$_SESSION["lvl"] = $unempleado->nivel;

						
						//Datos para el token
						$datosempleado = array(
							'nombre' => $unempleado->nombre,
							'correo' => $unempleado->correo,
							'nivel' => $unempleado->nivel,
							'sexo' => $unempleado->sexo
						);

						$token = autentificadorJWT::crearJWT($datosempleado);
						$newResponse = $newResponse->withAddedHeader('token', $token);
						
						$newResponse = $newResponse->withAddedHeader('datos', json_encode($datosempleado));

						$newResponse = $newResponse->withAddedHeader('alertType', "success");
						$rta = "<strong>¡Bien!</strong> empleado (e-mail) y clave válidos";
						
						break;
					
					case false:
						$newResponse = $newResponse->withAddedHeader('alertType', "danger");
						$rta = "<strong>ERROR!</strong> empleado y clave inválidos";
						break;

					case 'NOMAIL':
						$newResponse = $newResponse->withAddedHeader('alertType', "warning");
						$rta = "No se encuentra el mail que ingresó";
						break;
					
					default:
						# code...
						break;
				}
			}	
		}
		$newResponse->getBody()->write($rta);
		
		return $newResponse;
    }

	public function LogOut($request, $response, $args) {
		$_SESSION["user"] = null;
        $_SESSION["lvl"] = null;
		session_unset();
        session_destroy();
		
		return $response->getBody()->write("Deslogueo Correcto");
    }


}
?>