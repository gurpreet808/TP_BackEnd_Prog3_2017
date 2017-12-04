<?php

require_once "./clases/AutentificadorJWT.php";
class MWparaAutentificar
{
 /**
   * @api {any} /MWparaAutenticar/  Verificar Usuario
   * @apiVersion 0.1.0
   * @apiName VerificarUsuario
   * @apiGroup MIDDLEWARE
   * @apiDescription  Por medio de este MiddleWare verifico las credeciales antes de ingresar al correspondiente metodo 
   *
   * @apiParam {ServerRequestInterface} request  El objeto REQUEST.
   * @apiParam {ResponseInterface} response El objeto RESPONSE.
   * @apiParam {Callable} next  The next middleware callable.
   *
   * @apiExample Como usarlo:
   *    ->add(\MWparaAutenticar::class . ':VerificarUsuario')
   */
	public function VerificarUsuario($request, $response, $next) {
         
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";
	   	  
		//$response->getBody()->write('<p>verifico credenciales</p>');

		//tomo el token del header
		$arrayConToken = $request->getHeader('Authorization');
		
		var_dump($arrayConToken);

		$token = $arrayConToken[0];			
		
		//var_dump($token);
		$objDelaRespuesta->esValido=true; 
		try {		
			//var_dump(autentificadorJWT::decodificarToken($token));
			AutentificadorJWT::verificarToken($token);
			$objDelaRespuesta->esValido=true;      
		} catch (Exception $e) {      
			//guardar en un log
			$objDelaRespuesta->excepcion=$e->getMessage();
			$objDelaRespuesta->esValido=false;     
		}

		if($objDelaRespuesta->esValido) {
			echo "***TOKEN VALIDO***";						
			if($request->isPost()) {		
				// el post sirve para todos los logeados			    
				$response = $next($request, $response);
			}
			else {
				$payload = AutentificadorJWT::dataDelToken($token);
				var_dump($payload);
				// DELETE,PUT y DELETE sirve para todos los logeados y admin
				if($payload["perfil"]=="admin") {
					$response = $next($request, $response);
				}		           	
				else {	
					$objDelaRespuesta->respuesta="Solo administradores";
				}
			}		          
		}    
		else {
			//   $response->getBody()->write('<p>no tenes habilitado el ingreso</p>');
			$objDelaRespuesta->respuesta="Solo usuarios registrados";
			$objDelaRespuesta->elToken=$token;

		}  
		
		if($objDelaRespuesta->respuesta!="") {
			$nueva=$response->withJson($objDelaRespuesta, 401);  
			return $nueva;
		}
		  
		 //$response->getBody()->write('<p>vuelvo del verificador de credenciales</p>');
		 return $response;   
	}
}