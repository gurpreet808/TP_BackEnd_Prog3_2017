<?php

class Vehiculo{
    public $patente; //funciona como ID
    public $color;
    public $marca;

    public function ToJSON(){
        return json_encode($this);
    }
    
    public function BorrarVehiculo(){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//lo borra mediante el ID de la instancia que se creó
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM vehiculos WHERE patente=:id");
        $consulta->bindValue(':id',$this->patente, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->rowCount();
    }
    
    public function Modificarvehiculo(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //modifica enlazando parametros de la instncia
		$consulta =$objetoAccesoDato->RetornarConsulta("UPDATE vehiculos SET 
        color=:color, 
        marca=:marca
        WHERE patente=:id");

		$consulta->bindValue(':id',$this->patente, PDO::PARAM_STR);
		$consulta->bindValue(':color',$this->color, PDO::PARAM_STR);
		$consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);

		return $consulta->execute();
    }
    
    public function InsertarVehiculo(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //inserta enlazando parametros dela instancia
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO vehiculos (patente,color,marca)
        values(:patente,:color,:marca)");
        
        $consulta->bindValue(':patente',$this->patente, PDO::PARAM_STR);
		$consulta->bindValue(':color',$this->color, PDO::PARAM_STR);
		$consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
		$consulta->execute();

		return $objetoAccesoDato->RetornarUltimoidInsertado();
    }
    
    public function GuardarVehiculo(){
        if(empty(vehiculo::TraerUnVehiculo($this->patente))){
            $this->InsertarVehiculo();
            echo "vehiculo guardado";
        } else {
            if ($this->ModificarVehiculo()) {
                echo "vehiculo modificado";
            } else {
                echo "No modifico vehiculo";
            }
        }
    }
    
    public static function TraerTodosLosVehiculos(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT patente, color, marca FROM vehiculos");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "vehiculo");
    }
    
    public static function TraerUnVehiculo($plate){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT patente, color, marca FROM vehiculos where patente = :plate");
		$consulta->bindValue(':plate',$plate, PDO::PARAM_STR);
        $consulta->execute();
		$vehiculoBuscado= $consulta->fetchObject('vehiculo');
		return $vehiculoBuscado;
    }

    public static function BorrarVehiculoPorParametro($plate){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM vehiculos WHERE patente=:plate");	
		$consulta->bindValue(':plate',$plate, PDO::PARAM_STR);		
		$consulta->execute();
		return $consulta->rowCount();
    }

    public static function TraervehiculoJSON($plate){
        if(empty(vehiculo::TraerUnvehiculo($plate))){
            return "plate no registrado";
        } else {
            $unvehiculo = self::TraerUnvehiculo($plate);
            return $unvehiculo->ToJSON();
        }
    }
}
?>