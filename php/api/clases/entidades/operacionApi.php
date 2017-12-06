<?php
require_once "./clases/entidades/operacion.php";
require_once "./clases/AutentificadorJWT.php";
require_once ('./clases/entidades/IApiUsable.php');
//var_dump(scandir("./clases")); //para ver donde estoy parado
//session_start();

class operacionApi extends operacion implements IApiUsable{

	public function CheckBBDD($request, $response, $next) {
		$newResponse = $response;

		try {
			operacion::TraerTodasLasOperaciones();
			$newResponse = $next($request, $response);
			
		} catch (Exception $e) {
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$consulta =$objetoAccesoDato->RetornarConsulta("CREATE TABLE `operaciones` (
				`patente` VARCHAR(10) NOT NULL, 
				`color` VARCHAR(45) NOT NULL,
				`marca` VARCHAR(45) NOT NULL,
				`cochera` INT NOT NULL,  
				`foto` VARCHAR(45), 
				`id_empleado_ingreso` INT NOT NULL,
				`fecha_hora_ingreso` VARCHAR(45) NOT NULL,
				`id_empleado_salida` INT,
				`fecha_hora_salida` VARCHAR(45),
				`tiempo` INT,
				`importe` FLOAT, 
				PRIMARY KEY (`patente`,`fecha_hora_ingreso`),
    			FOREIGN KEY (`id_empleado_ingreso`) REFERENCES `empleados`(`id`),
				FOREIGN KEY (`id_empleado_salida`) REFERENCES `empleados`(`id`),
				FOREIGN KEY (`cochera`) REFERENCES `cocheras`(`id_cochera`)
			)");
			
			$newResponse->getBody()->write($consulta->execute());
		}
		return $newResponse;
	}
	
	/*
	public function LlenarBBDD(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO operaciones (nombre,apellido,clave,mail,turno,perfil,fecha_creacion)
	 		values
			 ('Administrador','Administrator','admin','admin@admin.com','mañana','admin','15/11/16'), 
			 ('operacion','User','user','user@user.com','tarde','user','24/12/16')");
	
		return $consulta->execute();
	}*/

 	public function TraerUno($request, $response, $args) {
     	$mail = $args['mail'];
		$eloperacion = operacion::TraerUnoperacion($mail);
		
		$newResponse = $response;
		
		if (!$eloperacion) {
			return $newResponse->getBody()->write('<p>ERROR!! No se encontró ese operacion.</p>');			
		}	

    	return $newResponse->withJson($eloperacion, 200);
	}
	
    public function TraerTodos($request, $response, $args) {
      	$todasLasOperaciones = operacion::TraerTodasLasOperaciones();
     	$response = $response->withJson($todasLasOperaciones, 200);  
		
		return $response;
	}

	public function OperacionesVehiculo($request, $response, $args) {
		$patente = $args['patente'];
		$todasLasOperaciones = operacion::TraerOperacionesDeUnVehiculo($patente);
		
		$newResponse = $response;
		
		if (!$todasLasOperaciones) {
			return $newResponse->getBody()->write('<p>ERROR!! No se encontró esa patente.</p>');			
		}	

		return $newResponse->withJson($todasLasOperaciones, 200);
	}

	public function BuscarCocherasLibres($request, $response, $args) {
		//$todasLasOperaciones = operacion::TraerTodasLasOperaciones();
		//$response = $response->withJson($todasLasOperaciones, 200);  
		
		return operacion::CocherasLibres();
	}
	
    public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;

