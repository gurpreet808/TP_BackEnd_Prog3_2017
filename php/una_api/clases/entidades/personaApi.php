<?php
require_once 'persona.php';
require_once 'IApiUsable.php';

class personaApi extends persona implements IApiUsable{
 	public function TraerUno($request, $response, $args) {
     	$mail=$args['mail'];
    	$lapersona=persona::TraerUnaPersona($mail);
		$newResponse = $response->withJson($lapersona, 200);
		//$newResponse = $newResponse->withAddedHeader('Token', 'unTokenCreado');

    	return $newResponse;
    }
    
    public function TraerTodos($request, $response, $args) {
      	$todasLaspersonas=persona::TraerTodasLasPersonas();
     	$newResponse = $response->withJson($todasLaspersonas, 200);  
    	//$newResponse = $newResponse->withAddedHeader('Token', 'unTokenCreado');

        return $newResponse;
    }
    
    public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;
        
		if (!array_key_exists('nombre', $ArrayDeParametros) 
        or !array_key_exists('mail', $ArrayDeParametros)
        or !array_key_exists('password', $ArrayDeParametros) 
        or !array_key_exists('sexo', $ArrayDeParametros)) {
			$rta = '<p>Ingrese todas las keys ("nombre", "mail", "password" y "sexo")</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null
            or $ArrayDeParametros['mail']==null
            or $ArrayDeParametros['password']==null
            or $ArrayDeParametros['sexo']==null) {
				$rta = '<p>ERROR!! Ingrese todos los datos ("nombre", "mail", "password" y "sexo")</p>';
			}else {
				$mipersona = new persona();
				
				$mipersona->nombre=$ArrayDeParametros['nombre'];
				$mipersona->mail=$ArrayDeParametros['mail'];
                $mipersona->setPassword($ArrayDeParametros['password']);
                $mipersona->sexo=$ArrayDeParametros['sexo'];
                
				$rta = $mipersona->GuardarPersona();
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

    public function BorrarUno($request, $response, $args) {
		$newResponse = $response;
		
		$ArrayDeParametros = $request->getParsedBody();
		$mail=$ArrayDeParametros['mail'];
			
		$cantidadDeBorrados = persona::BorrarPersonaPorMail($mail);

		if($cantidadDeBorrados>0){
			//$newResponse = $newResponse->withAddedHeader('alertType', "success");
			$rta = "Elementos borrados: ".$cantidadDeBorrados;
		}
		else {
			//$newResponse = $newResponse->withAddedHeader('alertType', "danger");
			$rta = "No se borró nada";	
		}

		$newResponse->getBody()->write($rta);
		return $newResponse;
    }
     
    public function ModificarUno($request, $response, $args) {
		$newResponse = $response;
		
		$ArrayDeParametros = $request->getParsedBody();
		$newResponse = $response;

		if (!array_key_exists('nombre', $ArrayDeParametros)
		or !array_key_exists('mail', $ArrayDeParametros) 
		or !array_key_exists('sexo', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys ("nombre", "mail" y "sexo")</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null
			or $ArrayDeParametros['mail']==null
			or $ArrayDeParametros['sexo']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos ("nombre", "mail" y "sexo")</p>';
			}else {
				$mipersona = persona::TraerUnaPersona($ArrayDeParametros['mail']);
				if(empty($mipersona)){
					$rta = "No se encontró el mail ingresado";
				} else {
					$mipersona->nombre=$ArrayDeParametros['nombre'];
					$mipersona->mail=$ArrayDeParametros['mail'];
					$mipersona->sexo=$ArrayDeParametros['sexo'];
					$guardo = true;

					if (array_key_exists('nuevomail', $ArrayDeParametros) && $ArrayDeParametros['nuevomail']!=null) {
						if(empty(persona::TraerUnaPersona($ArrayDeParametros['nuevomail']))){
							$mipersona->mail=$ArrayDeParametros['nuevomail'];
						} else {
							$rta = "Ya hay un persona con ese mail";
							$guardo = false;
						}
					}
					//$newResponse = $newResponse->withAddedHeader('alertType', "success");
					if ($guardo) {
						if ($mipersona->Modificarpersona()>0) {
							$rta = "persona modificada";
						} else {
							$rta = "No se modificó la persona";
						}
					}
				}					
			}	
		}	

		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

}
?>