<?php
class AccesoDatos{
    private static $ObjetoAccesoDatos;
    private $objetoPDO;
 
    public $host = 'localhost';
    public $dbname = 'u779441249_park';
    //Hostinger
    public $user = "u779441249_admin";
    public $pass = "estacionamiento.2017";

    //Localhost
    /*
    public $user = "u779441249_admin";
    public $pass = "estacionamiento.2017";
    */

    private function __construct(){
        try {
            $this->objetoPDO = new PDO('mysql:host='.$this->host.';dbname='.$this->dbname.';charset=utf8', $this->user, $this->pass, array(PDO::ATTR_EMULATE_PREPARES => false,PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $this->objetoPDO->exec("SET CHARACTER SET utf8");
        } catch (PDOException $e) { 
            print "Error!: " . $e->getMessage(); 
            die();
        }
    }
 
    public function RetornarConsulta($sql){ 
        return $this->objetoPDO->prepare($sql); 
    }
    
    public function RetornarUltimoIdInsertado(){ 
        return $this->objetoPDO->lastInsertId(); 
    }
 
    public static function dameUnObjetoAcceso(){ 
        if (!isset(self::$ObjetoAccesoDatos)) {          
            self::$ObjetoAccesoDatos = new AccesoDatos(); 
        } 
        return self::$ObjetoAccesoDatos;        
    }
 
 
     // Evita que el objeto se pueda clonar
    public function __clone()
    { 
        trigger_error('La clonación de este objeto no está permitida', E_USER_ERROR); 
    }
}
?>