		if ($ArrayDeParametros == null
		or !array_key_exists('patente', $ArrayDeParametros) 
		or !array_key_exists('color', $ArrayDeParametros) 
		or !array_key_exists('marca', $ArrayDeParametros)
		or !array_key_exists('discapacitado_embarazada', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys (
				"patente", 
				"color", 
				"marca" y
				"discapacitado_embarazada"
				)</p>';
		} else {
			if ($ArrayDeParametros['patente']==null 
			or $ArrayDeParametros['color']==null 
			or $ArrayDeParametros['marca']==null
			or $ArrayDeParametros['discapacitado_embarazada']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos (
					"patente", 
					"color",
					"marca" y
					"discapacitado_embarazada"
					)</p>';
			}else {

				if ($ArrayDeParametros['discapacitado_embarazada']!=="si" and $ArrayDeParametros['discapacitado_embarazada']!=="no") {
					$rta = '<p>ERROR!! debe ingresar "si" o "no" en "discapacitado_embarazada")</p>';
				} else {
					$todasLasOperaciones = operacion::TraerOperacionesDeUnVehiculo($ArrayDeParametros['patente']);
					
					if ($todasLasOperaciones) {
						return $newResponse->getBody()->write('<p>ERROR!! Ese vehiculo ya está estacionado en '."cochera".'.</p>');			
					} else {
						
						operacion::CocherasLibres();
						
						$mioperacion = new operacion();
						
						$mioperacion->patente=$ArrayDeParametros['patente'];
						$mioperacion->color=$ArrayDeParametros['color'];
						$mioperacion->marca=$ArrayDeParametros['marca'];
						$mioperacion->fecha_hora_ingreso=date("Y-m-d H:i:s");
	
						$mioperacion->cochera=1;
		
						//extraer del token el ID
		
						//tomo el token del header
						$arrayConToken = $request->getHeader('Authorization');
						//var_dump($arrayConToken);
						
						$token = "";
		
						if (!empty($arrayConToken)) {
							$token = $arrayConToken[0];			
						}
		
						$datos = autentificadorJWT::dataDelToken($token);
		
						//var_dump($datos);
		
						$mioperacion->id_empleado_ingreso=$datos["id"];
						
						$newResponse = $newResponse->withAddedHeader('alertType', "success");
		
						$rta = $mioperacion->EstacionarVehiculo();
		
						if ($rta) {
							$rta = "Estacionó el vehiculo";
						} else {
							$rta = "No pudo estacionar el vehiculo";
						}
					}				
				}
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
	}
	
	public function SacarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;

		if ($ArrayDeParametros == null
		or !array_key_exists('patente', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas la key "patente"</p>';
		} else {
			if ($ArrayDeParametros['patente']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese los datos de la patente.</p>';
			}else {
				$todasLasOperaciones = operacion::TraerOperacionesDeUnVehiculo($ArrayDeParametros['patente']);
				$mioperacion = operacion::VehiculoEstacionado($ArrayDeParametros['patente']);
				//var_dump($mioperacion);
				
				if (!$todasLasOperaciones or !$mioperacion) {
					return $newResponse->getBody()->write('<p>ERROR!! Ese vehiculo no está estacionado.');			
				} else {					
					

					//Validar que el array que trae vehiculo estacionado no sea array vacío
					$mioperacion = $mioperacion[0];
	
					$mioperacion->fecha_hora_salida=date("Y-m-d H:i:s");
					$mioperacion->fecha_hora_salida="2017-12-05 21:37:48";
						
					//tomo el token del header
					$arrayConToken = $request->getHeader('Authorization');
					//var_dump($arrayConToken);
					$token = "";
					if (!empty($arrayConToken)) {
						$token = $arrayConToken[0];			
					}
					$datos = autentificadorJWT::dataDelToken($token);
					//var_dump($datos);	
					$mioperacion->id_empleado_salida=$datos["id"];
					
					//calcular diferencia entre dates
					if(!$mioperacion->CalcularImporte()){
						return $newResponse->getBody()->write("ERROR!! no se pudo calcular el importe.");
					}
					
					$newResponse = $newResponse->withAddedHeader('alertType', "success");

					$rta = $mioperacion->SacarVehiculo();
	
					if ($rta) {
						$rta = "Se retiró el vehículo.".
						"<br><br>Patente: ".$mioperacion->patente.
						"<br>Color: ".$mioperacion->color.
						"<br>Marca: ".$mioperacion->marca.
						"<br>Fecha y hora de ingreso: ".$mioperacion->fecha_hora_ingreso.
						"<br>Fecha y hora de salida: ".$mioperacion->fecha_hora_salida.
						"<br>Estadía (hs): ".$mioperacion->tiempo.
						"<br>Cochera: ".$mioperacion->cochera.
						"<br><br>IMPORTE: $".$mioperacion->importe;
					} else {
						$rta = "No pudo sacar el vehículo";
					}
				}
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

					switch (operacion::VerificarClave($mail, $clave)) {
						case "VALIDO":
							$unoperacion = operacion::TraerUnoperacion($mail);
							
							//Datos para el token
							$datosoperacion = array(
								'nombre' => $unoperacion->nombre,
								'mail' => $unoperacion->mail,
								'perfil' => $unoperacion->perfil
							);
	
							$token = autentificadorJWT::crearJWT($datosoperacion);
							$newResponse = $newResponse->withAddedHeader('token', $token);
							
							$newResponse = $newResponse->withAddedHeader('datos', json_encode($datosoperacion));
	
							$newResponse = $newResponse->withAddedHeader('alertType', "success");
							$rta = "<strong>¡Bien!</strong> operacion (e-mail) y clave válidos";
							
							break;
						
						case "NO_VALIDO":
							$newResponse = $newResponse->withAddedHeader('alertType', "danger");
							$newResponse = $newResponse->withAddedHeader('token', $token);
							$rta = "<strong>ERROR!</strong> operacion y clave inválidos";
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

		if(empty(operacion::TraerUnoperacionPorId($id))){
			$newResponse = $newResponse->withAddedHeader('alertType', "danger");
			$rta = "No se encontró ese operacion";
		} else {
			$operacion = new operacion();
			$operacion->id = $id;
			
			$cantidadDeBorrados = $operacion->Borraroperacion();

			if($cantidadDeBorrados>0) {
				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = "Elementos borrados: ".$cantidadDeBorrados;
			} else {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = "No se puedo borrar operacion";	
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
				$mioperacion = operacion::TraerUnoperacionPorId($ArrayDeParametros['id']);

				$mioperacion->nombre=$ArrayDeParametros['nombre'];
				$mioperacion->apellido=$ArrayDeParametros['apellido'];
				$mioperacion->mail=$ArrayDeParametros['mail'];
				$mioperacion->turno=$ArrayDeParametros['turno'];
				$mioperacion->perfil=$ArrayDeParametros['perfil'];

				$mioperacion->setClave($ArrayDeParametros['clave']);

				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				if ($mioperacion->Modificaroperacion()>0) {
					$rta = "operacion modificado";
					$newResponse = $newResponse->withAddedHeader('alertType', "success");
				} else {
					$rta = "No se modificó el operacion";
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