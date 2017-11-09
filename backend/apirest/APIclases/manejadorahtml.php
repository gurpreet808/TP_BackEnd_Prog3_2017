<?php
require_once "../clases/Cochera.php";

class CocheraApi{
    public function BorrarUno($request, $response, $args) {
		$tokenAuth = $request->getHeader('Authorization');
		$tokenAuth = $tokenAuth[0];
		$newResponse = $response;
		 
		if ($_SESSION["user"] == $tokenAuth && $_SESSION["lvl"]==0) {
			$ArrayDeParametros = $request->getParsedBody();
			$id=$ArrayDeParametros['id'];
			
			$usuario= new Usuario();
			$usuario->id=$id;
			
			$cantidadDeBorrados=$usuario->BorrarUsuario();

			$objDelaRespuesta= new stdclass();
			//$objDelaRespuesta->cantidad=$cantidadDeBorrados;
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
}