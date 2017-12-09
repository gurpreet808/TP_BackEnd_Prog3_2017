<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require './vendor/autoload.php';
require_once './clases/AccesoDatos.php';
require_once './clases/entidades/pizzaApi.php';
require_once './clases/entidades/empleadoApi.php';
require_once './clases/entidades/operacionApi.php';
require_once './clases/AutentificadorJWT.php';
require_once './clases/middlewares/MWparaCORS.php';
require_once './clases/middlewares/MWparaAutentificar.php';

date_default_timezone_set('America/Argentina/Buenos_Aires');

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App([
  "settings" => $config
]);

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $res, $next) {
  $response = $next($req, $res);
  return $response
          ->withHeader('Access-Control-Allow-Origin', '*')
          ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
          ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

/*Empleado*/
$app->group('/empleado', function () {

  //Públicos
  $this->post('/login', \empleadoApi::class . ':LogIn');
  
  //Logueados
  $this->get('/', \empleadoApi::class . ':traerTodos')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  
  $this->get('/{mail}', \empleadoApi::class . ':traerUno')->add(\MWparaAutentificar::class . ':VerificarUsuario');

  $this->post('/modificarme', \empleadoApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarUsuario');

  //Administradores
  $this->post('/logueos/{mail}', \empleadoApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin');  

  $this->post('/operaciones/{mail}', \empleadoApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin');    

  $this->post('/', \empleadoApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin');

  $this->delete('/', \empleadoApi::class . ':BorrarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin');
 
  $this->put('/', \empleadoApi::class . ':ModificarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin');
});

/*Operaciones*/
$app->group('/vehiculo', function () {
  
  $this->get('/cocheras', \operacionApi::class . ':BuscarCocherasLibres');

  //Logueados
  $this->get('/', \operacionApi::class . ':TraerTodos')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  
  $this->get('/{patente}', \operacionApi::class . ':OperacionesVehiculo')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  
  //$this->post('/modificar', \operacionApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  
  $this->post('/estacionar', \operacionApi::class . ':CargarUno')->add(\MWparaAutentificar::class . ':VerificarUsuario');

  $this->post('/sacar', \operacionApi::class . ':SacarUno')->add(\MWparaAutentificar::class . ':VerificarUsuario');
  
  //Administradores
  $this->delete('/', \operacionApi::class . ':BorrarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin');
  
  $this->put('/', \operacionApi::class . ':ModificarUno')->add(\MWparaAutentificar::class . ':VerificarAdmin');
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