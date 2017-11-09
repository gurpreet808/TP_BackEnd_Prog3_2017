<?php
session_start();

    if(isset($_POST['opMenu'])){
        $opcion = $_POST['opMenu'];

        if (isset($_SESSION['lvl'])) {

            if ($_SESSION['lvl']>=0){
                //opciones con session
                switch ($opcion) {
                    case 'addVehiculo':
                        include("../partes/forms/frmVehiculo.html");
                        break; 
                    
                    case 'quitarVehiculo':
                        //include("../partes/forms/frmVehiculo.html");
                        break; 
                    
                    case 'listVehiculo':
                        //include("../partes/forms/frmVehiculo.html");
                        break; 
                    
                    case 'listCochera':
                        //include("../partes/navBar.html");
                        break;

                    case 'miPerfil':
                        include("../partes/forms/frmMiPerfil.html");
                        break;       

                    case 'cargarNav':
                        include("../partes/navBar.html");
                        break;           

                    default:
                        # code...
                        break;
                }
            }

            if ($_SESSION['lvl']==0) {
                switch ($opcion) {
                    //opciones del administrador
                    case 'addEmpleado':
                        echo("Solo admin");
                        break;

                    case 'listEmpleado':
                        include("../partes/tablas/tablaUsuario.html");
                        break;
                    
                    case 'adminOptions':
                        include("../partes/navOptions/optionsEmpleados.html");
                        break;
                    
                    case 'modUsuario':
                        include("../partes/forms/frmModUsuario.html");
                        break;
                    
                    default:
                        # code...
                        break;
                }                
            }
        }
        //opciones públicas
        switch ($opcion) {            
            case 'logIn':
                include("../partes/forms/frmLogIn.html");
                break;    

            default:
                # code...
                break;
        }
    }    
 ?>