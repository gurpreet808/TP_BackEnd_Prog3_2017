<?php
require_once "./clases/entidades/cochera.php";
require_once "./clases/AutentificadorJWT.php";
require_once ('./clases/entidades/IApiUsable.php');
//var_dump(scandir("./clases")); //para ver donde estoy parado
//session_start();

class cocheraApi extends cochera implements IApiUsable{

	public function CheckBBDD($request, $response, $next) {
		$newResponse = $response;

		try {
			cochera::TraerTodosLoscocheras();
			$newResponse = $next($request, $response);
			
		} catch (Exception $e) {
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$consulta =$objetoAccesoDato->RetornarConsulta("CREATE TABLE `cocheras` (
				`id_cochera` INT NOT NULL AUTO_INCREMENT, 
				`nombre` VARCHAR(2) NOT NULL, 
				`especial` VARCHAR(45), 
				PRIMARY KEY (`id_cochera`)
			)");
			
			$newResponse->getBody()->write($consulta->execute());
		}
		return $newResponse;
	}
	
	public function LlenarBBDD(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO cocheras (nombre,especial)
	 		values
			 ('1A','si'), ('1B','si'), ('1C','si'), ('1D',NULL), ('1E',NULL), ('1F',NULL), ('1G',NULL), 
			 ('2A',NULL), ('2B',NULL), ('2C',NULL), ('2D',NULL), ('2E',NULL), ('2F',NULL), ('2G',NULL),
			 ('3A',NULL), ('3B',NULL), ('3C',NULL), ('3D',NULL), ('3E',NULL), ('3F',NULL), ('3G',NULL)");
	
		return $consulta->execute();
	}

 	public function TraerUno($request, $response, $args) {
     	$id = $args['id'];
		$laCochera = cochera::TraerUnaCochera($id);
		
		$newResponse = $response;
		
		if (!$laCochera) {
			return $newResponse->getBody()->write('<p>ERROR!! No se encontró esa cochera.</p>');			
		}	

    	return $newResponse->withJson($laCochera, 200);
	}
	
    public function TraerTodos($request, $response, $args) {
      	$todasLascocheras = cochera::TraerTodasLasCocheras();
     	$response = $response->withJson($todasLascocheras, 200);  
		
		 return $response;
	}
	
    public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;

		if ($ArrayDeParametros == null
		or !array_key_exists('nombre', $ArrayDeParametros) 
		or !array_key_exists('especial', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys (
				"nombre" y 
				"especial")</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null 
			or $ArrayDeParametros['especial']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos (
					"nombre" y 
					"especial")</p>';
			}else {

				if (array_key_exists(2, str_split($ArrayDeParametros['nombre']))) {
					return $newResponse->getBody()->write('<p>ERROR!! Sólo puede ingresar 2 caracteres, uno para el piso y otro para la sección.</p>');
				}

				$micochera = new cochera();
				
				$micochera->nombre=$ArrayDeParametros['nombre'];
				$micochera->especial=$ArrayDeParametros['especial'];
				
				if ($micochera->InsertarCochera()>0) {
					$newResponse = $newResponse->withAddedHeader('alertType', "success");
					$rta = "Se guardó la cochera";
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

		if(empty(cochera::TraerUnaCochera($id))){
			$newResponse = $newResponse->withAddedHeader('alertType', "danger");
			$rta = "No se encontró esa cochera";
		} else {
			
			$cantidadDeBorrados = cochera::BorrarCochera($id);

			if($cantidadDeBorrados>0) {
				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = "Elementos borrados: ".$cantidadDeBorrados;
			} else {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = "No se pudo borrar la cochera";	
			}
		}

		$newResponse->getBody()->write($rta);

		return $newResponse;
    }
     
    public function ModificarUno($request, $response, $args) {
		$newResponse = $response;
		
		$ArrayDeParametros = $request->getParsedBody();

		if ($ArrayDeParametros == null
		or !array_key_exists('id', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese debe ingresar al menos la key "id"</p>';
		} else {
			if ($ArrayDeParametros['id']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Complete el campo de la key "id"</p>';
			}else {

				$micochera = cochera::TraerUnaCochera($ArrayDeParametros['id']);

				$array_nombre = self::comprobar_key("nombre", $ArrayDeParametros);
				if ($array_nombre["esValido"]) {
					$micochera->nombre=$ArrayDeParametros['nombre'];
				} elseif (array_key_exists('msg', $array_nombre)) {
					return $newResponse->getBody()->write($array_nombre["msg"]);
				}

				$array_especial = self::comprobar_key("especial", $ArrayDeParametros);
				if ($array_especial["esValido"]) {
					$micochera->especial=$ArrayDeParametros['especial'];
				} elseif (array_key_exists('msg', $array_especial)) {
					return $newResponse->getBody()->write($array_especial["msg"]);
				}

				/*
				$micochera->nombre=$ArrayDeParametros['nombre'];
				$micochera->especial=$ArrayDeParametros['especial'];
				*/

				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				if ($micochera->Modificarcochera()>0) {
					$rta = "cochera modificada";
					$newResponse = $newResponse->withAddedHeader('alertType', "success");
				} else {
					$rta = "No se modificó la cochera";
				}				
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;	
	}
	
	public static function comprobar_key($tag, $unArray){
        $rta_array = array();
        $rta_array["esValido"] = false;
        
        if (array_key_exists($tag, $unArray)) {
			if ($unArray[$tag]==null) {
                $rta_array["msg"] = '<p>ERROR!! Complete el campo de la key "'.$tag.'" </p>';
			} else {
                $rta_array["esValido"] = true;
            }
        }

        return $rta_array;
    }
}
?>