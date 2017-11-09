<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require ('./vendor/autoload.php');
require ('../clases/AccesoDatos.php');
require ('./APIclases/usuarioApi.php');
require ('./APIclases/vehiculoApi.php');
require ('./APIclases/cocheraApi.php');

$config['displayErrorDetails'] = true; //habilita detalles sobre errores
$config['addContentLengthHeader'] = false; //habilita que el servidor web establezca el Content-Lenght header

$app = new \Slim\App(["settings" => $config]);

/*LLAMADA A METODOS DE INSTANCIA DE UNA CLASE*/
$app->get('/hola', function (Request $request, Response $response) {
    $response->getBody()->write("hola");

    return $response;
});

/*Clase Usuario*/
$app->group('/usuario', function () {
 
  $this->get('/', \UsuarioApi::class . ':traerTodos');
 
  $this->get('/{mail}', \UsuarioApi::class . ':traerUno');

  $this->post('/', \UsuarioApi::class . ':CargarUno');

  $this->delete('/', \UsuarioApi::class . ':BorrarUno');

  $this->put('/', \UsuarioApi::class . ':ModificarUno');

  $this->patch('/', \UsuarioApi::class . ':MiPerfil');

  $this->post('/logIn', \UsuarioApi::class . ':LogIn');

  $this->post('/logOut', \UsuarioApi::class . ':LogOut');
  
});

/*Clase Vehiculo*/
$app->group('/vehiculo', function () {
 
  $this->get('/', \VehiculoApi::class . ':traerTodos');
 
  $this->get('/{patente}', \VehiculoApi::class . ':traerUno');

  $this->post('/', \VehiculoApi::class . ':CargarUno');

  $this->delete('/', \VehiculoApi::class . ':BorrarUno');

  $this->put('/', \VehiculoApi::class . ':ModificarUno');
  
});

/*Clase Cochera*/
$app->group('/cochera', function () {
 
  $this->get('/', \CocheraApi::class . ':traerTodos');
 
  $this->get('/{id}', \CocheraApi::class . ':traerUno');

  $this->post('/', \CocheraApi::class . ':CargarUno');

  $this->post('/estacionar', \CocheraApi::class . ':EstacionarEnCochera');

  $this->delete('/', \CocheraApi::class . ':BorrarUno');

  $this->put('/', \CocheraApi::class . ':ModificarUno');
  
});

/*Partes HTML*/
$app->group('/html', function () {
 
  $this->get('/', \CocheraApi::class . ':traerTodos');
 
  $this->get('/{id}', \CocheraApi::class . ':traerUno');

  $this->post('/', \CocheraApi::class . ':CargarUno');

  $this->post('/estacionar', \CocheraApi::class . ':EstacionarEnCochera');

  $this->delete('/', \CocheraApi::class . ':BorrarUno');

  $this->put('/', \CocheraApi::class . ':ModificarUno');
  
});

$app->run();