<?php
require_once("../../clases/usuario.php");
require_once("../../clases/AccesoDatos.php");

if (isset($_POST['correo'])) {
    $unUsuario = Usuario::TraerUnUsuario($_POST["correo"]);
    if(empty($unUsuario)){
        $disponible = true;
    } else {
        //encontró un usuario con ese mail
        if (isset($_GET['UserNum'])) {

            if ($_GET['UserNum'] == $unUsuario->id ){
                //Mail con mismo ID, es decir que se quiere modificar el mismo
                $disponible = true;
            } else {
                //Mail con mismo ID, es decir que otra persona tiene ese mail
                $disponible = false;
            }            
        } else {
            $disponible = false;
        }    
    }
    echo json_encode(array(
        'valid' => $disponible,
        ));
}
?>