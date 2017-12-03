<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require './vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/entidades/pizzaApi.php';
require_once './clases/entidades/empleadoApi.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/middlewares/MWparaCORS.php';
require_once './clases/middlewares/MWparaAutentificar.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App([
  "settings" => $config
]);

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
})->add(\MWparaCORS::class . ':HabilitarCORSTodos');

/*empleado*/
$app->group('/empleado', function () {
  
   $this->get('/', \empleadoApi::class . ':traerTodos')->add(\MWparaCORS::class . ':HabilitarCORSTodos');
  
   $this->get('/{username}', \empleadoApi::class . ':traerUno')->add(\MWparaCORS::class . ':HabilitarCORSTodos');
 
   $this->post('/', \empleadoApi::class . ':CargarUno');
 
   $this->delete('/', \empleadoApi::class . ':BorrarUno');
 
   $this->put('/', \empleadoApi::class . ':ModificarUno');
 
 });

/*Pizza*/
$app->group('/pizza', function () {
  
   $this->get('/', \pizzaApi::class . ':traerTodos');
  
   $this->get('/{id}', \pizzaApi::class . ':traerUno');
 
   $this->post('/', \pizzaApi::class . ':CargarUno');
 
   $this->delete('/', \pizzaApi::class . ':BorrarUno');

   $this->delete('/{id}', \pizzaApi::class . ':Borrar');
 
   $this->put('/{id}', \pizzaApi::class . ':ModificarUno');
   
})->add(\MWparaCORS::class . ':HabilitarCORSTodos');

$app->get('/hola', function (Request $request, Response $response) {
    $response->getBody()->write("hola");

    return $response;
});


$app->run();