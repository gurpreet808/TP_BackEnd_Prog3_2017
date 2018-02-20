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
				FOREIGN KEY (`cochera`) REFERENCES `cocheras`(`id_cochera`)
			)");
			/*
    			FOREIGN KEY (`id_empleado_ingreso`) REFERENCES `empleados`(`id`),
				FOREIGN KEY (`id_empleado_salida`) REFERENCES `empleados`(`id`),
			*/
			
			$newResponse->getBody()->write($consulta->execute());
		}
		return $newResponse;
	}
	
	/*
	public function LlenarBBDD(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO `operaciones` (`patente`, `color`, `marca`, `cochera`, `id_empleado_ingreso`, `fecha_hora_ingreso`, `id_empleado_salida`, `fecha_hora_salida`, `tiempo`, `importe`) VALUES
		('AAA111', 'Azul', 'Fiat', 5, 2, '2017-10-01 11:46:24', NULL, NULL, NULL, NULL),
		('AAA222', 'Negro', 'Peugeot', 6, 2, '2017-10-01 12:32:44', NULL, NULL, NULL, NULL),
		('AAA333', 'Verde', 'Citroen', 1, 2, '2017-10-01 12:36:54', NULL, NULL, NULL, NULL),
		('AAA444', 'Blanco', 'Volkswagen', 2, 3, '2017-10-02 15:47:41', NULL, NULL, NULL, NULL),
		('AAA555', 'Rojo', 'Renault', 3, 2, '2017-10-02 12:43:46', NULL, NULL, NULL, NULL),
		('AAA666', 'Gris', 'Hyundai', 21, 4, '2017-10-02 21:47:59', NULL, NULL, NULL, NULL),
		('AAA777', 'Dorado', 'Porsche', 11, 2, '2017-10-03 08:18:22', NULL, NULL, NULL, NULL),
		('AAA888', 'Azul', 'Mitsubishi', 15, 2, '2017-10-03 10:48:27', NULL, NULL, NULL, NULL),
		('AAA999', 'Amarillo', 'Subaru', 8, 3, '2017-10-03 13:21:31', NULL, NULL, NULL, NULL),
		('BBB111', 'Gris', 'Toyota', 12, 2, '2017-10-04 09:48:40', NULL, NULL, NULL, NULL),
		('BBB222', 'Negro', 'Ford', 4, 2, '2017-10-04 12:38:44', NULL, NULL, NULL, NULL),
		('BBB333', 'Blanco', 'Chevrolet', 18, 4, '2017-11-12 22:48:48', NULL, NULL, NULL, NULL),
		('BBB444', 'Azul', 'Honda', 17, 2, '2017-11-12 10:28:57', NULL, NULL, NULL, NULL),
		('BBB555', 'Rojo', 'Mazda', 7, 2, '2017-12-10 09:49:03', NULL, NULL, NULL, NULL),
		('BBB666', 'Verde', 'Dodge', 16, 2, '2017-12-11 10:19:08', NULL, NULL, NULL, NULL),
		('BBB777', 'Amarillo', 'Jeep', 19, 3, '2017-12-12 15:32:22', NULL, NULL, NULL, NULL)");
	
		return $consulta->execute();
	}*/

 	public function TraerUno($request, $response, $args) {
     	$mail = $args['mail'];
		$laOperacion = operacion::TraerUnoperacion($mail);
		
		$newResponse = $response;
		
		if (!$laOperacion) {
			return $newResponse->getBody()->write('<p>ERROR!! No se encontró ese operacion.</p>');			
		}	

    	return $newResponse->withJson($laOperacion, 200);
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

	public function OperacionesEmpleado($request, $response, $args) {
		$id_emp = $args['id_empleado'];
		$newResponse = $response;

		if (!is_numeric($id_emp)) {
			return $newResponse->getBody()->write('<p>Debe ingresar el id del empleado.</p>');
		} else {
			
			$id_emp = (int)$id_emp;
			
			if (!empty(Empleado::TraerUnempleadoPorId($id_emp))) {
				$todasLasOperaciones = operacion::TraerOperacionesDeUnEmpleado($id_emp);
				
				if (!$todasLasOperaciones) {
					return $newResponse->getBody()->write('<p>Sin operaciones.</p>');			
				}	
		
				return $newResponse->withJson($todasLasOperaciones, 200);
			} else {
				return $newResponse->getBody()->write('<p>No se encontró el empleado con id = '.$id_emp.'.</p>');
			}
		}
	}

	public function BuscarCocherasLibres($request, $response, $args) {
		//$todasLasOperaciones = operacion::TraerTodasLasOperaciones();
		//$response = $response->withJson($todasLasOperaciones, 200);  
		var_dump(operacion::CocherasLibres());

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
					
					$vehiculo = "NO";

					foreach ($todasLasOperaciones as $key => $value) {
						if ($value->fecha_hora_salida == NULL) {
							$vehiculo = $value;
						}
					}
					
					if ($vehiculo != "NO") {
						$nombre_cochera = operacion::NombreCochera($vehiculo->cochera);
						return $newResponse->getBody()->write('<p>ERROR!! Ese vehiculo ya está estacionado en '.$nombre_cochera.'.</p>');			
					} else {
						
						$libres = operacion::CocherasLibres($ArrayDeParametros['discapacitado_embarazada']);
						if (empty($libres)) {
							$rta = '<p>ERROR!! No hay más lugar en el estacionamiento';
							if ($ArrayDeParametros['discapacitado_embarazada']=="si") {
								$rta = $rta.' para discapacitados.</p><p>Puede consultarle al cliente si desea un lugar que no está reservado para embarazadas o discapacitados.</p>';
							} else {
								$rta = $rta.'.</p>';
							}
							return $newResponse->getBody()->write($rta);
						} else {
							
							$mioperacion = new operacion();

							
							$mioperacion->patente=$ArrayDeParametros['patente'];
							$mioperacion->color=$ArrayDeParametros['color'];
							$mioperacion->marca=$ArrayDeParametros['marca'];
							$mioperacion->fecha_hora_ingreso=date("Y-m-d H:i:s");
							
							//cohera random
							$mioperacion->cochera = $libres[array_rand($libres, 1)];
							
							if(!operacion::UsarCochera($mioperacion->cochera)){
								return $newResponse->getBody()->write("<p>ERROR!! No se pudo contar el uso.</p>");
							}
			
							//tomo el token del header para ID empleado
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
					$mioperacion = $mioperacion[0];
	
					$mioperacion->fecha_hora_salida=date("Y-m-d H:i:s");
					//$mioperacion->fecha_hora_salida="2017-12-05 21:37:48";
						
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

					$nombre_cochera = operacion::NombreCochera($mioperacion->cochera);
	
					if ($rta) {
						$rta = "Se retiró el vehículo.".
						"<br><br>Patente: ".$mioperacion->patente.
						"<br>Color: ".$mioperacion->color.
						"<br>Marca: ".$mioperacion->marca.
						"<br>Fecha y hora de ingreso: ".$mioperacion->fecha_hora_ingreso.
						"<br>Fecha y hora de salida: ".$mioperacion->fecha_hora_salida.
						"<br>Estadía: ".$mioperacion->tiempo." hs.".
						"<br>Cochera: ".$nombre_cochera.
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
		$newResponse = $newResponse->withAddedHeader('Authorization', "Chau..");

		return $newResponse->getBody()->write("Deslogueo Correcto");
	}
}
?>