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
	public static function VerificarToken($request) {
         
		$objDelaRespuesta= new stdclass();
		$objDelaRespuesta->respuesta="";

		//tomo el token del header
		$arrayConToken = $request->getHeader('Authorization');
		//var_dump($arrayConToken);
		
		$token = "";

		if (!empty($arrayConToken)) {
			$token = $arrayConToken[0];			
		}
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
			//echo "***TOKEN VALIDO***";
			$objDelaRespuesta->payload = AutentificadorJWT::dataDelToken($token);
			//var_dump($objDelaRespuesta->payload);
			
			//Deshabilita cualquier cosa que quiera hacer un SUSPENDIDO
			if ($objDelaRespuesta->payload["perfil"]==="suspendido") {
				$objDelaRespuesta->respuesta = "Usted está suspendido";
				$objDelaRespuesta->esValido = false;
				unset($objDelaRespuesta->payload);
			}									
				          
		} else {
			//ACA ENTRO EL TOKEN EXPIRADO
			if ($objDelaRespuesta->excepcion == "Token no valido --Expired token") {
				$objDelaRespuesta->respuesta="Su sesión expiró, debe loguearse nuevamente";
			} else {
				$objDelaRespuesta->respuesta="Solo usuarios registrados";
			}
			
			$objDelaRespuesta->elToken=$token;
		}

		return $objDelaRespuesta;   
	}

	public function VerificarUsuario($request, $response, $next) {
		$objDelaRespuesta = self::VerificarToken($request);

		if ($objDelaRespuesta == null or !array_key_exists('payload',$objDelaRespuesta)) {
			$newResponse = $response->withJson($objDelaRespuesta, 401);
		} else {
			$newResponse = $next($request, $response);
			//var_dump($objDelaRespuesta->payload);
			$token = AutentificadorJWT::refrescarToken($objDelaRespuesta->payload);
			//var_dump($token);
			$newResponse = $newResponse->withAddedHeader('token', $token);
		}		

		return $newResponse;
	}
	
	public function VerificarAdmin($request, $response, $next) {
		$objDelaRespuesta = self::VerificarToken($request);

		$newResponse = $response;
	
		if ($objDelaRespuesta == null or array_key_exists('payload',$objDelaRespuesta)) {
			if ($objDelaRespuesta->payload["perfil"]!="administrador") {
				$objDelaRespuesta->esValido = false;
				$objDelaRespuesta->respuesta = "Solo administradores";

				$token = AutentificadorJWT::refrescarToken($objDelaRespuesta->payload);
				//var_dump($token);
				$newResponse = $newResponse->withAddedHeader('token', $token);
				unset($objDelaRespuesta->payload);
			} else {
				$newResponse = $next($request, $response);
				//var_dump($objDelaRespuesta->payload);
				$token = AutentificadorJWT::refrescarToken($objDelaRespuesta->payload);
				//var_dump($token);
				$newResponse = $newResponse->withAddedHeader('token', $token);
			}
		} 
		
		if (!$objDelaRespuesta->esValido) {
			$newResponse = $newResponse->withJson($objDelaRespuesta, 401);
		}

		return $newResponse;
	}
}