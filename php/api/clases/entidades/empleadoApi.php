<?php
require_once "../clases/usuario.php";
require_once "../clases/autentificadorjwt.php";
require_once ('./APIclases/IApiUsable.php');
session_start();

class usuarioApi extends Usuario implements IApiUsable{

	public function CheckBBDD($request, $response, $next) {
		$newResponse = $response;

		try {
			Empleado::TraerTodasLosEmpleados();
			$newResponse = $next($request, $response);
			
		} catch (Exception $e) {
			$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
			$consulta =$objetoAccesoDato->RetornarConsulta("CREATE TABLE `empleados` (
				`id` INT NOT NULL AUTO_INCREMENT, 
				`nombre` VARCHAR(45) NOT NULL, 
				`apellido` VARCHAR(45) NOT NULL, 
				`clave` LONGTEXT NOT NULL,
				`mail` VARCHAR(45) NOT NULL UNIQUE,
				`turno` VARCHAR(45) NOT NULL,
				`perfil` VARCHAR(45) NOT NULL,
				`fecha_creacion` VARCHAR(45) NOT NULL,
				`foto` VARCHAR(45), 
				PRIMARY KEY (`id`)
			)");
			
			$newResponse->getBody()->write($consulta->execute());
		}
		return $newResponse;
	}
	
	public function LlenarBBDD(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO empleados (nombre,apellido,clave,mail,turno,perfil,fecha_creacion,foto)
	 		values
			 ('Administrador','Administrator','admin','admin@admin.com','mañana','admin','15/11/16'), 
			 ('Usuario','User','user','user@user.com','tarde','user','24/12/16')");
	
		return $consulta->execute();
	}
	
	public function TraerUno($request, $response, $args) {
		$id=$args['id'];		
		$laempleado=empleado::TraerUnaempleado($id);

		$newResponse = $response;
		
		if (!$laempleado) {
			return $newResponse->getBody()->write('<p>ERROR!! No se encontró esa empleado.</p>');			
		}	

    	return $newResponse->withJson($laempleado, 200);
    }

 	public function TraerUno($request, $response, $args) {
     	$mail=$args['mail'];
    	$elUsuario=Usuario::TraerUnUsuario($mail);
		$newResponse = $response->withJson($elUsuario, 200);
		$newResponse = $newResponse->withAddedHeader('Token', 'unTokenCreado');

    	return $newResponse;
    }
    public function TraerTodos($request, $response, $args) {
      	$todosLosUsuarios=Usuario::TraerTodosLosUsuarios();
     	$response = $response->withJson($todosLosUsuarios, 200);  
    	return $response;
    }
    public function CargarUno($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);

		$newResponse = $response;

