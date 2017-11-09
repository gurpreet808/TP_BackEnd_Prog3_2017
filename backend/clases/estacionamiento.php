<?php
require_once("/cochera.php");
require_once("/vehiculo.php");

class Estacionamiento{
    public static $cocheras = array();
    public static $empleados = array();
    
    public static $cantReservadas = 3;
    public static $cantCocheras = 18;   
    
    public static function ingresarVehiculo($patente, $disabled){
        $unaCochera = new Cochera($patente, time());

        if ($disabled === true) {
            $lugar = rand(0,$cantReservadas-1);
        } else {
            $lugar = rand($cantReservadas,$cantCocheras-1);
        }

        $cocheras[$lugar] = $unaCochera;
    }

    public static function altaVehiculo($pat, $marc, $colr){
        $unVehiculo = new Vehiculo();
        $unVehiculo->patente = $pat;
        $unVehiculo->marca = $marc;
        $unVehiculo->color = $colr;
        $unVehiculo->GuardarVehiculo();
    }

    public static function guardarCocheras(){
    }

    public static function sacarVehiculo($pat){
        $unVehiculo = Vehiculo::TraerUnVehiculo($pat);
        $unaCochera = Cochera::TraerUnaCochera($pat);
        $tiempoAhora = time();
        if ($diferencia>12) {
            # code...
        } 
        if ($diferencia>24){
            # code...
        }
        if ($diferencia>24){
            # code...
        }
        
    }

}
?>