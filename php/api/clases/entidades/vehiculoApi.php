<?php
require_once "../clases/vehiculo.php";
require_once "../clases/usuario.php";
require_once ('./APIclases/IApiUsable.php');
require_once "../clases/autentificadorjwt.php";

class VehiculoApi extends Vehiculo implements IApiUsable{
 	public function TraerUno($request, $response, $args) {
     	$patente=$args['patente'];
    	$elVehiculo=vehiculo::TraerUnVehiculo($patente);
		$newResponse = $response->withJson($elVehiculo, 200);
		$newResponse = $newResponse->withAddedHeader('Token', 'unTokenCreado');

    	return $newResponse;
    }
    public function TraerTodos($request, $response, $args) {
      	$todosLosvehiculos=vehiculo::TraerTodosLosvehiculos();
     	$response = $response->withJson($todosLosvehiculos, 200);  
    	return $response;
    }
    public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;

		if (!array_key_exists('patente', $ArrayDeParametros) or !array_key_exists('color', $ArrayDeParametros) or !array_key_exists('marca', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys ("patente", "color" y "marca")</p>';
		} else {
			if ($ArrayDeParametros['patente']==null or $ArrayDeParametros['color']==null or $ArrayDeParametros['marca']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos ("patente", "color" y "marca")</p>';
			}else {
				//falta validar que si el correo no existe, es decir es un nuevo vehiculo, solicite todas las keys.
				//Para eso mejor usar PATCH para modificaciones
				$miVehiculo = new vehiculo();
				
				$miVehiculo->patente=$ArrayDeParametros['patente'];
				$miVehiculo->color=$ArrayDeParametros['color'];
				$miVehiculo->marca=$ArrayDeParametros['marca'];

				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = $miVehiculo->GuardarVehiculo();
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

    public function BorrarUno($request, $response, $args) {
		$tokenAuth = $request->getHeader('Authorization');
		$tokenAuth = $tokenAuth[0];
		$newResponse = $response;
		 
		if ($_SESSION["user"] == $tokenAuth) {
			$ArrayDeParametros = $request->getParsedBody();
			$plate=$ArrayDeParametros['patente'];
			
			$vehiculo= new vehiculo();
			$vehiculo->patente=$plate;
			
			$cantidadDeBorrados=$vehiculo->BorrarVehiculo();

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

			if (!array_key_exists('patente', $ArrayDeParametros) or !array_key_exists('color', $ArrayDeParametros) or !array_key_exists('marca', $ArrayDeParametros)) {
				$newResponse = $newResponse->withAddedHeader('alertType', "warning");
				$rta = '<p>Ingrese todas las keys ("patente", "color" y "marca")</p>';
			} else {
				if ($ArrayDeParametros['patente']==null or $ArrayDeParametros['color']==null or $ArrayDeParametros['marca']==null) {
					$newResponse = $newResponse->withAddedHeader('alertType', "danger");
					$rta = '<p>ERROR!! Ingrese todos los datos ("patente", "color" y "marca")</p>';
				}else {
					$miVehiculo = new vehiculo();
					
					$miVehiculo->patente=$ArrayDeParametros['patente'];
					$miVehiculo->color=$ArrayDeParametros['color'];
					$miVehiculo->marca=$ArrayDeParametros['marca'];
					
					$newResponse = $newResponse->withAddedHeader('alertType', "success");
					$rta = $miVehiculo->GuardarVehiculo();
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



}