<?php
require_once("../../clases/usuario.php");
require_once("../../clases/AccesoDatos.php");

if (isset($_POST["mail"]) && isset($_POST["clave"])) {
    $elUsuario = Usuario::TraerUnUsuario($_POST["mail"]);
    if ($elUsuario->clave === $_POST["clave"]) {
        session_start();
        $_SESSION["uN"] = $elUsuario->correo;
        $_SESSION["lvl"] = $elUsuario->nivel;
        echo "OK";
    } else {
        echo "NO";
    }
    
} else {
    echo "error no se recibieron parámetros";
}
 ?>