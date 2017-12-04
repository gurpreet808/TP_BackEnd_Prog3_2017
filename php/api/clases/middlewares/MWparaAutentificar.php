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
			//echo "***TOKEN VALIDO***";						
			$objDelaRespuesta->payload = AutentificadorJWT::dataDelToken($token);
			//var_dump($objDelaRespuesta->payload);
				          
		} else {
			//   $response->getBody()->write('<p>no tenes habilitado el ingreso</p>');

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
			$nueva=$response->withJson($objDelaRespuesta, 401);
			return $nueva;
		} else {
			$response = $next($request, $response);
		}		

		return $response;
	}
	
	public function VerificarAdmin($request, $response, $next) {
		$objDelaRespuesta = self::VerificarToken($request);
	
		if ($objDelaRespuesta == null or array_key_exists('payload',$objDelaRespuesta)) {
			if ($objDelaRespuesta->payload["perfil"]!="admin") {
				$objDelaRespuesta->esValido = false;
				$objDelaRespuesta->respuesta = "Solo administradores";
			} else {
				return $response = $next($request, $response);
			}
		} 
		
		if (!$objDelaRespuesta->esValido) {
			$nueva = $response->withJson($objDelaRespuesta, 401);
			return $nueva;
		}
	}
}