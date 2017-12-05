<?php

class Operacion{
    public $patente = null;
    public $color = null;
    public $foto = null;// Agregar path de foto por defecto   
    public $id_empileado_ingreso = null;
    public $fecha_hora_ingreso = null;
    public $id_empileado_salida = null;
    public $fecha_hora_salida = null;
    public $tiempo = null;
    public $importe = null;

    public function ToJSON(){
        //Se puede poner en private los atributos que no quiero que salgan en el JSON
        return json_encode($this);
    }

    public static function TraerTodasLasOperacionesJSON(){
        $Operaciones = self::TraerTodasLasOperaciones();

        $stringArrayOperaciones = "[";
        for ($i=0; $i < count($Operaciones); $i++) { 
            $stringArrayOperaciones = $stringArrayOperaciones.$Operaciones[$i]->toJSON().",";
        }
        $stringArrayOperaciones = substr($stringArrayOperaciones,0,-1) ."]";

        return $stringArrayOperaciones;
    }
    
    public function BorrarOperacion(){
	 	$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		//lo borra mediante el ID de la instancia que se creó
        $consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM Operaciones WHERE patente=:patente AND fecha_hora_ingreso=:fecha_hora_ingreso");
        $consulta->bindValue(':patente',$this->patente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_ingreso',$this->fecha_hora_ingreso, PDO::PARAM_STR);
        $consulta->execute();
        
        return $consulta->rowCount();
    }
    
    /*
    public function ModificarOperacion(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //modifica enlazando parametros de la instncia
		$consulta = $objetoAccesoDato->RetornarConsulta("UPDATE Operaciones SET 
        color=:color, 
        foto=:foto
        WHERE patente=:patente AND fecha_hora_ingreso=:fecha_hora_ingreso");

		$consulta->bindValue(':patente',$this->patente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_ingreso', $this->fecha_hora_ingreso, PDO::PARAM_STR);
        $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
		$consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);

		return $consulta->execute();
    }
    */
    
    public function EstacionarVehiculo(){
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso();
        //inserta enlazando parametros dela instancia
		$consulta =$objetoAccesoDato->RetornarConsulta("INSERT INTO Operaciones (
            patente,
            color,
            foto,
            id_empleado_ingreso,
            fecha_hora_ingreso
            )values(
            :patente,
            :color,
            :foto,
            :id_empleado_ingreso,
            :fecha_hora_ingreso
            )");
        
		$consulta->bindValue(':patente',$this->patente, PDO::PARAM_STR);
		$consulta->bindValue(':color', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->bindValue(':id_empleado_ingreso', $this->id_empleado_ingreso, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_hora_ingreso', date("Y-m-d H:i:s e"), PDO::PARAM_STR);
		$consulta->execute();

		return $objetoAccesoDato->RetornarUltimoidInsertado();
    }
    
    public static function SacarVehiculo($patente){

        $unaOperacion = self::TraerUnaOperacion()

        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta =$objetoAccesoDato->RetornarConsulta("SELECT patente, color, foto, id_empleado_ingreso, fecha_hora_ingreso, id_empleado_salida, fecha_hora_salida, tiempo, importe FROM Operaciones WHERE patente=:patente AND fecha_hora_ingreso=:fecha_hora_ingreso");
        $consulta->bindValue(':patente',$patente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_ingreso',$fecha_hora_ingreso, PDO::PARAM_STR);
        $consulta->execute();
        $OperacionBuscada = $consulta->fetchObject('Operacion');
        
        return $OperacionBuscada;
    }

    /*
    public function GuardarOperacion(){
        if(empty(Operacion::TraerUnOperacion($this->mail))){
            $this->InsertarOperacion();
            echo "Operacion guardado";
        } else {
            $elOperacion = Operacion::TraerUnOperacion($this->mail);
            $this->id = $elOperacion->id;
            
            //un For que traiga todos los datos si están en NULL que no debería ser
            if ($this->clave==null) {
                $this->clave = $elOperacion->getClave();
            }
            
            if ($this->ModificarOperacion()) {
                echo "Operacion modificado";
            } else {
                echo "No modifico Operacion";
            }
        }
    }
    */

    public static function TraerTodasLasOperaciones(){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT patente, color, foto, id_empleado_ingreso, fecha_hora_ingreso, id_empleado_salida, fecha_hora_salida, tiempo, importe FROM Operaciones");
		$consulta->execute();			
        
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Operacion");
    }
    
    /*public static function TraerUnVehiculoEstacionado($patente){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT patente, color, foto, id_empleado_ingreso, fecha_hora_ingreso, id_empleado_salida, fecha_hora_salida, tiempo, importe FROM Operaciones WHERE patente=:patente AND fecha_hora_salida = null");
        $consulta->bindValue(':patente',$patente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_ingreso',$fecha_hora_ingreso, PDO::PARAM_STR);
        $consulta->execute();
		$OperacionBuscada = $consulta->fetchObject('Operacion');
        
        return $OperacionBuscada;
    }*/

    public static function TraerUnaOperacion($patente, $fecha_hora_ingreso){
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("SELECT patente, color, foto, id_empleado_ingreso, fecha_hora_ingreso, id_empleado_salida, fecha_hora_salida, tiempo, importe FROM Operaciones WHERE patente=:patente AND fecha_hora_ingreso=:fecha_hora_ingreso");
        $consulta->bindValue(':patente',$patente, PDO::PARAM_STR);
        $consulta->bindValue(':fecha_hora_ingreso',$fecha_hora_ingreso, PDO::PARAM_STR);
        $consulta->execute();
		$OperacionBuscada = $consulta->fetchObject('Operacion');
        
        return $OperacionBuscada;
    }

    /*
    public static function BorrarOperacionPorParametro($mail){
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta =$objetoAccesoDato->RetornarConsulta("DELETE FROM Operaciones WHERE mail=:mail");	
		$consulta->bindValue(':mail',$mail, PDO::PARAM_STR);		
		$consulta->execute();
        
        return $consulta->rowCount();
    }*/

}
?>