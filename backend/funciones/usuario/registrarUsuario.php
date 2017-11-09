<?php
require_once("../../clases/usuario.php");
require_once("../../clases/AccesoDatos.php");

if (isset($_POST["nombre"]) && isset($_POST["apellido"]) && isset($_POST["sexo"]) && isset($_POST["correo"]) && isset($_POST["clave"])) {
    $unUsuario = new Usuario();

    if (isset($_POST["nivel"])) {
        $unUsuario->nivel = $_POST["nivel"];
    } else {
        $unUsuario->nivel = -1;
    }
    
    $unUsuario->nombre = $_POST["nombre"]; 
    $unUsuario->apellido = $_POST["apellido"];
    $unUsuario->sexo = $_POST["sexo"]; 
    $unUsuario->correo = $_POST["correo"]; 
    $unUsuario->clave = $_POST["clave"];

    echo $unUsuario->GuardarUsuario();

} else {
    echo "ERROR no se recibieron parámetros";
}


?>