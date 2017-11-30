<?php
require_once "../clases/cochera.php";
require_once ('./APIclases/IApiUsable.php');

class CocheraApi extends Cochera implements IApiUsable
{
 	public function TraerUno($request, $response, $args) {
     	$piso=$args['piso'];
    	$laCochera=Cochera::TraerUnCochera($piso);
     	//$newResponse = $response->withJson($elCd, 200);  
        $response->getBody()->write("<h1>TraerUno</h1>");

    	return $response;
    }
    public function TraerTodos($request, $response, $args) {
      	$todasLasCocheras=Cochera::TraerTodosLasCocheras();
     	$response = $response->withJson($todasLasCocheras, 200);  
    	return $response;
    }
    public function CargarUno($request, $response, $args) {
     	$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$piso= $ArrayDeParametros['piso'];
        
        $miCochera = new Cochera();
        $miCochera->piso=$piso;
        
		$rdo = $miCochera->InsertarCochera();

		$response->getBody()->write($rdo);

        return $response;
    }
	public function EstacionarEnCochera($request, $response, $args) {
     	$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$piso= $ArrayDeParametros['piso'];
		$patente= $ArrayDeParametros['patente'];
        
		$rdo = Cochera::CargarVehiculoEnCochera($piso, $patente);

		$response->getBody()->write($rdo);

        return $response;
    }
    public function BorrarUno($request, $response, $args) {
     	$ArrayDeParametros = $request->getParsedBody();
     	$piso=$ArrayDeParametros['piso'];
     	
		$Cochera= new Cochera();
     	$Cochera->piso=$piso;
		
     	$cantidadDeBorrados=$Cochera->BorrarCochera();

     	$objDelaRespuesta= new stdclass();
	    $objDelaRespuesta->cantidad=$cantidadDeBorrados;
	    if($cantidadDeBorrados>0)
	    	{
	    		 $objDelaRespuesta->resultado="algo borro!!!";
	    	}
	    	else
	    	{
	    		$objDelaRespuesta->resultado="no Borro nada!!!";
	    	}
	    $newResponse = $response->withJson($objDelaRespuesta, 200);  
      	return $newResponse;
    }
     
    public function ModificarUno($request, $response, $args) {
     	//$response->getBody()->write("<h1>Modificar  uno</h1>");
     	$ArrayDeParametros = $request->getParsedBody();
	    //var_dump($ArrayDeParametros);    	

        $miCochera = new Cochera();
        $miCochera->piso=$ArrayDeParametros['piso'];
        $miCochera->patente=$ArrayDeParametros['patente'];
		$miCochera->contUsos=$ArrayDeParametros['contUsos'];

	   	$resultado =$miCochera->ModificarCochera();
	   	$objDelaRespuesta= new stdclass();
		//var_dump($resultado);
		$objDelaRespuesta->resultado=$resultado;
		return $response->withJson($objDelaRespuesta, 200);		
    }


}