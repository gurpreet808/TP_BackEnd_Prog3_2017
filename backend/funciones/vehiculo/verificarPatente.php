<?php
require_once("../../clases/Vehiculo.php");
require_once("../../clases/AccesoDatos.php");

if (isset($_POST['patente'])) {
    if(empty(Vehiculo::TraerUnVehiculo($_POST["patente"]))){
        $disponible = true;
    } else {
        $disponible = false;
    }
    echo json_encode(array(
        'valid' => $disponible,
        ));
}

?>