		if (!array_key_exists('nombre', $ArrayDeParametros) or !array_key_exists('apellido', $ArrayDeParametros) or !array_key_exists('sexo', $ArrayDeParametros) or !array_key_exists('correo', $ArrayDeParametros) or !array_key_exists('clave', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys ("nombre", "apellido", "sexo", "correo" y "clave")</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null or $ArrayDeParametros['apellido']==null or $ArrayDeParametros['sexo']==null or $ArrayDeParametros['correo']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos ("nombre", "apellido", "sexo", "correo" y "clave")</p>';
			}else {
				$miUsuario = new Usuario();
				
				$miUsuario->nombre=$ArrayDeParametros['nombre'];
				$miUsuario->apellido=$ArrayDeParametros['apellido'];
				$miUsuario->sexo=$ArrayDeParametros['sexo'];
				$miUsuario->correo=$ArrayDeParametros['correo'];
				
				$miUsuario->setClave($ArrayDeParametros['clave']);
				$miUsuario->nivel=-4;
				
				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = $miUsuario->GuardarUsuario();
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

	public function MiPerfil($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
		$tokenAuth = $request->getHeader('Authorization');
		$tokenAuth = $tokenAuth[0];

		autentificadorJWT::dataDelToken($tokenAuth);

		$newResponse = $response;

		if (!array_key_exists('nombre', $ArrayDeParametros) or !array_key_exists('apellido', $ArrayDeParametros) or !array_key_exists('sexo', $ArrayDeParametros) or !array_key_exists('correo', $ArrayDeParametros) or !array_key_exists('clave', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys ("nombre", "apellido", "sexo", "correo" y "clave")</p>';
		} else {
			if ($ArrayDeParametros['nombre']==null or $ArrayDeParametros['apellido']==null or $ArrayDeParametros['sexo']==null or $ArrayDeParametros['correo']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos ("nombre", "apellido", "sexo", "correo" y "clave")</p>';
			}else {
				$miUsuario = Usuario::TraerUnUsuario();
				
				$miUsuario->nombre=$ArrayDeParametros['nombre'];
				$miUsuario->apellido=$ArrayDeParametros['apellido'];
				$miUsuario->sexo=$ArrayDeParametros['sexo'];
				$miUsuario->correo=$ArrayDeParametros['correo'];
				
				$miUsuario->setClave($ArrayDeParametros['clave']);
				$miUsuario->nivel=-4;
				
				$newResponse = $newResponse->withAddedHeader('alertType', "success");
				$rta = $miUsuario->GuardarUsuario();
			}	
		}
		$newResponse->getBody()->write($rta);

        return $newResponse;
    }

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

			if (!array_key_exists('nombre', $ArrayDeParametros) or !array_key_exists('apellido', $ArrayDeParametros) or !array_key_exists('sexo', $ArrayDeParametros) or !array_key_exists('correo', $ArrayDeParametros) or !array_key_exists('nivel', $ArrayDeParametros)) {
				$newResponse = $newResponse->withAddedHeader('alertType', "warning");
				$rta = '<p>Ingrese todas las keys ("nombre", "apellido", "sexo", "correo" y "nivel")</p>';
			} else {
				if ($ArrayDeParametros['nombre']==null or $ArrayDeParametros['apellido']==null or $ArrayDeParametros['sexo']==null or $ArrayDeParametros['correo']==null) {
					$newResponse = $newResponse->withAddedHeader('alertType', "danger");
					$rta = '<p>ERROR!! Ingrese todos los datos ("nombre", "apellido", "sexo", "correo" y "nivel")</p>';
				}else {
					$idUsuario = $request->getHeader('UserNum');
					$idUsuario = $idUsuario[0];
					
					$miUsuario = Usuario::TraerUnUsuarioPorId($idUsuario);
					
					$miUsuario->nombre=$ArrayDeParametros['nombre'];
					$miUsuario->apellido=$ArrayDeParametros['apellido'];
					$miUsuario->sexo=$ArrayDeParametros['sexo'];
					$miUsuario->correo=$ArrayDeParametros['correo'];
					$miUsuario->nivel=$ArrayDeParametros['nivel'];
					
					$newResponse = $newResponse->withAddedHeader('alertType', "success");
					if ($miUsuario->ModificarUsuario()>0) {
						$rta = "Usuario modificado";
					} else {
						$rta = "No se modificó el usuario";
					}
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

	public function LogIn($request, $response, $args) {
		$ArrayDeParametros = $request->getParsedBody();
        //var_dump($ArrayDeParametros);
		$newResponse = $response;

		if (!array_key_exists('correo', $ArrayDeParametros) or !array_key_exists('clave', $ArrayDeParametros)) {
			$newResponse = $newResponse->withAddedHeader('alertType', "warning");
			$rta = '<p>Ingrese todas las keys ("correo" y "clave")</p>';
		} else {
			if ($ArrayDeParametros['correo']==null or $ArrayDeParametros['clave']==null) {
				$newResponse = $newResponse->withAddedHeader('alertType', "danger");
				$rta = '<p>ERROR!! Ingrese todos los datos ("correo" y "clave")</p>';
			}else {
				$correo= $ArrayDeParametros['correo'];
				$clave= $ArrayDeParametros['clave'];


				switch (Usuario::VerificarClave($correo,$clave)) {
					case true:
						$unUsuario = Usuario::TraerUnUsuario($correo);
        				
						$_SESSION["user"] = $correo;
        				$_SESSION["lvl"] = $unUsuario->nivel;

						
						//Datos para el token
						$datosUsuario = array(
							'nombre' => $unUsuario->nombre,
							'correo' => $unUsuario->correo,
							'nivel' => $unUsuario->nivel,
							'sexo' => $unUsuario->sexo
						);

						$token = autentificadorJWT::crearJWT($datosUsuario);
						$newResponse = $newResponse->withAddedHeader('token', $token);
						
						$newResponse = $newResponse->withAddedHeader('datos', json_encode($datosUsuario));

						$newResponse = $newResponse->withAddedHeader('alertType', "success");
						$rta = "<strong>¡Bien!</strong> Usuario (e-mail) y clave válidos";
						
						break;
					
					case false:
						$newResponse = $newResponse->withAddedHeader('alertType', "danger");
						$rta = "<strong>ERROR!</strong> Usuario y clave inválidos";
						break;

					case 'NOMAIL':
						$newResponse = $newResponse->withAddedHeader('alertType', "warning");
						$rta = "No se encuentra el mail que ingresó";
						break;
					
					default:
						# code...
						break;
				}
			}	
		}
		$newResponse->getBody()->write($rta);
		
		return $newResponse;
    }

	public function LogOut($request, $response, $args) {
		$_SESSION["user"] = null;
        $_SESSION["lvl"] = null;
		session_unset();
        session_destroy();
		
		return $response->getBody()->write("Deslogueo Correcto");
    }


}
?>