<?php
require_once("../../clases/Vehiculo.php");
require_once("../../clases/AccesoDatos.php");

if (isset($_POST["patente"]) && isset($_POST["colorVehiculo"]) && isset($_POST["marca"]) && isset($_POST["discapacitado"])) {
    
    if ($_POST["discapacitado"] == "SI") {
        //guardar en cochera discap
    } else {
        # code...
    }
        $unVehiculo = new Vehiculo();
        
        $unVehiculo->patente = $_POST["patente"]; 
        $unVehiculo->color = $_POST["colorVehiculo"];
        $unVehiculo->marca = $_POST["marca"];
        
        echo $unVehiculo->GuardarVehiculo();
    
    

} else {
    echo "ERROR no se recibieron parámetros";
}
?>