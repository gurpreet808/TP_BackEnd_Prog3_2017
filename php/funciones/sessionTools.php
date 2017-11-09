<?php
require_once("../clases/usuario.php");
require_once("../clases/AccesoDatos.php");

session_start();

if(isset($_POST['opUser'])){
    $opcionU = $_POST['opUser'];
    if (isset($_SESSION['lvl'])) {
            if ($_SESSION['lvl']==0) {
                switch ($opcionU) {
                    //opciones del administrador
                    case 'admin':
                        echo("Solo admin");
                        break;
                    
                    case 'usuarios':
                        echo Usuario::TraerTodosLosUsuariosJSON();
                        break;
                    
                    default:
                        # code...
                        break;
                }                
            }
        switch ($opcionU) {
            case 'sessionData':
                //Hay que filtrar datos sencibles como la pass
                //echo "Datos de la sesion";
                var_dump($_SESSION);
                break;
                        
            default:
                # code...
                break;
        }
    }
    //opciones públicas
    switch ($opcionU) {
        case 'consultaSesion':
            if(isset($_SESSION['user'])){
                echo "HaySesion";
            } else {
                echo "SinSesion";
            }
            break;
    }
}
?>